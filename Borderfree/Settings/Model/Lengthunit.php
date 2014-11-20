<?php
class Borderfree_Settings_Model_Lengthunit
{
	public function toOptionArray()
	{
		return array(
				array('value' => 'IN', 'label' => Mage::helper('borderfreecatalog')->__('IN')),
				array('value' => 'CM', 'label' => Mage::helper('borderfreecatalog')->__('CM'))
		);
	}

}