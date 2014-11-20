<?php
/**
 * This code is part of the Borderfree Magento Extension.
 * 
 * @category Borderfree
 * @package Borderfree_Catalog
 * @author Jamie Kail <jamie.kail@livearealabs.com>
 * @copyright Copyright (c) 2013 Borderfree (http://www.borderfree.com)
 *
 */
?>
<?php
/**
 * This model exports full and incremental catalog data to Fifty one and checks for catalog error logs.
 * 
 * Observes the following events:
 * 
 * fiftonelocalization_predispatch_observer
 * controller_action_layout_load_before
 *
 * @category Borderfree
 * @package Borderfree_Catalog
 * @author Jamie Kail <jamie.kail@livearealabs.com>
 *
 */
class Borderfree_Catalog_Model_Export extends Mage_Core_Model_Abstract
{
	/**
	 * The catalog export directory
	 * 
	 * @var string
	 */
	private $dir = ""; 
	private $file = NULL;
	private $requestId = 0;
	private $recordsOut = 0;
	private $rootcatId = 2;
	private $request = "";
	private $type = "";
	private $filenum = 0;
	private $batchId;
	private $outStr = "";
	private $indent = 1;
	private $strIndent = 0;
	private $errors = "";
	private $lastRequest = "";
	private $pageSize = 1000;
	private $zip= NULL;
	private $filename = "";
	private $merchantIds = array();
	private $stores = array();
	private $subject = "Borderfree Catalog Export Error";
	private $batchComplete = false;
	private $localizationHelper;
	private $upsSimpleProductUpcs = array();
	
	/**
	 * Creates directory structure needed for Borderfree Catalog Export,
	 * cleans the export directory of old files, and sets the batch ID.
	 */
	function __construct()
	{
		gc_enable();
		
		parent::__construct();
		
		$this->localizationHelper = Mage::Helper("borderfreelocalization");
		$this->upsSimpleProductUpcs = $this->simpleProductUpcsOfUps();	
		$this->dir = Mage::getBaseDir('var') . DS . "Borderfree" . DS;
		umask(0);
		$this->batchId = date("mdyHis");
		
		if(!is_dir($this->dir))
		{
			mkdir($this->dir);
		}

		if(!is_dir($this->dir  . "incoming_borderfree_catalog_error_logs" . DS))
		{
			mkdir($this->dir  . "incoming_borderfree_catalog_error_logs" . DS);
		}
		
		if(!is_dir($this->dir  . "catalog_export_archive" . DS))
		{
			mkdir($this->dir  . "catalog_export_archive" . DS);
		}		
	}
	private function simpleProductUpcsOfUps()
	{
		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
		$sql="SELECT osp.upc FROM catalog_product_entity_varchar cpev INNER JOIN onerockwell_storesmgmt_products osp ON cpev.value = osp.upc  WHERE cpev.attribute_id = '207'  AND osp.storesmgmt_store_code = '700' AND osp.status = '1'AND 'qty' > '0'";
		$filterUpsUpcs=$connection->fetchCol($sql);
		return $filterUpsUpcs;
    }
    public function checkUpcInStore($upc)
	{
		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
		$sql = "SELECT `upc` FROM `onerockwell_storesmgmt_products` WHERE `upc` = $upc AND `storesmgmt_store_code` = '700' AND `qty` > '0' LIMIT 1";
		$Upc = $connection->fetchOne($sql);
		if($Upc){
			return true;
		}else{
			return false;
		}
    }
	private function sendZipFile()
	{
		$dir = opendir($this->dir);
		$this->zip = new ZipArchive();
		$filename = $this->dir . $this->batchId .".zip";
		
		if ($this->zip->open($filename, ZIPARCHIVE::CREATE)!==TRUE)
		{
			throw new Exception("Cannot creare zip archive $filename");
		}
		
		while($file = readdir($dir))
		{
			if(!is_dir($this->dir. $file) || strpos(".xml", $file) > 0)
			$this->zip->addFile($this->dir . $file, $file);
		}
		
		closedir($dir);		
		$this->zip->close();		
		
		$server = Mage::getStoreConfig('borderfree_options/settings/staging') ? Mage::getStoreConfig('borderfree_options/catalog/ftpstage') : Mage::getStoreConfig('borderfree_options/catalog/ftpprod');
		$username = Mage::getStoreConfig('borderfree_options/catalog/ftpuser');
		$password  = Mage::helper('core')->decrypt(Mage::getStoreConfig('borderfree_options/catalog/ftppassword'));
		
		//$conn_id = ftp_ssl_connect($server); 
		 $conn_id = ftp_connect($server); 
		if (!$conn_id)
		    throw new Exception("Unable to connect to FTP server: $server");
		
		$login_result = ftp_login($conn_id, $username, $password); 
		if (!$login_result)
			throw new Exception("Login to FTP server $server failed for user $username");

		ftp_pasv($conn_id, true);
		$upload = ftp_put($conn_id, '/Inbox/Catalog/' . $this->batchId .".zip", $this->dir . $this->batchId .".zip", FTP_BINARY); 
		if (!$upload)
			throw new Exception("Failed to upload file" . $this->batchId . ".zip to server " .$server . ".");
		
		$mrk = fopen($this->dir . $this->batchId .".zip.mrk", "w");
		fputs($mrk, "mrk");
		fclose($mrk);
		
		$upload = ftp_put($conn_id, '/Inbox/Catalog/' . $this->batchId .".zip.mrk", $this->dir . $this->batchId .".zip.mrk", FTP_BINARY);
		if (!$upload)
			throw new Exception("Failed to upload file" . $this->batchId . ".zip.mrk to server " .$server . ".");
		
		rename( $this->dir . $this->batchId .".zip", $this->dir  . "catalog_export_archive" . DS . $this->batchId .".zip");
		
		ftp_close($conn_id);
	}
	
