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
 * This controler handles requests to switch shipping countries.
 * 
 * @category Borderfree
 * @package Borderfree_Shipping
 * @author Jamie Kail <jamie.kail@livearealabs.com>
 *
 */
class Borderfree_Shipping_TrackController extends Mage_Core_Controller_Front_Action
{
	/**
	 * Render Borderfree International Parcel Tracking URL
	 */
	public function shipmentAction()
	{
		$this->loadLayout();
		$this->renderLayout();
	}	
}