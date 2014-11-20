<?php 
class Borderfree_Settings_Model_Handling extends Mage_Core_Model_Config_Data
{
	public function save()
	{
		$number = $this->getValue();
		
		if(!is_numeric($number))
		{
			Mage::throwException("Domestic Leg Extra Handling must be a number");
		}
		return parent::save();
	}
}