	public function getErrorLogs()
	{
		$model = Mage::getModel('borderfreecrontab/log');
		$model->setType("Get Catalog Error Logs");
		$model->setLastRun(time());
		$model->save();
		
		$allStores = Mage::app()->getStores();
		foreach ($allStores as $_eachStoreId => $val)
		{
			$store = Mage::app()->getStore($_eachStoreId)->getId();
			Mage::app()->setCurrentStore($store);
			if(Mage::getStoreConfig('borderfree_options/settings/enabled'))
			{
				if(!in_array(Mage::getStoreConfig('borderfree_options/settings/merchantid'), $this->merchantIds))
				{
					try
					{
						$this->subject = "Borderfree Get Catalog Error Logs";
						$server = Mage::getStoreConfig('borderfree_options/settings/staging') ? Mage::getStoreConfig('borderfree_options/catalog/ftpstage') : Mage::getStoreConfig('borderfree_options/catalog/ftpprod');
						$username = Mage::getStoreConfig('borderfree_options/catalog/ftpuser');
						$password  = Mage::helper('core')->decrypt(Mage::getStoreConfig('borderfree_options/catalog/ftppassword'));
						
						$conn_id = ftp_ssl_connect($server);
						if(!$conn_id)
							throw new Exception("Unable to connect to FTP server: $server");
						
						$login_result = ftp_login($conn_id, $username, $password);
						if(!$login_result)
							throw new Exception("Login to FTP server $server failed for user $username");
						
						ftp_pasv($conn_id, true);
						
						$chdir = ftp_chdir($conn_id, "/Inbox/Catalog");
						if(!$chdir)
							throw new Exception("Unable to change directory to /Inbox/Catalog on server $server");
						
						$files = ftp_nlist($conn_id, ".");
						foreach($files as $file)
						{
							if(strpos($file, "err") > 0)
							{
								$get = ftp_get($conn_id, $this->dir . "incoming_borderfree_catalog_error_logs" . DS . $file, $file, FTP_BINARY);
								
								if($get)
									$del = ftp_delete($conn_id, $file);
								else
									throw new Exception("Unable to download $file from server $server");
								
								if(!$del)
									throw new Exception("Unable to delete $file from server $server");
			
								$message = $file . ":\n\n" . file_get_contents($this->dir . "incoming_borderfree_catalog_error_logs" . DS . $file);
								mail(Mage::getStoreConfig('borderfree_options/settings/erroremail'), "Borderfree Catalog Import Error", $message);
							}
						}
					}
					catch(Exception $e)
					{
						$this->logError("Get Error logs: " . $e->getMessage(), Zend_Log::ERR);
					}
					if($conn_id) {
						ftp_close($conn_id);
					}
				}
			}
		}
		$this->sendErrorLog();
	}
	
	
	public function fullExport($updated_at = NULL)
	{
		if(Mage::getModel("borderfreecatalog/stores")->getCollection()->count() > 0)
			return;
		
		$model = Mage::getModel('borderfreecatalog/log');
		$model->setType($updated_at == NULL ? "full" : "incremental");
		$model->setStartTime(time());
		$model->save();
		
		$model = Mage::getModel('borderfreecrontab/log');
		$model->setType($updated_at == NULL ? "Full Catalog Export" : "Incremental Catalog Export");
		$model->setLastRun(time());
		$model->save();

		$dir = opendir($this->dir);
		while($file = readdir($dir))
		{
			if(!is_dir($this->dir. $file))
				unlink($this->dir. $file);
		}
		closedir($dir);
		
		$allStores = Mage::app()->getStores();
		foreach ($allStores as $_eachStoreId => $val)
		{
			$store = Mage::app()->getStore($_eachStoreId)->getId();
			Mage::app()->setCurrentStore($store);

			if(Mage::getStoreConfig('borderfree_options/settings/enabled'))
			{
				if(!in_array(Mage::getStoreConfig('borderfree_options/settings/merchantid'), $this->merchantIds))
				{
					$this->merchantIds[] = Mage::getStoreConfig('borderfree_options/settings/merchantid');

					$stores = Mage::getModel("borderfreecatalog/stores")->load($_eachStoreId);
					$stores->setStoreId($_eachStoreId);
					$stores->setStatus(1);
					$stores->setStartTime($updated_at);
					$stores->setFilenum(0);
					$stores->setRequestId(0);
					$stores->setBatchId(date("mdyHis"));
					$stores->save();
				}
			}
		}
		
		$this->resumeExport();
	}
	
