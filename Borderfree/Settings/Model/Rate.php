<?php
class Borderfree_Settings_Model_Rate extends Mage_Core_Model_Config_Data
{
	public function save()
	{
		$number = $this->getValue();

		if(empty($number))
			return parent::save();
		
		if(!is_numeric($number))
		{
			Mage::throwException("Domestic Leg Flat Shipping Rate must be a number");
		}
		return parent::save();
	}
}
