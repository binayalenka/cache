<?php 
class Borderfree_Localization_Model_Mysql4_Payment extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('borderfreelocalization/payment', 'id');
    }   
} 
?>