	private function sendErrorLog()
	{
		if(empty($this->errors))
			return;
				
		mail(Mage::getStoreConfig('borderfree_options/settings/erroremail'), $this->subject, $this->errors);
		$this->errors = "";
	}
	
	public function incrementalExport()
	{
		$collection = Mage::getModel("borderfreecatalog/log")->getCollection();
		$last = $collection->getLastItem();
		if($last == null)
			$this->fullExport();
		else
			$this->fullExport($last->getStartTime());
	}
	
	
	
	private function exportAllCategories($updated_at = null)
	{	
		$this->rootcatId = Mage::app()->getStore()->getRootCategoryId();
		$cat = Mage::getModel('catalog/category')->load($this->rootcatId);

		$this->writeCategory($cat, $updated_at);
		$this->writeCategories($cat->getChildrenCategories(), $updated_at);
		$this->closeFile();
		
		$cat->clearInstance();
		unset($cat);
	}
	
	private function getProductIDs($page, $updated_at = NULL)
	{
		$productIDs = Mage::getModel("catalog/product")->getCollection();
			
		if($updated_at != NULL)
			$productIDs->addFieldToFilter("updated_at", array("from" => $updated_at));
			
		$productIDs->setPageSize($this->pageSize)->setCurPage($page);
		
		return $productIDs;
	}
	
	private function exportAllProducts($updated_at = NULL, $kits = false)
	{
		$page = 1;
		$num = 1;
		
		$products = Mage::getModel("borderfreecatalog/products")->getCollection()->addFieldToFilter("status", 1)->setPageSize($this->pageSize)->setCurPage(1);
		if($products->count() == 0)
		{
			$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
			$connection->query("truncate table borderfree_catalog_export_products");
			
			$sql = "insert into borderfree_catalog_export_products select product_id, 1 from catalog_product_entity, catalog_product_website where product_id = entity_id ". ($kits ? "and type_id = 'bundle'": "") . "and website_id = " . Mage::app()->getWebsite()->getId();
			if($updated_at != NULL) 
				$sql .= " and updated_at >= '" . $updated_at . "'";			
			$connection->query($sql);
			
			$products = Mage::getModel("borderfreecatalog/products")->getCollection()->addFieldToFilter("status", 1)->setPageSize($this->pageSize)->setCurPage(1);
			if($products->count() == 0)
			{			
				$connection = Mage::getSingleton('core/resource')->getConnection('core_write');			
				$connection->query("truncate table borderfree_catalog_export_products");
				
				$this->batchComplete = true;
				$this->closeFile();
			}
			return;
		}
		
		while($products->count() > 0)
		{
			foreach($products as $product)
			{
				try
				{
					$product->setStatus(0)->save();

					$p = Mage::getModel("catalog/product");
					$p->load($product->getProductId());
					if($kits)
						$this->writeKit($p);
					else
						$this->writeProduct($p);
					
					$p->clearInstance();
					unset($p);
						
					if($this->file == NULL)
						return;
				}
				catch(Exception $e)
				{
					$this->resetStrIndent();	
					$this->logError("Product ". $p->getName() . ":" . $e->getMessage() . " - Skipping Product");
					$p->clearInstance();
					unset($p);
				}
			}

			$products = Mage::getModel("borderfreecatalog/products")->getCollection()->addFieldToFilter("status", 1)->setPageSize($this->pageSize)->setCurPage(1);
		}
		
		$connection = Mage::getSingleton('core/resource')->getConnection('core_write');			
		$connection->query("truncate table borderfree_catalog_export_products");
		
		$this->batchComplete = true;
		$this->closeFile();
	}
	
