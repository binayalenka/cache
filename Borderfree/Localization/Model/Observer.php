<?php
/**
 * This code is part of the Borderfree Magento Extension.
 * 
 * @category Borderfree
 * @package Borderfree_Localization
 * @author Jamie Kail <jamie.kail@livearealabs.com>
 * @copyright Copyright (c) 2013 Borderfree (http://www.borderfree.com)
 *
 */
?>
<?php
/**
 * TheBorderfree Localization Observer
 * 
 * Observes the following events:
 * 
 * fiftonelocalization_predispatch_observer
 * controller_action_layout_load_before
 *
 * @category Borderfree
 * @package Borderfree_Localization
 * @author Jamie Kail <jamie.kail@livearealabs.com>
 *
 */
class Borderfree_Localization_Model_Observer
{
	
	/**
	 * 
	 * @var Mage_Core_Controller_Request_Http
	 */
	private $request = NULL;
	/**
	 * 
	 * @var Mage_Core_Controller_Response_Http
	 */
	private $response = NULL;
	
	/**
	 * Refrence to the localization helper
	 * 
	 * @var Borderfree_Localization_Helper_Data
	 */
	private $localizationHelper = NULL;

	/**
	 * Refrence to the settings helper
	 * 
	 * @var Borderfree_Settings_Helper_Data
	 */
	private $settingsHelper = NULL;
	
	/**
	 * The attribute option ID for "All" from catalog_country_restrictions 
	 * 
	 * @var integer
	 */
	private $allCountries = NULL;
	
	/**
	 * Borderfree restricted categories
	 * 
	 * @var array
	 */
	private $restrictedCategories = array();
	
	/**
	 * 
	 * @var Mage_Core_Model_Cookie;
	 */
	private $cookies = NULL;
	

	/**
	 * Constroctor - gets the Borderfree Localization Helper
	 */
	public function __construct()
	{
		$this->localizationHelper = Mage::helper('borderfreelocalization');
		$this->settingsHelper = Mage::helper('borderfreesettings');
		$this->cookies = Mage::getModel("core/cookie");
		$this->allCountries = $this->localizationHelper->getOptionIdByValue("catalog_category", "category_country_restrictions", "All");	
	}
			
	/**
	 * Observer which detetcs foregin site visits and requests for Language changes
	 * 
	 * Observes: controller_action_predispatch
	 * 
	 * If Borderfree is enbled and this is the first request, set IP Country, Shipping Country and Language.
	 * Check For "switchStore" cookie and swithch to the requested store view
	 * 
	 * @param Varien_Event_Observer $observer
	 * @return Borderfree_Localization_Model_Observer
	 */
	public function preDispatch(Varien_Event_Observer $observer)
	{
		if($this->settingsHelper->isBorderfreeEnabled(false))
		{
			//make sure this is a storefront request
			if(!$this->settingsHelper->isStoreFrontRequest())
				return $this;

			//Save the request and response data from the observer data
  			$this->response = $observer->getControllerAction()->getResponse();
  			$this->request = $observer->getControllerAction()->getRequest();

  			
  			//logout customer if shipping internationally
  			if(Mage::helper('customer')->isLoggedIn() && $this->settingsHelper->isBorderfreeEnabled())
  			{
  				//logout customer
  				Mage::getSingleton('customer/session')->logout();
  			}
  			
  			//set IP & shipping countries from cookies
  			$this->localizationHelper->setIpCountry($this->cookies->get("ipCountry"));
  			$this->localizationHelper->setShippingCountry($this->cookies->get("shippingCountry"));

  			
  			//redirect customer account URls to home page if shipping internationally 
  			if($this->settingsHelper->isBorderfreeEnabled()  && $this->request->getRouteName() == "customer")
  			{
  				$this->response->setRedirect(Mage::getBaseUrl());
  				//$this->request->setDispatched();
  				return;
  			}	

  			//lookup the IP Country using geolocation data
  			Mage::getModel("borderfreelocalization/ip")->getIpCountry();
  			
  			Mage::getModel("borderfreelocalization/country")->switchStore($this->cookies->get("switchStore"));
		}
		else  //default shipping & IP country to US 
			$this->localizationHelper->setIpCountry("US");
		
		$currencyCoookie = $this->cookies->get("currency");
		if(!empty($currencyCoookie))
		{
			$currency = explode("|", $currencyCoookie);
			Mage::app()->getStore()->setCurrentCurrencyCode($currency[1]);
		}
		
						
		return $this;
	}
	
