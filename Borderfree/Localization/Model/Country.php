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
 * Country data model
 *
 * @category Borderfree
 * @package Borderfree_Localization
 * @author Jamie Kail <jamie.kail@livearealabs.com>
 */
class Borderfree_Localization_Model_Country extends Mage_Core_Model_Abstract 
{
	/**
	 * Refrence to the settings helper
	 *
	 * @var Borderfree_Settings_Helper_Data
	 */
	private $settingsHelper = NULL;

	/**
	 * Refrence to the localization helper
	 *
	 * @var Borderfree_Localization_Helper_Data
	 */
	private $localizationHelper = NULL;
	
	protected function _construct()
    {
        $this->_init('borderfreelocalization/country');
        $this->settingsHelper = Mage::helper("borderfreesettings");
        $this->localizationHelper = Mage::helper("borderfreelocalization");
    }

    /**
     * Set the store currency/language based on the IP country.
     *
     * @param string $countryCode The country code used to lookup the currency/language
     * @param boolean $setIpCountry Switch IP Country true/false
     */
    public function _setLocale($countryCode, $setIpCountry = true)
    {
    	if($setIpCountry)
    		$this->localizationHelper->setIpCountry($countryCode);
    	 
    	//lookup country data in the Borderfree site cache
    	$collection = $this->getCollection()
    	->addFieldToFilter("country_code", $countryCode)->addFieldToFilter("merchant_id", $this->settingsHelper->getMerchantId());
    		
    	$country = $collection->getFirstItem();
    	$currencyCode = $country->getCurrencyCode();
    	$languageCode = $country->getLanguageCode();
    	
    	//make sure currency is enabled otherwise default to USD
    	if(Mage::getModel("borderfreelocalization/currency")->getCollection()->addFieldToFilter("currency_code", $currencyCode)->count() == 0)
    		$currencyCode = "USD";
    
    	if($countryCode == "US")
    	{
    		$currencyCode = "USD";
    		$languageCode = Mage::app()->getDefaultStoreView()->getCode();
    	}
    
    	//set the store language based on the current country
    	if($setIpCountry)
    		$this->switchStore($languageCode);
    	else
    		$this->switchCountry($countryCode);
    	 
    	//set store currency to the active country
    	Mage::app()->getStore()->setCurrentCurrencyCode($currencyCode);
    }
    
    /**
     * Switches the current shipping country and sets the appropiate store view.
     *
     * @param string $countryCode The new Country Code
     */
    public function switchCountry($countryCode)
    {
    	if(empty($countryCode))
    		return;
    
    	$this->localizationHelper->setShippingCountry($countryCode);
    		
    	if($countryCode != "US")
    	{
    		$merchantId = $this->settingsHelper->getMerchantId();
    
    		$collection = Mage::getModel("borderfreelocalization/country")->getCollection()
    		->addFieldToFilter("country_code", $countryCode)->addFieldToFilter("merchant_id", $merchantId);
    
    		$country = $collection->getFirstItem();
    
    		$store = Mage::app()->getSafeStore(strtolower($countryCode));
    		
    		if(Mage::app()->getStore()->getCode() == Mage::app()->getDefaultStoreView()->getCode())
    			Mage::getModel('core/cookie')->set("switchStore", "en", 0, "/");
    		else if($store->getCode() != null)
    			Mage::getModel('core/cookie')->set("switchStore", strtolower($countryCode), 0, "/");
    	}
    	else
    	{
    		if(Mage::app()->getStore()->getCode() == "en")
    			$storeCode = Mage::app()->getDefaultStoreView()->getCode();
    		else
    			$storeCode = Mage::app()->getStore()->getCode() . "_us";

    		$store = Mage::app()->getSafeStore(strtolower($storeCode));
    		
    		if($store->getCode() != null)
    			Mage::getModel('core/cookie')->set("switchStore", $storeCode, 0, "/");
    	}
    }
    
    /**
     * Try to switch to store view for the given language code
     *
     * @param string $languageCode The language code used to locate the proper store view
     */
    public function switchStore($languageCode)
    {
    	if(empty($languageCode))
    		return;
    
    	//try to find the store view than matchs the given language code
    	$store = Mage::app()->getSafeStore(strtolower($languageCode));
    
    	//default to the international English store view if shipping outside the US
    	if($store->getCode() == NULL && $this->settingsHelper->isBorderfreeEnabled())
    		$store = Mage::app()->getSafeStore("en");
    
    	//fall back to default store view
    	if($store->getCode() == NULL)
    		$store = Mage::app()->getDefaultStoreView();
    
    	//redirect to the new store view
    	if($store->getCode() != Mage::app()->getStore()->getCode())
    	{
    		$storeUrl = $store->getCurrentUrl(Mage::app()->getStore()->getCode());
    		Mage::app()->getResponse()->setRedirect(str_replace("&amp;", "&", $storeUrl));
    		//Mage::app()->getRequest()->setDispatched();
    	}
    
    	//Delete the switchStore cookie
    	Mage::getModel('core/cookie')->delete("switchStore");
    }
    
} 
?>