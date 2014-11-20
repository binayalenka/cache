<?php
/**
 * This code is part of the Borderfree Magento Extension.
*
* @category Borderfree
* @package Borderfree_Checkout
* @author Jamie Kail <jamie.kail@livearealabs.com>
* @copyright Copyright (c) 2013 Borderfree (http://www.borderfree.com)
*
*/
?>
<?php
/**
 * Overrides Mage_Sales_Model_Quote_Address_Total_Shipping so shipping is not recalculated after Domestic Leg Shipping is computed
 * 
 * @category Borderfree
 * @package Borderfree_Checkout
 * @author Jamie Kail <jamie.kail@livearealabs.com>
 * @see Mage_Sales_Model_Quote_Address_Total_Shipping
 */
class Borderfree_Checkout_Model_Shipping extends Mage_Sales_Model_Quote_Address_Total_Shipping
{
    /**
     * Collect totals information about shipping.  If shipping internationally do not recalculate shipping charges.
     * Shipping charges are set by Borderfree_Checkout_EnvoyController::checkoutAction
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_Sales_Model_Quote_Address_Total_Shipping
     * @see Mage_Sales_Model_Quote_Address_Total_Shipping::collect
     * @see Borderfree_Checkout_EnvoyController::checkoutAction
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
 		if(!$address->getBorderfree())       
    		parent::collect($address);
 		else
 		{
 			$this->_setAmount($address->getShippingAmount());
 			$this->_setBaseAmount($address->getBaseShippingAmount());
 		}

        return $this;
    }

}