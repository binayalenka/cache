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
 * Overides Mage_Core_Model_Store to set Akamai cookie when currency is changed
 * 
 * @category Borderfree
 * @package Borderfree_Localization
 * @author Jamie Kail <jamie.kail@livearealabs.com>
 * @see Mage_Core_Model_Store
 *
 */
class Borderfree_Localization_Model_Store extends Mage_Core_Model_Store
{
	/**
	 * Set the Borderfree Akamai Cookie when store currency is changed.
	 * 
	 * @param $code Currency Code
	 * @return Borderfree_Localization_Model_Store
	 * @see Mage_Core_Model_Store::setCurrentCurrencyCode()
	 */
    public function setCurrentCurrencyCode($code)
    {
    	$countryCode = Mage::getSingleton('customer/session')->getShippingCountry();
    	$rate = Mage::getModel("directory/currency")->setCurrencyCode("USD")->getRate($code);
    	$lcp = Mage::helper("borderfreelocalization")->getLCPMultiplier();
    	$rate = $rate * $lcp;
    	    	
    	$code = strtoupper($code);
    	if (in_array($code, $this->getAvailableCurrencyCodes())) 
    	{
    		$this->_getSession()->setCurrencyCode($code);
    		Mage::app()->getCookie()->set(self::COOKIE_CURRENCY, "$countryCode|$code|$rate", 0);
    	}
    	 
    	if(Mage::getStoreConfig('borderfree_options/settings/akamai'))
    		setcookie("Borderfree_Akamai", "$countryCode|$code|$rate", 0, "/");
    	 
    	return $this;
    }
}
