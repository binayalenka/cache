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
 * Creates the URLs for the cournry selector
 * 
 * @see Borderfree_Localization_Block_Country::getCountryUrl()
 * @category Borderfree
 * @package Borderfree_Localization
 * @author Jamie Kail <jamie.kail@livearealabs.com>
 *
 */
class Borderfree_Localization_Helper_Url extends Mage_Core_Helper_Url
{
	
	/**
	 * Creates a switch country based on the key pair $params["country"} => Country Code
	 * 
	 * @see Borderfree_Localization_Block_Country::getCountryUrl()
	 * @param $params parameters to be passes in in the construced URL
	 */ 
    public function getSwitchCountryUrl($params = array())
    {
        $params = is_array($params) ? $params : array();

        //Set redirect URL for after the switchCountryAction is dispatched
        if ($this->_getRequest()->getAlias('rewrite_request_path'))
            $url = Mage::app()->getStore()->getBaseUrl() . $this->_getRequest()->getAlias('rewrite_request_path');
        else
            $url = $this->getCurrentUrl();
        
        //clean up the URL to prevent loops
        $url = preg_replace("/.___store=.*&___from_store=.*/", "", $url);
        $url = preg_replace("/checkout.onepage.index/", "checkout/cart", $url);
        $url = preg_replace("/borderfreecheckout.envoy.checkout/", "checkout/cart", $url);
        
        //encode the URL as a parameter
        $params[Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED] = Mage::helper('core')->urlEncode($url);

        return $this->_getUrl('borderfreelocalization/country/switch', $params);
    }

}
