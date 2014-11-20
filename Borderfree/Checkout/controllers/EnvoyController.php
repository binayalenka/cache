<?php
class Borderfree_Checkout_EnvoyController extends Mage_Core_Controller_Front_Action
{
	public function indexAction()
	{
		$shippingCountry = Mage::getSingleton('customer/session')->getShippingCountry();
		if($shippingCountry == "US")
		{
			$this->_redirect("checkout/onepage/index");
			return;
		}

		$quote = Mage::getModel('checkout/cart')->getQuote();

		if (!$quote->hasItems() || $quote->getHasError())
		{
			$this->_redirect('checkout/cart');
			return;
		}

		if (!$quote->validateMinimumAmount())
		{
			$error = Mage::getStoreConfig('sales/minimum_order/error_message') ?
			Mage::getStoreConfig('sales/minimum_order/error_message') :
			Mage::helper('checkout')->__('Subtotal must exceed minimum order amount');

			Mage::getSingleton('checkout/session')->addError($error);
			$this->_redirect('checkout/cart');
			return;
		}

		$this->_redirect("borderfreecheckout/envoy/checkout");
	}

	public function checkoutAction()
	{
		$cart = Mage::getModel('checkout/cart')->getQuote();
        $totalSalePrice = 0;
		$totalProductExtraShipping = 0;
		$totalProductExtraHandling = 0;

		$request = Mage::getModel("borderfreeapi/webservice");
		$request->startRequest("setCheckoutSessionRequest");

    	$request->startElement("domesticSession", array("merchantId" => Mage::getStoreConfig('borderfree_options/settings/merchantid')));
    		$request->startElement("domesticBasket");
    			$request->startElement("basketItems");
    			$orderDiscount = 0;

    			$state = Mage::getStoreConfig('borderfree_options/shipping/state');
    			$zip = Mage::getStoreConfig('borderfree_options/shipping/zip');
    			$method = Mage::getStoreConfig('borderfree_options/shipping/method');
				$shippingAddress = $cart->getShippingAddress();
				$regionModel = Mage::getModel('directory/region')->loadByCode($state, "US");
				$regionId = $regionModel->getId();
				$shippingAddress->setRegionCode($state)->setRegionId($regionId);
				$shippingAddress->setPostcode($zip)->setCountryId("US");
				$shippingAddress->setCollectShippingRates(true);
				$shippingAddress->collectShippingRates();
				$shippingAddress->setShippingMethod($method);
				$shippingAddress->collectTotals();
				$shippingAddress->save();
				$cart->collectTotals();
				$cart->save();

    			$addresses = $cart->getAllShippingAddresses();
    			if(isset($addresses[0]))
    				$orderDiscount = $addresses[0]->getBaseDiscountAmount() * -1;
    			else
    				return $this->_redirect("checkout/cart");

    			$loggedIn = Mage::helper('customer')->isLoggedIn();
    			if($loggedIn)
    			{
    				$customer = Mage::getSingleton("customer/session")->getCustomer();
    				$cart->setCustomer($customer);
    				$cart->save();
    			}


    			foreach ($cart->getAllVisibleItems() as $item)
    			{

    				$product = Mage::getModel("catalog/product")->load($item->getProduct()->getId());
    				$extraShipping = $product->getExtraShiping();
    				if(empty($extraShipping))
    					$extraShipping = 0;

    				$basePrice = $item->getBaseCalculationPrice();
    				$qty = $item->getQty();

    				$customOptions = "";
    				$extraAttributes = "";
    				$color = "";
    				$color_label = "";
    				$size = "";
    				$size_label = "";

    				if($item->getProductType() == "configurable" || $item->getProductType() == "simple" || $item->getProductType() == "bundle")
    				{
    					$productOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
    					if(isset($productOptions["options"]))
    					{
	    					$customOptions = serialize($productOptions["options"]);
	    					foreach($productOptions["options"] as $option)
	    					{
	    						if($extraAttributes != "")
	    							$extraAttributes .= ", ";

	    						$extraAttributes .= $option["label"] . ": " . $option["print_value"];
	    					}
    					}
    				}

    				if($item->getProductType() == "configurable")
    				{
	    				$attributes = $item->getProduct()->getTypeInstance(true)
	    					->getSelectedAttributesInfo($item->getProduct());

	    				$attributesArray = $item->getProduct()->getTypeInstance(true)
	    					->getConfigurableAttributesAsArray($item->getProduct());

	    				foreach($attributesArray as $attribute)
	    				{
	    					if($attribute["attribute_code"] == "color")
	    						$color_label = $attribute["store_label"];
	    					elseif($attribute["attribute_code"] == "size")
	    						$size_label = $attribute["store_label"];
	    				}

	    				foreach($attributes as $attribute)
	    				{
	    					if($attribute["label"] == $color_label)
	    						$color = $attribute["value"];
	    					elseif($attribute["label"] == $size_label)
	    						$size = $attribute["value"];
	    					else
	    					{	if($extraAttributes != "")
	    							$extraAttributes .= ", ";

	    						$extraAttributes .= $attribute["label"] . ": " . $attribute["value"];
	    					}
	    				}

	    				$item = $item->getOptionByCode('simple_product');
    					$product = Mage::getModel("catalog/product")->load($item->getProduct()->getId());

    					$es = $product->getExtraShipping();
    					if(!empty($es))
    						$extraShipping = $es;
	    			}

    				$request->startElement("basketItem", array("sku" => $item->getProduct()->getSku()));
    					$request->writeElement("quantity", $qty);
    					$request->startElement("pricing");

	    					$listPrice = $item->getProduct()->getPrice();
	    					$extraHandling = 0;
	    					$discount = $item->getProduct()->getPrice() - $basePrice;
	    					if($discount < 0)
	    					{
	    						$extraHandling = $discount * -1;
	    						$discount = 0;
	    					}
	    					$salePrice = $listPrice - $discount;

	    					$totalSalePrice += ($salePrice * $qty);
	    					$totalProductExtraShipping += $extraShipping;
	    					$totalProductExtraHandling += $extraHandling;

    						$request->writeElement("listPrice", round($listPrice, 2));
    						$request->writeElement("itemDiscount", round($discount ,2));
    						$request->writeElement("salePrice", round($salePrice, 2));
    						$request->writeElement("productExtraShipping", round($extraShipping, 2));
    						$request->writeElement("productExtraHandling", round($extraHandling, 2));
    					$request->endElement();
    					$request->startElement("display");
    						$request->writeElement("name", $product->getName(), true);
    						$request->writeElement("description", $product->getShortDescription(), true);
    						$request->writeElement("productUrl", $product->getProductUrl());
    						$request->writeElement("imageUrl", (string)Mage::helper('catalog/image')->init($product, 'thumbnail')->resize(75));

    						if(!empty($color))
    							$request->writeElement("color", $color);
    						if(!empty($size))
    							$request->writeElement("size", $size);
    						if(!empty($extraAttributes))
    							$request->writeElement("attributes", $extraAttributes);
    						$request->writeElement("inventory", $product->isInStock() ? "In Stock" : "Out of Stock");
    					$request->endElement(); //display
						$request->writeElement("customData", $customOptions);
    				$request->endElement(); //basketItem
    			}
    			$request->endElement();//basketItems
    			$request->startElement("basketTotal");
    				$request->writeElement("totalSalePrice" , round($totalSalePrice, 2));
    				$request->writeElement("orderDiscount", round($orderDiscount, 2));
    				$request->writeElement("totalProductExtraShipping", round($totalProductExtraShipping, 2));
    				$request->writeElement("totalProductExtraHandling", round($totalProductExtraHandling, 2));
    				$request->writeElement("totalPrice", round($totalSalePrice - $orderDiscount + $totalProductExtraShipping + $totalProductExtraHandling, 2));
    			$request->endElement();//basketTotal
				$request->WriteElement("customData");
    		$request->endElement();//domesticBasket
			$request->startElement("domesticShippingMethod");

				$domesticShippingPrice = $cart->getShippingAddress()->getBaseShippingAmount();
				if(empty($domesticShippingPrice))
					$domesticShippingPrice = 0;

				$domesticHandlingPrice = Mage::getStoreConfig('borderfree_options/shipping/handling');
				if(empty($domesticHandlingPrice))
					$domesticHandlingPrice = 0;

				$extraInsurancePrice = Mage::getStoreConfig('borderfree_options/shipping/insurance');
				if(empty($extraInsurancePrice))
					$extraInsurancePrice = 0;

				$deliveryPromiseMinimum = Mage::getStoreConfig('borderfree_options/shipping/min');
				if(empty($deliveryPromiseMinimum))
					$deliveryPromiseMinimum = 0;

				$deliveryPromiseMaximum = Mage::getStoreConfig('borderfree_options/shipping/max');
				if(empty($deliveryPromiseMaximum))
					$deliveryPromiseMaximum = 0;

				$request->writeElement("domesticShippingPrice", round($domesticShippingPrice, 2));
				$request->writeElement("domesticHandlingPrice", round($domesticHandlingPrice, 2));
				$request->writeElement("extraInsurancePrice", round($extraInsurancePrice, 2));
				$request->writeElement("deliveryPromiseMinimum", round($deliveryPromiseMinimum, 2));
				$request->writeElement("deliveryPromiseMaximum", round($deliveryPromiseMaximum, 2));

				$totalShipping = $domesticShippingPrice + $domesticHandlingPrice + $extraInsurancePrice + $totalProductExtraShipping + $totalProductExtraHandling;
				$amountPrice = $cart->getStore()->convertPrice($totalShipping, false);
				$shippingAddress->setShippingAmount($amountPrice);
				$shippingAddress->setBaseShippingAmount($totalShipping);
				$shippingAddress->setBorderfree(true);
				$cart->setTotalsCollectedFlag(false);
				$cart->collectTotals()->save();
				$shippingAddress->save();

			$request->endElement();//domesticShippingMethod
    		$request->startElement("sessionDetails");
				$request->writeElement("buyerSessionId", Mage::getSingleton("core/session")->getEncryptedSessionId());
				$request->writeElement("buyerIpAddress", $_SERVER['REMOTE_ADDR']);
				$request->startElement("checkoutUrls");
					$request->writeElement("successUrl", Mage::getUrl("*/*/success" . "?QID=" . $cart->getId(), array('_secure'=>true)));
					$request->writeElement("pendingUrl", Mage::getUrl("*/*/pending") . "?QID=" . $cart->getId(), array('_secure'=>true));
					$request->writeElement("failureUrl", Mage::getUrl("*/*/failure"), array('_secure'=>true));
					$request->writeElement("callbackUrl", Mage::getUrl("*/*/callback") . "?QID=" . $cart->getId() . "&SID=" . Mage::getSingleton("core/session")->getEncryptedSessionId(), array('_secure'=>true));
					$request->writeElement("basketUrl", Mage::getUrl("checkout/cart"));
					$request->writeElement("contextChooserPageUrl", Mage::getUrl("borderfreelocalization/context"));
					$request->writeElement("usCartStartPageUrl", Mage::getUrl("*/*/uscart"));
					$request->startElement("paymentUrls");
						if(Mage::getStoreConfig('borderfree_options/checkout/paypal'))
						{
							$request->startElement("payPalUrls");
								$request->writeElement("returnUrl", Mage::getUrl("*/*/paypal"));
								$request->writeElement("cancelUrl", Mage::getUrl("checkout/cart"));
								$request->writeElement("headerLogoUrl", Mage::getStoreConfig('borderfree_options/checkout/logo'));
							$request->endElement();
						}
					$request->endElement();//paymentUrls
				$request->endElement();//checkoutUrls
			$request->endElement();//sessionDetails
			$request->startElement("orderProperties");
				$request->writeElement("currencyQuoteId", Mage::getModel("borderfreelocalization/fxrate")->getCollection()->addFieldToFilter("buyer_currency", Mage::app()->getStore()->getCurrentCurrencyCode())->getFirstItem()->getQuoteId());

				$lcp = Mage::helper("borderfreelocalization")->getLCPRule();
				if($lcp)
					$request->writeElement("lcpRuleId", $lcp);

				$cart->reserveOrderId();
				//$request->writeElement("merchantOrderId", $cart->getReservedOrderId());
				$request->writeElement("merchantOrderRef", $cart->getId());
			$request->endElement();//orderProperties
			$request->endElement();//domesticSession
			$request->startElement("buyerSession");
				$billingAddress = NULL;
				$shippingAddress = NULL;

				$customer = Mage::getSingleton('customer/session')->getCustomer();
				$shippingAddressId = $customer->getDefaultShipping();
				if ($shippingAddressId)
					$shippingAddress = Mage::getModel('customer/address')->load($shippingAddressId);
				if($shippingAddressId && $shippingAddress->getCountryId() == Mage::getSingleton('customer/session')->getShippingCountry())
				{
					$street = $shippingAddress->getStreet();
					$countryCode = Mage::getSingleton('customer/session')->getShippingCountry();

					$request->startElement("shipToAddress");
						$request->writeElement("firstName", $shippingAddress->getFirstname());
						$request->writeElement("lastName", $shippingAddress->getLastname());
						if(isset($street[0]))
							$request->writeElement("addressLine1", $street[0]);
						if(isset($street[1]))
							$request->writeElement("addressLine2", $street[1]);
						if(isset($street[2]))
							$request->writeElement("addressLine3", $street[2]);
						$request->writeElement("city", $shippingAddress->getCity());
						//if($countryCode == "US" || $countryCode == "CA")
						//{
							$request->writeElement("region", $shippingAddress->getRegionCode());
							$request->writeElement("postalCode", $shippingAddress->getPostcode());
						//}
						$request->writeElement("countryCode", $countryCode);
						$request->writeElement("email", $customer->getEmail());
						$request->writeElement("primaryPhone", $shippingAddress->getTelephone());
					$request->endElement();//shipToAddress
				}
				else
				{
					$request->startElement("shipToAddress");
						$request->writeElement("countryCode", Mage::getSingleton('customer/session')->getShippingCountry());
					$request->endElement();//shipToAddress
				}

				$billingAddressId = Mage::getSingleton('customer/session')->getCustomer()->getDefaultBilling();
				if ($billingAddressId)
				{
					$billingAddress = Mage::getModel('customer/address')->load($billingAddressId);
					$street = $billingAddress->getStreet();

					$request->startElement("billToAddress");
						$request->writeElement("firstName", $billingAddress->getFirstname());
						$request->writeElement("lastName", $billingAddress->getLastname());
						if(isset($street[0]))
							$request->writeElement("addressLine1", $street[0]);
						if(isset($street[1]))
							$request->writeElement("addressLine2", $street[1]);
						if(isset($street[2]))
							$request->writeElement("addressLine3", $street[2]);
						$request->writeElement("city", $billingAddress->getCity());
						//if($countryCode == "US" || $countryCode == "CA")
						//{
							$request->writeElement("region", $billingAddress->getRegionCode());
							$request->writeElement("postalCode", $billingAddress->getPostcode());
						//}
						$request->writeElement("countryCode", Mage::getSingleton('customer/session')->getIpCountry());
						$request->writeElement("email", $customer->getEmail());
						$request->writeElement("primaryPhone", $billingAddress->getTelephone());
					$request->endElement();//billToAddress
				}
				else
				{
					$request->startElement("billToAddress");
						$request->writeElement("countryCode", Mage::getSingleton('customer/session')->getIpCountry());
					$request->endElement();//billToAddress
				}

				$request->startElement("buyerPreferences");
					if(strlen(Mage::app()->getStore()->getCode()) == 2)
						$request->writeElement("language", strtoupper(Mage::app()->getStore()->getCode()));
					else
						$request->writeElement("language", "EN");
					$request->writeElement("buyerCurrency", Mage::app()->getStore()->getCurrentCurrencyCode());
					$request->writeElement("couponCode", $cart->getCouponCode());
					$request->endElement();//buyerPreferences
			$request->endElement();//buyerSession

    	$endpoint = Mage::getStoreConfig('borderfree_options/settings/staging') ? Mage::getStoreConfig('borderfree_options/checkout/staging') : Mage::getStoreConfig('borderfree_options/checkout/production');

    	try
    	{
    		$result = $request->submitRequest($endpoint);
			$xml = new SimpleXMLElement($result);
    	}
    	catch(Exception $e)
    	{
    		mail(Mage::getStoreConfig('borderfree_options/settings/erroremail'), "Borderfree Checkout BTP Error", $e->getMessage());
    		Mage::getSingleton("checkout/session")->addError(Mage::helper('checkout')->__("An error occured trying to process your order.  Please try again later."));
    		return $this->_redirect("checkout/cart");
    	}

		$warnings = $xml->payload->warnings;
		$errors = $xml->payload->errorResponse;

		if($warnings || $errors)
		{
			$message = "";
			$subject = "Borderfree Checkout BTP Warning";

			if($warnings)
				$message .= $warnings->asXml() . "\n\n";
			if($errors)
			{
				$message .= $errors->asXml() . "\n\n";
				$subject = "Borderfree Checkout BTP Error";
			}

			mail(Mage::getStoreConfig('borderfree_options/settings/erroremail'), $subject, $message);
		}

		if($errors)
		{
			Mage::getSingleton("checkout/session")->addError(Mage::helper('checkout')->__("An error occured trying to process your order.  Please try again later."));
			return $this->_redirect("checkout/cart");
		}

		/*$document =  $request->outputMemory();
    	echo "<pre>";
    	echo htmlentities($document);
		echo htmlentities($result);
    	echo "</pre>";*/

		Mage::getSingleton("checkout/session")->setEnvoyUrl((string)$xml->payload->setCheckoutSessionResponse->envoyInitialParams->fullEnvoyUrl);
		$this->loadLayout();
		$this->renderLayout();
	}

