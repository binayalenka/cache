<?php
class Borderfree_Settings_Model_Weightunit
{
	public function toOptionArray()
	{
		return array(
				array('value' => 'LB', 'label' => Mage::helper('borderfreecatalog')->__('LB')),
				array('value' => 'KG', 'label' => Mage::helper('borderfreecatalog')->__('KG'))
		);
	}

}