<?php
class Borderfree_Crontab_Adminhtml_CronjobsController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
	{
		$this->_initAction()
		->renderLayout();
	}
		
	protected function _initAction()
	{
		$this->loadLayout()
		// Make the active menu match the menu config nodes (without 'children' inbetween)
		->_setActiveMenu('system/borderfreecrontab')
		->_title($this->__('System'))->_title($this->__('Borderfree Cron Jobs'))
		->_addBreadcrumb($this->__('System'), $this->__('System'))
		->_addBreadcrumb($this->__('Borderfree Cron Jobs'), $this->__('Borderfree Cron Jobs'));
		 
		return $this;
	}
	
	/**
	 * Check currently called action by permissions for current user
	 *
	 * @return bool
	 */
	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')->isAllowed('system/borderfreecrontab');
	}
	
	public function runAction()
	{
		$job = $this->getRequest()->getParam('cronjob');
		switch($job)
		{
			case "Full Catalog Export":
				Mage::getModel("borderfreecatalog/export")->fullExport();
				break;
				
			case "Incremental Catalog Export":
				Mage::getModel("borderfreecatalog/export")->incrementalExport();
				break;
			
			case "Get Catalog Error Logs":
				Mage::getModel("borderfreecatalog/export")->getErrorLogs();
				break;
			
			case "Update Site Cache":
				Mage::getModel("borderfreelocalization/import")->import();
				break;
				
			case "Import Orders":
				Mage::getModel("borderfreeorder/import")->import();
				break;
		}
		$this->_redirect("*/*/index");
	}
}
