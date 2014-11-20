<?php 
class Borderfree_Catalog_Model_Mysql4_Stores extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('borderfreecatalog/stores', 'store_id');
        $this->_isPkAutoIncrement = false;
    }   
} 
?>