	/**
	 * Observer which adds layout handle Borderfree when shippingCountry != US
	 * 
	 * Observes: controller_action_layout_load_before	 
	 *  
	 * @param $observer Varien_Event_Observer
	 * @return Borderfree_Localization_Model_Observer
	 */
	public function updateLayout(Varien_Event_Observer $observer)
	{
		if($this->settingsHelper->isBorderfreeEnabled())
		{
			$update = $observer->getEvent()->getLayout()->getUpdate();
			$update->addHandle('borderfree');
		}
		
		return $this;
	}
			  
    /**
     * Apply category view for tree based on Borderfree restrictions
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_CatalogPermissions_Model_Observer
     */
    public function applyCategoryInactiveIds(Varien_Event_Observer $observer)
    {
        if (!$this->settingsHelper->isBorderfreeEnabled()) {
            return $this;
        }

        $this->restrictedCategories = array();
        $currentCountryCode = $this->localizationHelper->getShippingCountry();
        $currentCountryName = $this->localizationHelper->codeToCountry($currentCountryCode);
        $currentCountryId = $this->localizationHelper->getOptionIdByValue("catalog_category", "category_country_restrictions", $currentCountryName);
                        
        $categories = Mage::getModel("catalog/category")->getCollection()->addFieldToFilter('borderfree_restricted', 1);
        foreach($categories as $category)
        	$this->addRestrictedCategory($category);

        $categories = Mage::getModel("catalog/category")->getCollection()->addFieldToFilter('category_country_restrictions', array('finset' => $this->allCountries));
        foreach($categories as $category)
        	$this->addRestrictedCategory($category);
                
        $categories = Mage::getModel("catalog/category")->getCollection()->addFieldToFilter('category_country_restrictions', array('finset' => $currentCountryId));
        foreach($categories as $category)
        	$this->addRestrictedCategory($category);
                
        $observer->getEvent()->getTree()->addInactiveCategoryIds($this->restrictedCategories);

        return $this;
    }
    
    public function applyProductPermissionOnCollection(Varien_Event_Observer $observer)
    {
		if (!$this->settingsHelper->isBorderfreeEnabled()) 
            return $this;
    	    

    	$currentCountryCode = $this->localizationHelper->getShippingCountry();
    	$currentCountryName = $this->localizationHelper->codeToCountry($currentCountryCode);
    	$currentCountryId = $this->localizationHelper->getOptionIdByValue("catalog_product", "country_restrictions", $currentCountryName);
    	    		
		$allCountries = "(#? IS NOT NULL AND FIND_IN_SET('" . $this->localizationHelper->getOptionIdByValue("catalog_product", "country_restrictions", "All") . "', #?))";	
		$currentCountry = "(#? IS NOT NULL AND FIND_IN_SET('" . $currentCountryId . "', #?))";
		
		$collection = $observer->getEvent()->getCollection();
    	$collection = $collection->addAttributeToFilter('borderfree_restricted', array("neq" => 1));
    	$collection = $collection->addAttributeToFilter('country_restrictions', array('eq' => 0, 'field_expr' => $allCountries), "left");
    	$collection = $collection->addAttributeToFilter('country_restrictions', array('eq' => 0, 'field_expr' => $currentCountry), "left");
    	
    	return $this;
    }
    

    /**
     * 
     * 
     * @param Mage_Catalog_Model_Category $category
     */
	private function addRestrictedCategory(Mage_Catalog_Model_Category $category) 
	{
		$this->restrictedCategories[] = $category->getId();
		array_merge($this->restrictedCategories, $this->getChildCategoyIds($category));
	}

    
    /**
     * Returns an arry of child category IDs
     * 
     * @param Mage_Catalog_Model_Category $category
     * @return array
     */
    private function getChildCategoyIds(Mage_Catalog_Model_Category $category)
    {
    	$childCategoryIds = array();
    	
    	foreach($category->getChildrenCategories() as $category)
    	{
    		$childCategoryIds[] = $category->getId();
    		array_merge($childCategoryIds, $this->getChildCategoyIds($category));
    	}
    	
    	return $childCategoryIds;
    }
    
