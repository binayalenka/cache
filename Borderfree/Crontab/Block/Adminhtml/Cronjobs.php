<?php
class Borderfree_Crontab_Block_Adminhtml_Cronjobs extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        // The blockGroup must match the first half of how we call the block, and controller matches the second half
        // ie. foo_bar/adminhtml_baz
        $this->_blockGroup = 'borderfreecrontab';
        $this->_controller = 'adminhtml_cronjobs';
        $this->_headerText = $this->__('Borderfree Cron Jobs');
                
        parent::__construct();
        $this->_removeButton('add');
    }
}