<?php 
class Borderfree_Catalog_Model_Mysql4_Products extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('borderfreecatalog/products', 'product_id');
        $this->_isPkAutoIncrement = false;
    }   
} 
?>