<?php
class Borderfree_Settings_Model_Zip extends Mage_Core_Model_Config_Data
{
	public function save()
	{
		$number = $this->getValue();
		$number = preg_replace('#[^0-9]#','',$number);
		
		if(Mage::getStoreConfig('borderfree_options/shipping/flatrate'))
			return parent::save();
		
		if(strlen($number) == 5)
			return parent::save();
		if(strlen($number) == 9)
			return parent::save();

		Mage::throwException("Invlaid Domestic Leg Ship-to Zipcode");
	}
}
