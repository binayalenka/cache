<?php 
class Borderfree_Settings_Model_Insurance extends Mage_Core_Model_Config_Data
{
	public function save()
	{
		$number = $this->getValue();
		
		if(!is_numeric($number))
		{
			Mage::throwException("Domestic Leg Extra Insurance Price must be a number");
		}
		return parent::save();
	}
}
