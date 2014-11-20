<?php 
class Borderfree_Catalog_Model_Mysql4_Log extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('borderfreecatalog/log', 'id');
    }   
} 
?>