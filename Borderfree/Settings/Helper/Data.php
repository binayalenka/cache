<?php
/**
* This code is part of the Borderfree Magento Extension.
*
* @category Borderfree
* @package Borderfree_Settings
* @author Jamie Kail <jamie.kail@livearealabs.com>
* @copyright Copyright (c) 2013 Borderfree (http://www.borderfree.com)
*
*/
?>
<?php
/**
 * The Borderfree Settings helper class which provides access to the Borderfree Site Settings.
 *
 * @category Borderfree
 * @package Borderfree_Settings
 * @author Jamie Kail <jamie.kail@livearealabs.com>
 * @copyright Copyright (c) 2013 Borderfree (http://www.borderfree.com)
 */
class Borderfree_Settings_Helper_Data extends Mage_Core_Helper_Abstract
{
		
	/**
	 * Get the Merchant ID for the current store
	 * 
	 * @return string
	 */
	public function getMerchantId()
	{
		return Mage::getStoreConfig('borderfree_options/settings/merchantid');
	}
	
	/**
	 * Is Borderfree Enabled
	 * 
	 * @param $internationalOnly Return true only if shipping country != US
	 * @return boolean
	 */
	public function isBorderfreeEnabled($internationalOnly = true)
	{
		$international = true;
		if($internationalOnly)
			$international = Mage::helper('borderfreelocalization')->getShippingCountry() != "US";
			
		return Mage::getStoreConfig('borderfree_options/settings/enabled') && $international;
	}
	
	/**
	 * Is the Borderfree Welcome Mat Enabled
	 * 
	 * @return boolean
	 */
	public function isWelcomatEnabled()
	{
		return $this->isBorderfreeEnabled() && Mage::getStoreConfig('borderfree_options/settings/welcomemat');
	}
	
	/**
	 * Is staging mode enabled
	 * 
	 * @return boolean
	 */
	public function isStagingEnabled()
	{
		return Mage::getStoreConfig('borderfree_options/settings/staging');
	}
	
	/**
	 * Gets the international parcel tracking URL prefix.
	 * 
	 * @return string
	 */
	public function getEnvironment()
	{
		if($this->isStagingEnabled())
			return "sandbox";
		return "embassy";
	}
	
	/**
	 * Get the height of the international parcel tracking page
	 * 
	 * @return integer
	 */
	public function getTrackHeight()
	{
		return Mage::getStoreConfig('borderfree_options/shipping/height');
	}
	
	/**
	 * Is this request from the test IP
	 * 
	 * @return bool
	 */
	public function isTestIp()
	{
		return Mage::getStoreConfig('borderfree_options/settings/testip') == $_SERVER['REMOTE_ADDR'];
	}

	/**
	 * Get the test country code
	 * 
	 * @return string
	 */
	public function getTestCountry()
	{
		return Mage::getStoreConfig('borderfree_options/settings/testcountry');
	}
	
	/**
	 * Get the credentials for the Borderfree API
	 * 
	 * @return string
	 */
	public function getApiCredentials()
	{
		return Mage::getStoreConfig('borderfree_options/settings/apiuser') . ":" . Mage::helper('core')->decrypt(Mage::getStoreConfig('borderfree_options/settings/apipassword'));		
	}
	
	/**
	 * Detect if this request is a StoreFront Request
	 */
	public function isStoreFrontRequest()
	{
		$uri = Mage::app()->getRequest()->getRequestUri();
		if(strpos($uri, 'api') || Mage::app()->getStore()->isAdmin() || Mage::getDesign()->getArea() == 'adminhtml' || strpos($uri, 'downloader') || strpos($uri, 'fileuploader'))
			return false;
		else
			return true;
	}
	
}
?>