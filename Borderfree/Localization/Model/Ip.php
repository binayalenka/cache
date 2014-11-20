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
 * IP Geolocation data model
 *
 * @category Borderfree
 * @package Borderfree_Localization
 * @author Jamie Kail <jamie.kail@livearealabs.com>
 */
class Borderfree_Localization_Model_Ip extends Mage_Core_Model_Abstract 
{
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
	
	protected function _construct()
    {
        $this->_init('borderfreelocalization/ip');
        $this->localizationHelper = Mage::helper("borderfreelocalization");
    	$this->settingsHelper = Mage::helper('borderfreesettings');
    }

    /**
     * Get the current IP country using Geo Location data and set the currency/language
     *
     */
    public function getIpCountry()
    {
    	//get IP contry form session if it exists
    	$ipCountry = $this->localizationHelper->getIpCountry();
    
    	//Determine IP country based on Geo Location data
    	if(empty($ipCountry))
    	{
    		//Set IP country to US by default
    		$ipCountry = "US";
    		 
    		//convert IP dotted quad IP address into decimal notation
    		$ipNumbers = explode(".", $_SERVER['REMOTE_ADDR']);
    		$ip = $ipNumbers[0] * (16777216) + $ipNumbers[1] * (65536) + $ipNumbers[2] * (256) + $ipNumbers[3] * (1);
    		 
    		//search for IP address in Geo Location database
    		$collection = $this->getCollection()
    		->addFieldToFilter("high", array('gteq' => $ip))
    		->addFieldToFilter("low", array('lteq' => $ip));
    		 
    		//If an entry in the Geo Location database is found, set the IP country
    		if($collection->count() > 0)
    			$ipCountry = $collection->getFirstItem()->getCountryCode();
    		 
    		//If request is coming from the test IP address, set the IP country to the Mock country.
    		if($this->settingsHelper->isTestIp())
    			$ipCountry = $this->settingsHelper->getTestCountry();
    
    		//set IP/shipping country, currency & language
    		Mage::getModel("borderfreelocalization/country")->_setLocale($ipCountry);
    	}
    }
    
} 
?>