	private function resetStrIndent()
	{
		$this->indent -= $this->strIndent;
		$this->strIndent = 0;
	}
	
	private function writeGroupedProduct($product)
	{
		$category = $this->getCategory($product);
		
		$childProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
		foreach($childProducts as $p)
		{
			$pp = Mage::getModel("catalog/product")->load($p->getId());
			$p->clearInstance();
			unset($p);
			$this->writeProduct($pp, true, $category);
			$pp->clearInstance();
			unset($pp);
		}
		unset($category);
		unset($childProducts);
	}
	
	private function getCategory($product)
	{
		$level = 0;
		$category = NULL;
		
		$categories = $product->getCategoryIds();
		foreach ($categories as $category_id)
		{
			$cat = Mage::getModel('catalog/category')->load($category_id);
			if($cat->getLevel() > $level)
			{
				$level = $cat->getLevel();
				$category = $category_id;
			}
			$cat->clearInstance();
			unset($cat);
		}
		unset($categories);
		
		return $category;
	}

	private function writeKit($product)
	{
		$this->getFile("submitKitRequest", "kits");
		
		$this->startElement("kit", array("sku" => $product->getSku()), true);

		$selectionCollection = $product->getTypeInstance(true)->getSelectionsCollection(
				$product->getTypeInstance(true)->getOptionsIds($product), $product
		);
		$bundled_items = array();
		foreach($selectionCollection as $option)
		{
			//print_r($option);
			$this->writeElement("qty", 1, $option->getSelectionQty(), false, true, array("sku" => $option->getSku()));
		}
		
	
		$this->closeElement("kit", true);
		$this->writeRecord();
	}
	
	
	private function writeProduct($product, $grouped = false, $category = NULL)
	{
		$this->getFile("submitProductRequest", "products");

		$productType = $product->getTypeId();
		if(!in_array($productType, array("simple", "grouped", "configurable", "bundle")))
				return;
		
		if($productType == "grouped")
		{
			$this->writeGroupedProduct($product);
			return;
		}

//@todo RETHINK THIS, Check gurouped sub products for category assignment, DO WE ALSO NEED TO CHECK FOR SEARCH?
		$visibility = $product->getAttributeText('visibility');
		if(strpos($visibility, "Catalog") === FALSE && !$grouped )
			return;
		if(!(strpos($visibility, "Catalog") === FALSE) && $grouped)
			return;

				
		if($category == NULL)
			$category = $this->getCategory($product);
		else
		{
			$cat = $this->getCategory($product);
			if($cat != NULL)
				$category = $cat;
			unset($cat);
		}
			
		if($category == NULL)
			throw new Exception("No categories assigned.");
		

		$this->startElement("product", array("id" => $product->getId(), "merchantId" => Mage::getStoreConfig('borderfree_options/settings/merchantid'), "categoryId" => $category), true);
		$this->writeElement("name", $product->getName(), 255, false, true);
		$this->writeElement("description", $product->getDescription(), 1024, true, true);
		
		$associatedProducts = NULL;
		if($productType == "configurable")
		{
			$associatedProducts = $product->getTypeInstance()->getUsedProducts();
			if(count($associatedProducts) == 0)
				throw new Exception("No simple products defined for this configurable product.");
			$this->writeElement("primarySku", $associatedProducts[0]->getSku(), 50);
		}
		else
			$this->writeElement("primarySku", $product->getSku(), 50);

		$this->writeElement("isActive", $product->isSaleable() ? "true" : "false", 5);
		$this->writeElement("shortDescription", $product->getShortDescription(), 255, true);
		$this->writeElement("keywords", $product->getMetaKeyword(), 1024);
		$this->writeElement("url", $product->getProductUrl(), 1024, false, true);
		$this->writeElement("sizeChartUrl", $product->getSizeChartUrl(), 1024);
		$this->writeElement("isExclusive", $product->getIsExclusive() ? "true" : "false", 5);
		$this->writeElement("isPersonalizable", $product->hasOptions() ? "true" : "false", 5);
		$this->writeElement("manufacturer", $product->getAttributeText('manufacturer'), 256);
		$this->writeElement("brand", $product->getAttributeText('brand'), 256);
		$this->writeElement("collection", $product->getAttributeText('collection'), 256);
		$this->writeElement("gender", $product->getAttributeText('gender'), 256);
		$this->writeMultiselect("ages", "age", $product->getAttributeText("age"));
		$this->writeMultiselect("seasons", "season", $product->getAttributeText("season"));
		
		$this->startElement("skus", null, true);
		
		if($productType == "simple" || $productType == "bundle")
			$this->writeSku($product);
		elseif($productType == "configurable")
			$this->writeSkus($product, $associatedProducts);
		
		$this->closeElement("skus", true);
		
		$this->closeElement("product", true);
		$this->writeRecord();
	}

//continue clearInstance/unset
	private function writeSkus($product, $associatedProducts)
	{
		foreach($associatedProducts as $p)
		{
            $is_present = false;
			$pp = Mage::getModel("catalog/product")->load($p->getId());
			$p->clearInstance();
			unset($p);
            if (in_array($pp->getUpc(), $this->upsSimpleProductUpcs))
			{
				$is_present = true;
				$this->writeSku($pp, $product);
			}
			$pp->clearInstance();
			unset($pp);
		}
		if (!$is_present) {
			throw new Exception("No product defined for this simple product in UPS.");
		}
		unset($associatedProducts);
	}
	
