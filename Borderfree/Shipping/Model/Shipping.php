<?php


class Borderfree_Shipping_Model_Shipping extends Mage_Shipping_Model_Carrier_Abstract
{

    /**
     * unique identifier for our shipping module
     * @var string $_code
     */
    protected $_code = 'borderfreeshipping';

    /**
     * Collect rates for this shipping method based on information in $request
     *
     * @param Mage_Shipping_Model_Rate_Request $data
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigData('active'))
            return false;
		
		$shippingCountry = Mage::getSingleton('customer/session')->getShippingCountry();
        if($shippingCountry == "US")
        	return;
		
        $result = Mage::getModel('shipping/rate_result');		   
       	$method = Mage::getModel('shipping/rate_result_method');
		$method->setCarrier($this->_code);
        $method->setCarrierTitle("Borderfree");
        $method->setMethod("Borderfree");
        $method->setMethodTitle("International Shipping");
        $method->setCost(10);
        $method->setPrice(Mage::getStoreConfig('carriers/borderfreeshipping/rate'));	        
        $result->append($method);
        
        return $result;
    }

    public function getAllowedMethods()
    {
    	return array("Borderfree");
    }
		    
}
