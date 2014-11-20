<?php

class Borderfree_Localization_Model_Discount extends Mage_SalesRule_Model_Quote_Discount
{


    /**
     * Aggregate item discount information to address data and related properties
     *
     * @param   Mage_Sales_Model_Quote_Item_Abstract $item
     * @return  Mage_SalesRule_Model_Quote_Discount
     */
    protected function _aggregateItemDiscount($item)
    {
    	if(!Mage::helper("borderfreesettings")->isBorderfreeEnabled())
    		return parent::_aggregateItemDiscount($item);
    	 
		$merchantId = Mage::getStoreConfig('borderfree_options/settings/merchantid');
		$collection = Mage::getModel("borderfreelocalization/currency")->getCollection()
			->addFieldToFilter("currency_code", Mage::app()->getStore()->getCurrentCurrencyCode())->addFieldToFilter("merchant_id", $merchantId);
		$currency = $collection->getFirstItem();
		$roundMethod = $currency->getRoundMethod();
		$lcp = Mage::helper("borderfreelocalization")->getLCPMultiplier();
		
		$discount = $item->getDiscountAmount();
		$discount = $discount / $lcp;
		$discount = round($discount, $roundMethod);
		
		$this->_addAmount(-$discount);
        $this->_addBaseAmount(-$item->getBaseDiscountAmount());
        return $this;
    }
}