	private function writeSku($product, $parent = NULL)
	{
		$this->startElement("sku", array("id" => $product->getSku()), true);
		$this->writeElement("isActive", $product->isSaleable() ? "true" : "false", 5);
		
		$this->startElement("uids", null, true);
		$this->writeElement("gtin", $product->getGtin(), 100);
		$this->writeElement("upc", $product->getUpc(), 100);
		$this->writeElement("ean", $product->getEan(), 100);
		$this->writeElement("apn", $product->getApn(), 100);
		$this->writeElement("jan", $product->getJan(), 100);
		$this->writeElement("eccn", $product->getEccn(), 100);
		$this->writeElement("mpn", $product->getMpn(), 100);
		$this->closeElement("uids", true);
		
		if($parent != null)
		{
			$attributes = $parent->getTypeInstance()->getConfigurableAttributesAsArray($parent);
			$attribs = array();
			foreach($attributes as $attribute)
			{
				$attribs[] = $attribute["attribute_code"];
			}
			if(count($attribs) > 0)
			{
				$this->startElement("variants", null, true);

				if(in_array("color", $attribs))
					$this->writeElement("color", $product->getAttributeText('color'), 128);

				if(in_array("size", $attribs))
					$this->writeElement("size", $product->getAttributeText('size'), 128);

				if(in_array("condition", $attribs))
					$this->writeElement("condition", $product->getAttributeText('condition'), 128);

				foreach($attribs as $attrib)
				{
					if($attrib != "color" && $attrib != "size" && $attrib != "condition")
						$this->writeElement("custom", "$attrib:{$product->getAttributeText($attrib)}", 128);
				}
				
				$this->closeElement("variants", true);
			}
		}

		$media = $product->getMediaGalleryImages();
		if($media == NULL || count($media->getItems()) == 0)
		{	
			if($parent != NULL)
				$media = $parent->getMediaGalleryImages();
			
			if($media == null)
				throw new Exception("no images found.");
		}
		$images = $media->getItems();
		if(count($images) > 0)
		{			
			$this->startElement("imageUrls", null, true);
			
			$imageName = "primary";
			foreach($images as $image)
			{
				$this->writeElement($imageName, $image->getUrl(), 1024, false, true);

				if($imageName == "alternate")
					break;
				
				$imageName = "alternate";
			}
			
			$swatchImage = $product->getMediaGalleryImages()->getItemByColumnValue('label', 'swatch');
			if($swatchImage != null)
				$this->writeElement("swatch", $swatchImage->getUrl(), 1024);
				
				
			$this->closeElement("imageUrls", true);
		}
		else
			throw new Exception("{$product->getName()} no images found.");
				
		$this->writeElement("dutiablePrice", $product->getPrice(), 100);
/**
 * @todo from here down get data from parent if empty
 */
		$this->writeElement("care", $product->getCare(), 1024, true);
		$this->writeElement("contents", $product->getContents(), 1024, true);
		
		$com = $product->getAttributeText('country_of_manufacture');
		if(!empty($com))
		{
			$coo = $this->localizationHelper->countryToCode($com);
			$this->writeElement("coo", $coo, 2);
			$this->writeElement("isImported", $coo != "US" ? "true" : "false", 5);
		}
		
		$this->writeHsCodes($product->getHsCodes());
		$this->writeElement("isOrmd", $product->getIsOrmd() ? "true" : "false", 5);

		$commonName = $product->getFwsCommonName();
		$scientificName = $product->getFwsScientificName();
		
		if(!empty($commonName) || !empty($scientificName))
		{
			$this->startElement("fishAndWildlife", null, true);
				$this->writeElement("commonName", $commonName, 128);
				$this->writeElement("scientificName", $scientificName, 128);
				$this->writeElement("color", $product->getAttributeText('fws_source'), 1);
				$coo = $product->getAttributeText('fws_country_of_origin');
				if(!empty($coo))
				{
					$coo = $this->localizationHelper->countryToCode($com);
					$this->writeElement("coo", $coo, 2);
				}				
			$this->closeElement("fishAndWildlife", true);
		}
		
		$weight = $product->getWeight();
		if(!empty($weight) && !($weight > 0))
			$this->writeElement("weight", $weight, 100, false, false, array("unit" => Mage::getStoreConfig('borderfree_options/catalog/weightunit')));
		
		$this->startElement("boxDimensions", array("unit" => Mage::getStoreConfig('borderfree_options/catalog/lengthunit')), true);
		$this->writeDimension("width", $product->getBoxWidth(), 100);
		$this->writeDimension("length", $product->getBoxLength(), 100);
		$this->writeDimension("height", $product->getBoxHeight(), 100);
		$this->closeElement("boxDimensions", true);
				
		$this->startElement("productDimensions", array("unit" => Mage::getStoreConfig('borderfree_options/catalog/lengthunit')), true);
		$this->writeDimension("width", $product->getProductWidth(), 100);
		$this->writeDimension("length", $product->getProductLength(), 100);
		$this->writeDimension("height", $product->getProductHeight(), 100);
		$this->closeElement("productDimensions", true);

		$restrictions = $product->getAttributeText("country_restrictions");
		if($product->getBorderfreeRestricted())
		{
			$this->startElement("restrictions", null, true);
			$this->writeElement("restriction", "All", 3, false, false, array("type" => "Country"));	
			$this->closeElement("restrictions", true);
		}
		else if($restrictions != NULL)
		{
			$this->startElement("restrictions", null, true);
			if(is_array($restrictions))
			{
				foreach($restrictions as $r)
				{
					$cc = $this->localizationHelper->countryToCode($r);
					$this->writeElement("restriction", $cc, 3, false, false, array("type" => "Country"));
				}
			}
			else
			{
				$cc = $this->localizationHelper->countryToCode($restrictions);
				$this->writeElement("restriction", $cc, 3, false, false, array("type" => "Country"));
			}
			$this->closeElement("restrictions", true);
		}
		unset($restrictions);
				
		$this->closeElement("sku", true);
	}
	