    /**
     * Checks if given category is restricted for shipping to current country
     * 
     * @param Mage_Catalog_Model_Category $category
     * @return boolean
     */
    private function isCategoryRestricted(Mage_Catalog_Model_Category $category)
    {
    	if($this->settingsHelper->isBorderfreeEnabled())
    	{    		
    		if($this->checkCategoryRestrictions($category))
    			return true;

    		$categories = $category->getParentCategories();
    		
    		foreach($categories as $category)
    		{
    			$this->checkCategoryRestrictions($category);
    		}
    	}
    	
    	return false;
    }
    
	/**
	 * Check for Borderfree category restrictions.
	 * 
	 * @param Mage_Catalog_Model_Category $category
	 * @return boolean
	 */
	private function checkCategoryRestrictions(Mage_Catalog_Model_Category $category) 
	{
		$category = Mage::getModel('catalog/category')->load($category->getId());
		
		$currentCountryCode = $this->localizationHelper->getShippingCountry();
		$currentCountryName = $this->localizationHelper->codeToCountry($currentCountryCode);
		$currentCountryId = $this->localizationHelper->getOptionIdByValue("catalog_category", "category_country_restrictions", $currentCountryName);
		
		if($category->getFiftyoneRestricted())
			return true;
		
		$countryRestrictions = explode(",", $category->getCategoryCountryRestrictions());
		
		if($countryRestrictions != NULL && in_array($this->allCountries, $countryRestrictions))
			return true;
		
		if($countryRestrictions != NULL && in_array($currentCountryId, $countryRestrictions))
			return true;
		
		return false;
	}

    
    /**
     * Returns user to the home page is current category is restricted for current country.
     * 
     * @param Varien_Event_Observer $observer
     * @return  @return FiFtyOne_Localization_Model_Observer
     */
    public function applyCategoryPermission(Varien_Event_Observer $observer)
    {
    	if($this->settingsHelper->isBorderfreeEnabled())
    	{
    		$category = $observer->getEvent()->getCategory();
    		if($this->isCategoryRestricted($category))
    		{
    			$this->response = $observer->getEvent()->getControllerAction()->getResponse();
    			$this->response->setRedirect(Mage::getBaseUrl());
    			Mage::throwException($this->localizationHelper->__('Products in this category to not ship to your selected shipping country'));
    		}
    	}
    	
    	return $this;
    }
    
    public function applyCategoryPermissionOnIsActiveFilterToCollection(Varien_Event_Observer $observer)
    {
        if (!$this->settingsHelper->isBorderfreeEnabled()) {
            return $this;
        }

        $this->restrictedCategories = array();
        $currentCountryCode = $this->localizationHelper->getShippingCountry();
        $currentCountryName = $this->localizationHelper->codeToCountry($currentCountryCode);
        $currentCountryId = $this->localizationHelper->getOptionIdByValue("catalog_category", "category_country_restrictions", $currentCountryName);
                        
        $categories = Mage::getModel("catalog/category")->getCollection()->addFieldToFilter('category_country_restrictions', array('finset' => $this->allCountries));
        foreach($categories as $category)
        	$this->addRestrictedCategory($category);
                
        $categories = Mage::getModel("catalog/category")->getCollection()->addFieldToFilter('category_country_restrictions', array('finset' => $currentCountryId));
        foreach($categories as $category)
        	$this->addRestrictedCategory($category);
    	    
    	$categoryCollection = $observer->getEvent()->getCategoryCollection()->addFieldToFilter('borderfree_restricted', array("neq" => 1));
		
    	$categoryCollection->addFieldToFilter("entity_id", array("nin" => $this->restrictedCategories));
    	
    	return $this;
    }
    
    public function removeShippingMethod(Varien_Event_Observer $observer)
    {
    	if($this->settingsHelper->isBorderfreeEnabled())
    	{
	    	$quote = Mage::getModel('checkout/cart')->getQuote();
	    	$quote->getShippingAddress()->setShippingMethod("");
    	}
    	
    	return $this;
    }
    
}
?>
