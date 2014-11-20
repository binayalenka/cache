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
 * Overrides Magento Wishlist Helper for localization.
 * 
 * Overrides isAllow()
 *
 * @category Borderfree
 * @package Borderfree_Localization
 * @author Jamie Kail <jamie.kail@livearealabs.com>
 * @see Mage_Wishlist_Helper_Data::isAllow()
 */
class Borderfree_Localization_Helper_Wishlist extends Mage_Wishlist_Helper_Data
{
    /**
     * Check is allow wishlist module
     *
     * @return bool
     */
    public function isAllow()
    {
    	if(Mage::helper('borderfreesettings')->isBorderfreeEnabled())
    		return false;
    	
    	return parent::isAllow();
    }
}