	private function writeDimension($type, $value)
	{
		if(empty($value))
			return;
		
		if(!is_numeric($value))
			throw new Exception('$type dimension must be numeric.');
		
		if(!($value > 0))
			throw new Exception('$type dimension must greater than zero.');

		$this->writeElement($type, $value, 100);
	}
	
	private function writeMultiselect($setName, $name, $values)
	{
		if($values == NULL)
			return;
		
		$values = explode(",", $values);
		
		$this->startElement($setName, NULL, true);
		foreach($values as $value)
		{
			$this->writeElement($name, strtoupper($value), 256);
		}
		$this->closeElement($setName, true);
	}
	
	public function resumeExport()
	{
		ini_set("memory_limit","2560M");
		ini_set('max_execution_time', 3600);
		try 
		{
			$stores = Mage::getModel("borderfreecatalog/stores")->getCollection()->addFieldToFilter("status", array('neq' => 0));
			if($stores->count() == 0)
			{
				$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
				$connection->query("truncate table borderfree_catalog_export_stores");
				return;
			}
	
			$stores = Mage::getModel("borderfreecatalog/stores")->getCollection()->addFieldToFilter("status", 2);
			if($stores->count() > 0)
			{
				$store = $stores->getFirstItem();
				Mage::app()->setCurrentStore($store->getStoreId());
				
				$this->lastRequest = "submitProductRequest";
				$this->request = "submitProductRequest";
				$this->type = "products";	
				$this->filenum = $store->getFilenum();
				$this->requestId = $store->getRequestId();
				$this->batchId = $store->getBatchId();

				$this->fileExists();
				$this->exportAllProducts($store->getStartTime());
				
				$store->setFilenum($this->filenum);
				$store->setRequestId($this->requestId);
				$store->setBatchId($this->batchId);
				$store->save();
				
				if($this->batchComplete)
				{
					$store->setStatus(3)->save();
					$this->exportAllProducts($store->getStartTime(), true);
				}
				
				$this->sendErrorLog();
				return;
			}
			
			$stores = Mage::getModel("borderfreecatalog/stores")->getCollection()->addFieldToFilter("status", 3);
			if($stores->count() > 0)
			{
				$store = $stores->getFirstItem();				
				Mage::app()->setCurrentStore($store->getStoreId());
			
				$this->lastRequest = "submitProductRequest";
				$this->request = "submitKitRequest";
				$this->type = "kits";
				$this->filenum = 0;
				$this->requestId = $store->getRequestId();
				$this->batchId = $store->getBatchId();
			
				$this->fileExists();
				$this->exportAllProducts($store->getStartTime(), true);
			
				$store->setFilenum($this->filenum);
				$store->setRequestId($this->requestId);
				$store->setBatchId($this->batchId);
				$store->save();
			
				if($this->batchComplete)
				{
					$store->setStatus(0)->save();
					$this->sendZipFile();
				}
			
				$this->sendErrorLog();
				return;
			}
				
			
			$stores = Mage::getModel("borderfreecatalog/stores")->getCollection()->addFieldToFilter("status", 1);
			if($stores->count() > 0)
			{
				$this->lastRequest = "";
				$this->request = "";
				$this->type = "";			
				$this->filenum = 0;
				$this->requestId = 0;
				$this->batchId = date("mdyHis");
				
				$store = $stores->getFirstItem();
				$store->setStatus(2)->save();
	
				Mage::app()->setCurrentStore($store->getStoreId());
						
				$this->exportAllCategories($store->getStartTime());
				$this->exportAllProducts($store->getStartTime());
				
				$store->setFilenum($this->filenum);
				$store->setRequestId($this->requestId);
				$store->setBatchId($this->batchId);
				$store->save();
							
				if($this->batchComplete)
				{
					$store->setStatus(3)->save();
				}
			}
		}
		catch(Exception $e)
		{
			$this->logError($e->getMessage(), Zend_Log::ERR);
		}
		
		$this->sendErrorLog();
		
	}
	
