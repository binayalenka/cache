<?php
class Borderfree_Localization_Model_Convert extends Mage_Directory_Model_Currency
{
	public function convert($price, $toCurrency=null)
	{
		if(!Mage::helper("borderfreesettings")->isBorderfreeEnabled())
			return parent::convert($price, $toCurrency);
		
		if (is_null($toCurrency))
			return $price;

		if ($rate = $this->getRate($toCurrency)) 
		{
			$merchantId = Mage::getStoreConfig('borderfree_options/settings/merchantid');
			$collection = Mage::getModel("borderfreelocalization/currency")->getCollection()
				->addFieldToFilter("currency_code", $toCurrency->getCurrencyCode())->addFieldToFilter("merchant_id", $merchantId);
			$currency = $collection->getFirstItem();
			$roundMethod = $currency->getRoundMethod();
			$lcp = Mage::helper("borderfreelocalization")->getLCPMultiplier();
			
			return round($price*$rate*$lcp, $roundMethod);
		}
	
		throw new Exception(Mage::helper('directory')->__('Undefined rate from "%s-%s".', $this->getCode(), $toCurrency->getCode()));
	}
	
}