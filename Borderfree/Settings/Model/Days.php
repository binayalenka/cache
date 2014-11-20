<?php
class Borderfree_Settings_Model_Days extends Mage_Core_Model_Config_Data
{
	public function save()
	{
		$number = $this->getValue(); 
		if(!is_numeric($number) || $number < 0 || $number > 12)
		{
			Mage::throwException("Domestic Leg Delivery Days must be a number between 0 & 12");
		}
		return parent::save();
	}
}