	private function getFile($request, $type)
	{
		unset($this->outStr);
				
		$this->outStr = "";
		$this->request = $request;
		$this->type = $type;
		
		if($this->file != NULL)
			return $this->file;
		
		if($this->lastRequest != $this->request)
			$this->filenum = 0;
				
		$filenum = str_pad($this->filenum, 5, "0", STR_PAD_LEFT);
		$this->filename = $type . "_$filenum.xml";
		$this->file = fopen($this->dir .$this->filename, "w");

		$this->lastRequest = $this->request;
		$this->requestId += 1;
		$this->startElement($this->request, array("id" => $this->requestId, "batchId" => $this->batchId));
		$this->startElement($this->type);

		return $this->file;
	}
		
	private function startElement($name, $attributes = NULL, $useStr = FALSE)
	{
		$data = str_pad("<", $this->indent, " ", STR_PAD_LEFT) . $name;
		
		if($attributes)
		{
			foreach($attributes as $attribute => $value)
			{
				$data .= " $attribute=\"$value\"";
			}
		}
		
		$data .= ">\n";
		
		if($useStr)
		{
			$this->outStr .= $data;
			$this->strIndent += 4;
		}
		else
			fputs($this->file, $data);

		$this->indent += 4;
	}
	
	private function closeElement($name, $useStr = false)
	{
		$this->indent -= 4;
		
		$data = str_pad("<", $this->indent, " ", STR_PAD_LEFT) . "/" . $name . ">\n";
		
		if($useStr)
		{
			$this->outStr .= $data;
			$this->strIndent -= 4;
		}
		else
			fputs($this->file, $data);
	}
	
	private function closeFile()
	{
		if($this->file == NULL)
			return;
				
		$this->closeElement($this->type);
		$this->closeElement($this->request);
		fclose($this->file);
		$this->file = NULL;
		$this->filenum += 1;
		$this->recordsOut = 0;
		$this->indent = 1;
	}

