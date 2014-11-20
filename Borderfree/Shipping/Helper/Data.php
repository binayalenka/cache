<?php
/**
 * This code is part of the Borderfree Magento Extension.
 * 
 * @category Borderfree
 * @package Borderfree_Shipping
 * @author Jamie Kail <jamie.kail@livearealabs.com>
 * @copyright Copyright (c) 2013 Borderfree (http://www.borderfree.com)
 *
 */
?>
<?php
/**
 * Borderfree Shipping Helper
 * 
 * @category Borderfree
 * @package Borderfree_Shipping
 * @author Jamie Kail <jamie.kail@livearealabs.com>
 *
 */
class Borderfree_Shipping_Helper_Data extends Mage_Core_Helper_Abstract
{
	/**
	 * Get link to Borderfree International Parcel Tracking page
	 * 
	 * @return string
	 */
	public function getTrackUrl()
	{
		return $this->_getUrl("borderfreeshipping/track/shipment");
	}	
}
?>