	public function successAction()
	{
		Mage::getSingleton('checkout/session')->clear();
		$quote = Mage::getModel("sales/quote")->load($_GET["QID"]);
		$quote->setIsActive(0)->save();
	}

	public function pendingAction()
	{
		Mage::getSingleton('checkout/session')->clear();
		$quote = Mage::getModel("sales/quote")->load($_GET["QID"]);
		$quote->setIsActive(0)->save();
	}

	public function callbackAction()
	{
		Mage::getSingleton("core/session")->setSessionId($_GET["SID"]);
		Mage::getSingleton('checkout/session')->clear();

		$quote = Mage::getModel("sales/quote")->load($_GET["QID"]);
		$quote->setIsActive(0)->save();

		$observer = new Varien_Event_Observer();
		$event = new Varien_Event(array('quote'=>$quote));
		$observer->setEvent($event);
		$inv = Mage::getModel("cataloginventory/observer");
		$inv->subtractQuoteInventory($observer);
		$inv->reindexQuoteInventory($observer);
	}

	public function failureAction()
	{
	}

	public function uscartAction()
	{
		Mage::app()->getStore()->setCurrentCurrencyCode("USD");
		Mage::getModel("borderfreelocalization/country")->switchCountry("US");
		return $this->_redirect("checkout/cart");
	}

	public function paypalAction()
	{
		$domain1 = "checkout";
		if(Mage::getStoreConfig('borderfree_options/settings/staging'))
			$domain1 = "stagecheckout";

		$envoyUrl = "$domain1.borderfree.com/htmlcheckout/views/preloadBack_pp.xhtml";

		Mage::getSingleton("checkout/session")->setEnvoyUrl($envoyUrl);
		$this->loadLayout();
		$this->renderLayout();
	}
}