	private function writeElement($name, $data, $maxLength, $strip = FALSE, $required = FALSE, $attributes = NULL)
	{
		if(empty($data))
		{
			if($required)
				throw new Exception($name . " - Value Required");
			
			return;	
		}
		
		$this->outStr .= str_pad("<", $this->indent, " ", STR_PAD_LEFT) . $name;
		
		if($attributes)
		{
			foreach($attributes as $attribute => $value)
			{
				$this->outStr .= " $attribute=\"$value\"";
			}
		}
		
		$this->outStr .= ">";		
		
		if($strip)
			$data = html_entity_decode(strip_tags($data), ENT_COMPAT, "UTF-8");
		
		if(strlen($data) > $maxLength) {
			if(Mage::getStoreConfig('borderfree_options/catalog/truncate') && $name != 'url' && $name != 'primarySku') {
				$data = substr($data, 0, $maxLength);
			}
		}
		
		if ($name == 'primarySku' && !preg_match("/^[A-Za-z0-9@#~|*_.\/-]+$/", trim($data))) {
			throw new Exception(" SKU contain illegal characters.");
		}
		
		if(strlen($data) > $maxLength)
			throw new Exception($name . " - exceeds max length of $maxLength characters.");
		
		$this->outStr .= "<![CDATA[$data]]>";
		
		
		$this->outStr .= "</$name>\n";
	}
	
	private function writeHsCodes($hscodes)
	{
		if(!empty($hscodes))
		{
			$this->startElement("hscodes", null, true);
		
			$codes = explode(",", $hscodes);
			foreach($codes as $code)
			{
				if(!is_numeric($code) || !is_int($code + 0))
					throw new Exception("Invalid hscode - $code");
		
				$this->writeElement("hscode", $code, 20);
			}
				
			$this->closeElement("hscodes", true);
		}
	}
	
	private function writeCategory($category, $updated_at = null)
	{
		try
		{
			if($updated_at != NULL)
			{
				$lastdate = strtotime($updated_at);
				$catdate = strtotime($category->getUpdatedAt());
					
				if($catdate < $lastdate)
					return;
			}
					
			$file = $this->getFile("submitCategoryRequest", "categories");
	
			$this->startElement("category", array("id" => $category->getId(), "merchantId" => Mage::getStoreConfig('borderfree_options/settings/merchantid'), "parentId" => ($category->getId() == $this->rootcatId ? "NULL" : $category->getParentId())), true);
			$this->writeElement("name", $category->getName(), 255, false, true);	
			$this->writeElement("description", $category->getDescription(), 1024, true);	
			$this->writeElement("url", $category->getUrl(), 1024);	
	
			$this->writeHsCodes($category->getHsCodes());
	
			$name = $category->getName();
			$restrictions = $category->getResource()->getAttribute('category_country_restrictions')->getFrontend()->getValue($category);
			if($category->getBorderfreeRestricted())
			{
				$this->startElement("restrictions", null, true);
				$this->writeElement("restriction", "All", 3, false, false, array("type" => "Country"));	
				$this->closeElement("restrictions", true);
			}
			else if($restrictions != NULL)
			{
				$countries = explode(",", $restrictions);
				$this->startElement("restrictions", null, true);
				foreach($countries as $r)
				{
					$cc = $this->localizationHelper->countryToCode($r);
					$this->writeElement("restriction", $cc, 3, false, false, array("type" => "Country"));
				}
				$this->closeElement("restrictions", true);
			}
					
			$this->closeElement("category", true);
			
			$this->writeRecord();
		}
		catch(Exception $e)
		{
			$this->resetStrIndent();
			$this->logError("Category ". $category->getName() . ":" . $e->getMessage() . "Skipping Category");
		}
	}

	private function writeRecord()
	{
		fputs($this->file, $this->outStr);
		
		$this->recordsOut += 1;
		if($this->recordsOut == Mage::getStoreConfig('borderfree_options/catalog/filerecords'))
			$this->closeFile();
	}
	
	private function writeCategories($categories, $updated_at = null) 
	{
		foreach($categories as $c) 
		{
			$category = Mage::getModel('catalog/category')->load($c->getId());

			$this->writeCategory($category, $updated_at);
			if($category->hasChildren()) 
			{
				$children = $category->getChildrenCategories();
				$this->writeCategories($children, $updated_at);
				unset($children);
			}
			
			$c->clearInstance();
			unset($c);
			$category->clearInstance();
			unset($category);
		}
	}
	
	private function logError($message, $level = Zend_Log::WARN)
	{
		$this->errors .= "Batch #" . $this->batchId . ": " . $message . "\n";
	}
	
	private function fileExists(){
		$filenum = str_pad($this->filenum, 5, "0", STR_PAD_LEFT);
		$filename = $this->type . "_$filenum.xml";
		$path = $this->dir . $filename;
		if (file_exists($path)) {
			exit();
		}
	}
}
