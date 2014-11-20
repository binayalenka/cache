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
 * FiftyOnne Language List Block
 * 
 * @category Borderfree
 * @package Borderfree_Localization
 * @author Jamie Kail <jamie.kail@livearealabs.com>
 * @see Mage_Page_Block_Switch
 */
class Borderfree_Localization_Block_Switch extends Mage_Page_Block_Switch
{
	/**
	 * Get the international or domestic store views 
	 * 
	 * @return array
	 */
    public function getRawStores()
    {
        if (!$this->hasData('raw_stores')) {
            $websiteStores = Mage::app()->getWebsite()->getStores();
            $stores = array();
            foreach ($websiteStores as $store) {
            	if(Mage::helper('borderfreesettings')->isBorderfreeEnabled() && $store->getCode() == Mage::app()->getDefaultStoreView()->getCode())
					continue;
            	elseif(!Mage::helper('borderfreesettings')->isBorderfreeEnabled() && strlen($store->getCode()) == 2)
            		continue;
            	            		
                /* @var $store Mage_Core_Model_Store */
                if (!$store->getIsActive()) {
                    continue;
                }
            	if($store->getCode() == Mage::app()->getDefaultStoreView()->getCode())
            		$store->setName("English");
                
            	$store->setLocaleCode(Mage::getStoreConfig('general/locale/code', $store->getId()));

                $params = array(
                    '_query' => array()
                );
                if (!$this->isStoreInUrl()) {
                    $params['_query']['___store'] = $store->getCode();
                }
                $baseUrl = $store->getUrl('', $params);

                $store->setHomeUrl($baseUrl);
                $stores[$store->getGroupId()][$store->getId()] = $store;
            }
            $this->setData('raw_stores', $stores);
        }
        return $this->getData('raw_stores');
    }
}
