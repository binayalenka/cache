<?php
class Borderfree_Order_Model_Import extends Mage_Core_Model_Abstract
{
	private $dir = "";
	private $errors = "";
	private $merchantIds = array();
	private $subject = "Borderfree Order Import Error";
	private $batchId;

	function __construct()
	{
		parent::__construct();

		$dir = Mage::getBaseDir('app') . DS . "etc" . DS . ".gnupg";
		putenv('GNUPGHOME=' . $dir);

		$this->dir = Mage::getBaseDir('var') . DS . "Borderfree" . DS . "orders" . DS;
		umask(0);
		$this->batchId = date("mdyHis");

		if(!is_dir(Mage::getBaseDir('var') .DS . "Borderfree"))
			mkdir(Mage::getBaseDir('var') .DS . "Borderfree");

		if(!is_dir($this->dir))
			mkdir($this->dir);

		if(!is_dir($this->dir . "errors"))
			mkdir($this->dir . "errors");
	}

	public function getPoFiles()
	{
		try
		{
			$server = Mage::getStoreConfig('borderfree_options/settings/staging') ? Mage::getStoreConfig('borderfree_options/catalog/ftpstage') : Mage::getStoreConfig('borderfree_options/catalog/ftpprod');
			$username = Mage::getStoreConfig('borderfree_options/catalog/ftpuser');
			$password  = Mage::helper('core')->decrypt(Mage::getStoreConfig('borderfree_options/catalog/ftppassword'));

			//$conn_id = ftp_ssl_connect($server);
			$conn_id = ftp_connect($server);
			if(!$conn_id)
				throw new Exception("Unable to connect to FTP server: $server");

			$login_result = ftp_login($conn_id, $username, $password);
			if(!$login_result)
				throw new Exception("Login to FTP server $server failed for user $username");

			ftp_pasv($conn_id, true);

			$chdir = ftp_chdir($conn_id, "/Outbox/Orders");
			if(!$chdir)
				throw new Exception("Unable to change directory to /Outbox/Orders on server $server");

			$files = ftp_nlist($conn_id, ".");
			foreach($files as $file)
			{
				if(strpos($file, "asc") > 0)
				{
					$get = ftp_get($conn_id, $this->dir . $file, $file, FTP_BINARY);
					ftp_delete($conn_id, $file);
				}
			}
		}
		catch(Exception $e)
		{
			$this->logError("Get PO files: " . $e->getMessage());
		}
		if($conn_id) { 
			ftp_close($conn_id);
		}
	}


