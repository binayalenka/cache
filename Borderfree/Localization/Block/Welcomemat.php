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
 * Borderfree Welcome Mat Block
 *
 * @category Borderfree
 * @package Borderfree_Localization
 * @author Jamie Kail <jamie.kail@livearealabs.com>
 *
 */
class Borderfree_Localization_Block_Welcomemat extends Mage_Core_Block_Template
{

	/**
	 * Settings Helper
	 * 
	 * @var Borderfree_Settings_Helper_Data
	 */
	private $settingsHelper = NULL;
	
	/**
	 * Localization Helper
	 * 
	 * @var Borderfree_Localization_Helper_Data
	 */
	private $localizationHelper = NULL;
	
	/**
	 * Constructor: Get settings & localization Helpers
	 */
	public function __construct()
	{
		$this->settingsHelper = Mage::helper('borderfreesettings');
		$this->localizationHelper = Mage::helper('borderfreelocalization');
	}
	
	public function isWelcomeMatEnabled()
	{
		return $this->settingsHelper->isWelcomatEnabled();
	}
	
	/**
	 * Get the Welcome Mat URL
	 * 
	 * @return string
	 */
	public function getWelcomeURL()
	{
		$merchantId = $this->settingsHelper->getMerchantId();
		$countryId = $this->localizationHelper->getShippingCountry();
		
		if($this->settingsHelper->isStagingEnabled())
			return trim(Mage::getStoreConfig('borderfree_options/settings/welcomematstageurl')) . "?merchId=$merchantId&countryId=$countryId&setCookie=Y";
		else
			return trim(Mage::getStoreConfig('borderfree_options/settings/welcomematprodurl')) . "?merchId=$merchantId&countryId=$countryId&setCookie=Y";
	}	
}

