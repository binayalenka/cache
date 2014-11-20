<?php

//check if Magento EE 1.13 and above
if(class_exists('Enterprise_CatalogInventory_Model_Index_Observer')){

class Borderfree_Order_Model_Observer extends Enterprise_CatalogInventory_Model_Index_Observer
{

  public function subtractQuoteInventory(Varien_Event_Observer $observer, $blind = false)
        {
                $quote = $observer->getEvent()->getQuote();
                $borderfreeOrderId = $quote->getBorderfreeOrderId();
                if(!is_null($borderfreeOrderId) && $borderfreeOrderId != '' && !$blind) {
                        return;
                } else {
                        // Maybe we've already processed this quote in some event during order placement
                        // e.g. call in event 'sales_model_service_quote_submit_before' and later in 'checkout_submit_all_after'
                        if ($quote->getInventoryProcessed()) {
                                return;
                        }
                        $items = $this->_getProductsQty($quote->getAllItems());

                        /**
                         * Remember items
                        */
                        $this->_itemsForReindex = Mage::getSingleton('cataloginventory/stock')->registerProductsSale($items);

                        $quote->setInventoryProcessed(true);
                        return $this;
                }
        }
}


}else{


class Borderfree_Order_Model_Observer extends Mage_CatalogInventory_Model_Observer 
{

	public function subtractQuoteInventory(Varien_Event_Observer $observer, $blind = false)
	{
		$quote = $observer->getEvent()->getQuote();
		$borderfreeOrderId = $quote->getBorderfreeOrderId();
		if(!is_null($borderfreeOrderId) && $borderfreeOrderId != '' && !$blind) {
			return;
		} else {
			// Maybe we've already processed this quote in some event during order placement
			// e.g. call in event 'sales_model_service_quote_submit_before' and later in 'checkout_submit_all_after'
			if ($quote->getInventoryProcessed()) {
				return;
			}
			$items = $this->_getProductsQty($quote->getAllItems());
			
			/**
			 * Remember items
			*/
			$this->_itemsForReindex = Mage::getSingleton('cataloginventory/stock')->registerProductsSale($items);
			
			$quote->setInventoryProcessed(true);
			return $this;
		}
	}
}
}
