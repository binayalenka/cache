<?php
/**
* This code is part of the Borderfree Magento Extension.
*
* @category Borderfree
* @package Borderfree_Order
* @author Jamie Kail <jamie.kail@livearealabs.com>
* @copyright Copyright (c) 2013 Borderfree (http://www.borderfree.com)
*
*/
?>
<?php
/**
 * Overrides Mage_Sales_Model_Order_Shipment_Track to send tracking numbers to Borderfree.
 *
 * @category Borderfree
 * @package Borderfree_Order
 * @author Jamie Kail <jamie.kail@livearealabs.com>
 * @see Mage_Sales_Model_Order_Shipment_Track
 */
class Borderfree_Order_Model_Track extends Mage_Sales_Model_Order_Shipment_Track
{
	/**
	 * Sends tracking number and shipment contents to Fifty_One
	 *
	 * @todo Use error handling model
	 *
	 * @see Mage_Sales_Model_Order_Shipment_Track::_beforeSave()
	 */
    protected function _beforeSave()
    {
        parent::_beforeSave();

        $track = $this;

        $shipment = $track->getShipment();
        $order = $shipment->getOrder();
        $borderfreeOrderId = $order->getBorderfreeOrderId();
        if(!is_null($borderfreeOrderId) && $borderfreeOrderId != '')
        {

	        $ns = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';

	        $token = new stdClass;
	        $token->Username = new SOAPVar(Mage::getStoreConfig('borderfree_options/settings/apiuser'), XSD_STRING, null, null, null, $ns);
	        $token->Password = new SOAPVar(Mage::helper('core')->decrypt(Mage::getStoreConfig('borderfree_options/settings/apipassword')), XSD_STRING, null, null, null, $ns);

	        $wsec = new stdClass;
	        $wsec->UsernameToken = new SoapVar($token, SOAP_ENC_OBJECT, null, null, null, $ns);

	        $header = new SOAPHeader($ns, 'Security', $wsec, true);

	        $date = new DateTime();
	        $shippingDate = $date->format("Ymd");

	        $items = array();
			$orderItems = array();

	        foreach ($shipment->getAllItems() as $item)
	        {
	        	if ($item->getQty()>0)
	        	{
	        		if (!$item->getOrderItem()->isDummy(true))
	        		{
	        			$sku = $item->getSku();

	        			$orderItem = $item->getOrderItem();
	        			if($orderItem->getProductType() == "configurable")
	        			{
	        				$options = $orderItem->getProductOptions();
	        				$sku = $options['simple_sku'];
	        			}

	        			$i = new stdClass();
	        			$i->quantity = $item->getQty();
	        			$i->sku = $sku;
	        			$items[] = $i;
	        			if($orderItem->canInvoice()) {
	        				$orderItems[$orderItem->getItemId()] = $item->getQty();
	        			}
	        		}
	        	}
	        }


	        $parcelShipmentNotification = new stdClass();
	        $parcelShipmentNotification->parcel = new stdClass();
	        $parcelShipmentNotification->parcel->carrierName = Mage::getStoreConfig('borderfree_options/shipping/carrier');
	        $parcelShipmentNotification->parcel->carrierService = Mage::getStoreConfig('borderfree_options/shipping/service');
	        $parcelShipmentNotification->parcel->items = $items;
	        $parcelShipmentNotification->parcel->orderId = $order->getBorderfreeOrderId();
	        $parcelShipmentNotification->parcel->parcelId = $track->getTrackNumber();
	        $parcelShipmentNotification->parcel->shippingDate = $shippingDate;

	        $trackingURL = Mage::getStoreConfig('borderfree_options/shipping/url');
	        if(!empty($trackingURL))
	        {
		        $trackingURL = str_replace("##", $track->getTrackNumber(), $trackingURL);
		        $parcelShipmentNotification->parcel->trackingURL = $trackingURL;
	        }

	        $parcelShipmentNotification->requestPackingSlip = false;

	        $wsdl = Mage::getStoreConfig('borderfree_options/settings/staging') ? Mage::getStoreConfig('borderfree_options/order/staging') : Mage::getStoreConfig('borderfree_options/order/production');
	        $client = new SoapClient($wsdl);
	        $client->__setSoapHeaders($header);
	        try
	        {
	        	$response = $client->parcelShipmentNotification($parcelShipmentNotification);

	        }
	        catch(Exception $e)
	        {
	        	$message = "Shipment Tracking API Error - Order: " . $order->getIncrementId() . " - " . $e->getMessage();
	        	mail(Mage::getStoreConfig('borderfree_options/settings/erroremail'), "Borderfree Order Tracking API Error", $message);
	      	}

	      	if ($order->canInvoice())
	      	{
	      		try
	      		{
	      			foreach ($order->getAllItems() as $item)
	      			{
	      				if(!isset($orderItems[$item->getItemId()])) {
	      					$orderItems[$item->getItemId()] = 0;
	      				}
	      			}
				if($order->getPayment()->canCapturePartial())
				{
		      			$invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice($orderItems);
				}
				else
				{
					$invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
				}
	      			$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
	      			$invoice->register();
	      			$transactionSave = Mage::getModel('core/resource_transaction')->addObject($invoice)->addObject($invoice->getOrder());
	      			$transactionSave->save();
	      		}
	      		catch (Exception $e)
	      		{
	      			$order->addStatusHistoryComment($e->getMessage());
	      			$order->save();
	      			$orderNumber = $order->getIncrementId();
	      			Mage::log("Error Invoicing Order #" . $orderNumber . ": " . $e->getMessage(), null, "borderfree_order_invoice.log");
	      			$order->setState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, true)->save();
	      		}
	      	}
		}

        return $this;
    }
}

