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
 * Overides Mage_Tax_Model_Calculation to ensure international customers are not charged sales tax
 * 
 * @category Borderfree
 * @package Borderfree_Localization
 * @author Jamie Kail <jamie.kail@livearealabs.com>
 * @see Mage_Tax_Model_Calculation
 *
 */
class Borderfree_Localization_Model_Tax extends Mage_Tax_Model_Calculation
{
	/**
	 * Forces sales tax for international orders to zero
	 * 
	 * @param $request
	 * @return float
	 * @see Mage_Tax_Model_Calculation::getRate()
	 */
	public function getRate($request)
	{
		if(Mage::getSingleton('customer/session')->getShippingCountry() != "US")
			return 0;
		
		return parent::getRate($request);
	}
}