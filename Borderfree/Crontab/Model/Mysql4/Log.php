<?php 
class Borderfree_Crontab_Model_Mysql4_Log extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('borderfreecrontab/log', 'type');
        $this->_isPkAutoIncrement = false;
    }   
} 
?>