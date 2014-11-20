<?php 
class Borderfree_Crontab_Block_Adminhtml_Cronjobs_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_invalidatedTypes = array();
    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('borderfreecrontab_grid');
        $this->_filterVisibility = false;
        $this->_pagerVisibility  = false;
    }

    /**
     * Prepare grid collection
     */
    protected function _prepareCollection()
    {
        $collection = new Varien_Data_Collection();
        
        $job = new Varien_Object();
        $job->setCronjob("Full Catalog Export");
		$model = Mage::getModel('borderfreecrontab/log')->load("Full Catalog Export");
		$job->setLastrun($model->getLastRun());
		$collection->addItem($job);

        $job = new Varien_Object();
        $job->setCronjob("Incremental Catalog Export");
		$model = Mage::getModel('borderfreecrontab/log')->load("Incremental Catalog Export");
        $job->setLastrun($model->getLastRun());
		$collection->addItem($job);
        
        $job = new Varien_Object();
        $job->setCronjob("Get Catalog Error Logs");
		$model = Mage::getModel('borderfreecrontab/log')->load("Get Catalog Error Logs");
        $job->setLastrun($model->getLastRun());
		$collection->addItem($job);
        
        $job = new Varien_Object();
        $job->setCronjob("Update Site Cache");
        $model = Mage::getModel('borderfreecrontab/log')->load("Update Site Cache");
		$job->setLastrun($model->getLastRun());
        $collection->addItem($job);
        
        $job = new Varien_Object();
        $job->setCronjob("Import Orders");
		$model = Mage::getModel('borderfreecrontab/log')->load("Import Orders");
		$job->setLastrun($model->getLastRun());
        $collection->addItem($job);
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare grid columns
     */
    protected function _prepareColumns()
    {
        $baseUrl = $this->getUrl();
        $this->addColumn('cronjob', array(
            'header'    => $this->__('Job Name'),
            'width'     => '180',
            'align'     => 'left',
            'index'     => 'cronjob',
            'sortable'  => false,
        ));

        $this->addColumn('lastrun', array(
        		'header'    => $this->__('Last Run'),
        		'width'     => '180',
        		'align'     => 'left',
        		'index'     => 'lastrun',
        		'sortable'  => false,
        ));
        
        $this->addColumn('action',
            array(
                'header'    =>  $this->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getCronjob',
                'actions'   => array(
                    array(
                        'caption'   => $this->__('Run'),
                        'url'       => array('base'=> '*/*/run'),
                        'field'     => 'cronjob'
                    ),
                ),
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true,
        ));

        return parent::_prepareColumns();
    }


    /**
     * Get row edit url
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return false;
    }

}
