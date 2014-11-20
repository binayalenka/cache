<?php 
class Borderfree_Marketing_Model_Mysql4_Record extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('borderfreemarketing/record', 'email');
		$this->_isPkAutoIncrement = false;
    }   
} 
?>