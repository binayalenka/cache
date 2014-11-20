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
 * FiftyOnne Country List Block
 * 
 * @category Borderfree
 * @package Borderfree_Localization
 * @author Jamie Kail <jamie.kail@livearealabs.com>
 *
 */
class Borderfree_Localization_Block_Country extends Mage_Core_Block_Template
{
	/**
	 * Refrence to the localization helper
	 *
	 * @var Borderfree_Localization_Helper_Data
	 */
	private $localizationHelper = NULL;
	
	/**
	 * Refrence to the localization helper
	 *
	 * @var Borderfree_Localization_Helper_Url
	 */
	private $localizationUrlHelper = NULL;

	/**
	 * Constroctor - gets the Borderfree Localization & URL Helper
	 */
	public function __construct()
	{
		$this->localizationHelper = Mage::helper('borderfreelocalization');
		$this->localizationUrlHelper = Mage::helper('borderfreelocalization/url');
	}
	
	/**
	 * Get Borderfree ShipTo Countries
	 * 
	 * @return array
	 */
    public function getCountries()
    {
        $countries = $this->getData('countries');
        if (is_null($countries)) 
        {
            $countries = array();
            
            //get the Borderfree ship to countries from the Borderfree Site Cache
            $collection = Mage::getModel("borderfreelocalization/country")->getCollection()->addFieldToFilter("ship_to", true)->setOrder("name", "ASC");
            
            //create array of Country Code => Country Name
            foreach ($collection as $country) 
            {
				$countries[$country->getCountryCode()] = $country->getName();
            }

            $this->setData('countries', $countries);
        }
        return $countries;
    }

    /**
     * Get the switch country URL from the give country code
     * 
     * @param string $code
     * @return string
     */
    public function getSwitchCountryUrl($code)
    {
        return $this->localizationUrlHelper->getSwitchCountryUrl(array('country' => $code));
    }
        
	/**
	 * Is Borderfree enabled
	 * 
	 * @return boolean
	 */
    public function getBorderfreeEnabled()
    {
    	return Mage::helper('borderfreesettings')->isBorderfreeEnabled(false);
    }
    
    /**
     * Get the current Shipping Country
     * 
     * @return string
     */
    public function getShippingCountry()
    {
    	return $this->localizationHelper->getShippingCountry();
    }
}
