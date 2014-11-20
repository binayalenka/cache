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
 * This controler handles requests to switch shipping countries.
 * 
 * @category Borderfree
 * @package Borderfree_Localization
 * @author Jamie Kail <jamie.kail@livearealabs.com>
 *
 */
class Borderfree_Localization_CountryController extends Mage_Core_Controller_Front_Action
{
	/**
	 * Switches current shipping country based on "country" paramater.
	 */
	public function switchAction()
	{
		if ($country = (string) $this->getRequest()->getParam('country'))
			Mage::getModel("borderfreelocalization/country")->_setLocale($country, false);
			
		$this->_redirectReferer(Mage::getBaseUrl());
	}	
}