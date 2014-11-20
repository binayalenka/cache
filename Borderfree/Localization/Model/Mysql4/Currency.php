<?php 
class Borderfree_Localization_Model_Mysql4_Currency extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('borderfreelocalization/currency', 'id');
    }   
} 
?>