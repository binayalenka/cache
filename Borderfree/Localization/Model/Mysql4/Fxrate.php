<?php 
class Borderfree_Localization_Model_Mysql4_Fxrate extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('borderfreelocalization/fxrate', 'buyer_currency');
        $this->_isPkAutoIncrement = false;
    }   
} 
?>