	public function import()
	{
		$model = Mage::getModel('borderfreecrontab/log');
		$model->setType("Import Orders");
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
						$this->merchantIds[] = Mage::getStoreConfig('borderfree_options/settings/merchantid');
						$this->getPoFiles();
						$this->importPoFiles();
					}
					catch(Exception $e)
					{
						$this->logError($e->getMessage());
					}

				}
			}
		}

		$this->sendErrorLog();
	}

	private function importPoFiles()
	{
		$gpg = new gnupg();
		$result = $gpg->import(Mage::getStoreConfig('borderfree_options/order/pgp'));
		$gpg->adddecryptkey($result["fingerprint"], "");
		$dir = dir($this->dir);
		while ((false !== ($file = $dir->read())))
		{
			try
			{
				if(strpos($file, "asc") > 0)
				{
					$enc = file_get_contents($this->dir . $file);
					$orderXml = $gpg->decrypt($enc);
					$this->importPoFile($orderXml, $file);
				}
			}
			catch(Exception $e)
			{
				$this->logError("Error decrypting PO file $file: " . $e->getMessage());
				rename($this->dir . $file, $this->dir . "errors" . DS. $file);
			}
		}
		$dir->close();
	}

	private function setQuoteAddress($quoteAdderss, $address)
	{
		$quoteAdderss->setFirstname((string)$address->FirstName);
		$quoteAdderss->setMiddlename((string)$address->MiddleInitials);
		$quoteAdderss->setLastname((string)$address->LastName);
		$quoteAdderss->setCity((string)$address->City);
		$quoteAdderss->setCountryId("US");
		$quoteAdderss->setPostcode((string)$address->PostalCode);
		$quoteAdderss->setTelephone((string)$address->PrimaryPhone);
		$quoteAdderss->setSameAsBilling(0);

		$state = $address->Region;
		$regionModel = Mage::getModel('directory/region')->loadByCode($state, "US");
		$quoteAdderss->setRegionId($regionModel->getId());


		$street = array();
		$street[] = (string)$address->AddressLine1;
		$street[] = (string)$address->AddressLine2;
		$street[] = (string)$address->AddressLine3;
		$quoteAdderss->setStreet($street);

		$quoteAdderss->save();
	}

	/**
	 * Import a purchase order
	 *
	 * @todo quote not found
	 * @todo quote active
	 * @todo totals do not match
	 * @todo already has increment ID
	 *
	 * @param string $xml PO XML
	 * @param string $file PO filename
	 */
	private function importPoFile($xml, $file)
	{
		$hasError = false;
		$orders = new SimpleXMLElement($xml);
		foreach($orders->Order as $order)
		{
			try
			{
				$quoteId = (string)$order->OrderId->MerchantOrderRef;
				$fraudState = (string)$order->FraudState;
				$quote = Mage::getModel("sales/quote")->load($quoteId);
				$blind = ($quote->getId() == NULL);

				if($blind)
					$quote->save();

				if($fraudState == "GREEN")
				{
					$quote->setBorderfreeOrderId((string)$order->OrderId->E4XOrderId);
					$domesticProfile = $order->DomesticProfile;
					$billingAddress = $domesticProfile->Address[0];
					$quote->setCustomerEmail((string)$billingAddress->Email);
					$this->setQuoteAddress($quote->getBillingAddress(), $billingAddress);
					$shippingAddress = $domesticProfile->Address[1];
					$this->setQuoteAddress($quote->getShippingAddress(), $shippingAddress);

					$quote->setStore(Mage::app()->getStore());
					$quote->setBaseCurrencyCode("USD");
					$quote->setCurrencyCode("USD");

					$items = $order->DomesticBasket->BasketDetails->BasketItem;
					$totalProductExtraShipping = floatval(0);
					$totalProductExtraHandling = floatval(0);
					if($blind)
					{

					foreach($items as $item)
					{
						$sku = (string)$item->MerchantSKU;
						$qty = new Varien_Object(array('qty' => (string)$item->ProductQuantity));
						$price = (string)$item->ProductSalePrice;
						$product = Mage::helper('catalog/product')->getProduct($sku, Mage::app()->getStore()->getId(), 'sku');
						$quoteItem = $quote->addProduct($product, $qty);
						$quoteItem->setCustomPrice($price);
						$quoteItem->setOriginalCustomPrice($price);
						$totalProductExtraShipping += $item->ProductExtraShipping;
						$totalProductExtraHandling += $item->ProductExtraHandling;
						//$quoteItem->getProduct()->setIsSuperMode(true);
					}
					
					}
					else
					{
					    foreach($quote->getAllItems() as $qItem)
					    {
						foreach($items as $item)
						{
						    if($qItem->getSku() == (string)$item->MerchantSKU)
						    {
							$price = (string)$item->ProductSalePrice;
                	                                $qItem->setCustomPrice($price);
                        	                        $qItem->setOriginalCustomPrice($price);
                                	                $totalProductExtraShipping += $item->ProductExtraShipping;
                                        	        $totalProductExtraHandling += $item->ProductExtraHandling;					    
							$qItem->getProduct()->setIsSuperMode(true);
						    }
						}
					    }
					}




					$domesticShippingPrice = floatval($order->COPShippingMethod->DeliveryServiceType->ShippingPrice);
		                        $domesticHandlingPrice = floatval($order->COPShippingMethod->DeliveryServiceType->HandlingPrice);
                			$extraInsurancePrice = floatval($order->COPShippingMethod->DeliveryServiceType->ExtraInsurancePrice);
		                        $totalShipping = $domesticShippingPrice + $domesticHandlingPrice + $extraInsurancePrice + $totalProductExtraShipping + $totalProductExtraHandling;

					$method = Mage::getStoreConfig('borderfree_options/shipping/method');
					$shippingAddress = $quote->getShippingAddress();
					$shippingAddress->setCollectShippingRates(true);
					$shippingAddress->collectShippingRates();
					$shippingAddress->setShippingMethod($method);
					$shippingAddress->collectTotals();
					$shippingAddress->save();
					$quote->collectTotals();
					$quote->save();


					$shippingAddress->setShippingAmount($totalShipping);
					$shippingAddress->setBaseShippingAmount($totalShipping);
					$shippingAddress->setBorderfree(true);
					$quote->setTotalsCollectedFlag(false);
					$quote->collectTotals()->save();

					$customerId = $quote->getCustomerId();
					if($customerId == NULL)
					{
						$quote->setCustomerIsGuest(1);
						$address = $order->Marketing->Address[1];
						$quote->setCustomerFirstname((string)$address->FirstName);
						$quote->setCustomerMiddlename((string)$address->MiddleInitials);
						$quote->setCustomerLastname((string)$address->LastName);
						$quote->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
					}

					$payment = array();
					$payment["method"] = (string)Mage::getStoreConfig('borderfree_options/order/method');

					if($payment["method"] != "checkmo")
					{
						$payment["cc_owner"] = (string)$order->CreditCard->NameOnCard;
						if(strpos((string)$order->CreditCard->Type, "A") === 0)
							$payment["cc_type"] = "AE";
						else if(strpos((string)$order->CreditCard->Type, "V") === 0)
							$payment["cc_type"] = "VI";
						else if(strpos((string)$order->CreditCard->Type, "M") === 0)
							$payment["cc_type"] = "MC";
						else if(strpos((string)$order->CreditCard->Type, "D") === 0)
							$payment["cc_type"] = "DI";
						$payment["cc_number"] = (string)$order->CreditCard->Number;
						$payment["cc_exp_month"] = intval((string)$order->CreditCard->Expiry->Month);
						$payment["cc_exp_year"] = intval((string)$order->CreditCard->Expiry->Year);
						$payment["cc_cid"] = (string)$order->CreditCard->CVN;
					}

					$quote->setTotalsCollectedFlag(true);
					$quote->getBillingAddress()->setPaymentMethod($payment['method']);
					$quote->getPayment()->importData($payment);

					$service = Mage::getModel('sales/service_quote', $quote);
					$service->submitAll();

					$morder = $service->getOrder();
					/*if ($morder && $morder->getCanSendNewEmailFlag())
							$morder->sendNewOrderEmail();*/

					$profiles = $service->getRecurringPaymentProfiles();
					Mage::dispatchEvent(
							'checkout_submit_all_after',
							array('order' => $morder, 'quote' => $quote, 'recurring_profiles' => $profiles)
					);

					$quote->save();
					if($blind)
					{
						$observer = new Varien_Event_Observer();
						$event = new Varien_Event(array('quote'=>$quote));
						$observer->setEvent($event);
						$inv = Mage::getModel("cataloginventory/observer");
						$inv->subtractQuoteInventory($observer,true);
						$inv->reindexQuoteInventory($observer);
					}

					$confirmed = $this->confirmOrder($morder->getIncrementId(), (string)$order->OrderId->E4XOrderId);
					if(!$confirmed)
						$hasError = true;

				}
				elseif(!$blind)
				{
					$observer = new Varien_Event_Observer();
					$event = new Varien_Event(array('quote'=>$quote));
					$observer->setEvent($event);
					$inv = Mage::getModel("cataloginventory/observer");
					$inv->revertQuoteInventory($observer);
					$inv->reindexQuoteInventory($observer);
				}

				$email = (string)$order->Marketing->Address[1]->Email;
				if(!empty($email))
				{
					$data = $order->Marketing->Address[1];
					$record = Mage::getModel("borderfreemarketing/record")->load($email);
					$record->setEmail($email);
					$record->setFirstName($data->FirstName);
					$record->setMiddleInitials($data->MiddleInitials);
					$record->setLastName($data->LastName);
					$record->setAddress1($data->AddressLine1);
					$record->setAddress2($data->AddressLine2);
					$record->setAddress3($data->AddressLine3);
					$record->setCity($data->City);
					$record->setRegion($data->Region);
					$record->setCountry($data->setCountry);
					$record->setPostalCode($data->PostalCode);
					$record->setPrimaryPhone($data->PrimaryPhone);
					$record->setSecondaryPhone($data->SecondaryPhone);
					$record->save();
				}

			}
			catch(Exception $e)
			{
				$this->logError("Error Importing order - PO file $file: " . $e->getMessage());
				$hasError = true;
			}
		}

		if($hasError)
			rename($this->dir . $file, $this->dir . "errors" . DS. $file);
		else
			unlink($this->dir . $file);
	}

	private function confirmOrder($merchantOrderId, $orderId)
	{
		$ns = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';

		$token = new stdClass;
		$token->Username = new SOAPVar(Mage::getStoreConfig('borderfree_options/settings/apiuser'), XSD_STRING, null, null, null, $ns);
		$token->Password = new SOAPVar(Mage::helper('core')->decrypt(Mage::getStoreConfig('borderfree_options/settings/apipassword')), XSD_STRING, null, null, null, $ns);

		$wsec = new stdClass;
		$wsec->UsernameToken = new SoapVar($token, SOAP_ENC_OBJECT, null, null, null, $ns);

		$header = new SOAPHeader($ns, 'Security', $wsec, true);

		$orderConfirmation = new stdClass();
		$orderConfirmation->order = new stdClass();
		$orderConfirmation->order->merchantOrderId = $merchantOrderId;
		$orderConfirmation->order->orderId = $orderId;

		$wsdl = Mage::getStoreConfig('borderfree_options/settings/staging') ? Mage::getStoreConfig('borderfree_options/order/staging') : Mage::getStoreConfig('borderfree_options/order/production');
		$client = new SoapClient($wsdl);
		$client->__setSoapHeaders($header);
		try
		{
			$response = $client->orderConfirmation($orderConfirmation);
		}
		catch(Exception $e)
		{
			$this->logError("Order Confirmation API Error - Order: $merchantOrderId - " . $e->getMessage());
			return false;
		}

		return true;
	}

	private function sendErrorLog()
	{
		if(empty($this->errors))
			return;

		mail(Mage::getStoreConfig('borderfree_options/settings/erroremail'), $this->subject, $this->errors);
		$this->errors = "";

	}

	private function logError($message)
	{
		$this->errors .= $message . "\n";
		Mage::log($message, Zend_Log::ERR, "borderfree_order_import.log");
	}
}
