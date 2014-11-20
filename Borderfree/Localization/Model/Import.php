<?php
class Borderfree_Localization_Model_Import extends Mage_Core_Model_Abstract
{
	private $merchantIds = array();
	
	public function import()
	{
		$model = Mage::getModel('borderfreecrontab/log');
		$model->setType("Update Site Cache");
		$model->setLastRun(time());
		$model->save();
		
		$this->getIPGeo();
		$this->getFxRates();

		$allStores = Mage::app()->getStores();
		foreach ($allStores as $_eachStoreId => $val)
		{
			$store = Mage::app()->getStore($_eachStoreId)->getId();
			Mage::app()->setCurrentStore($store);
			if(Mage::getStoreConfig('borderfree_options/settings/enabled'))
			{
				$merchantId = Mage::getStoreConfig('borderfree_options/settings/merchantid');
				if(!in_array($merchantId, $this->merchantIds))
				{
					$this->merchantIds[] = Mage::getStoreConfig('borderfree_options/settings/merchantid');
					$this->getCountries($merchantId);
					$this->getCurrencies($merchantId);
					$this->getPricingCustomizations($merchantId);
				}
			}
		}
	}
	
	/**
	 * Cache pricing customizations
	 * 
	 * @param string $merchantId
	 */
	public function getPricingCustomizations($merchantId)
	{
		list($xml, $version, $newVersion) = $this->callApi("PRICING_CUSTOMIZATIONS", $merchantId, true);
		if(!$newVersion)
			return;
		
		$pricingCustomizations = $xml->xpath("//pricingCustomization");
		
		$this->resetTable("borderfree_lcp_rules", $merchantId);
		$this->resetTable("borderfree_rounding_rules", $merchantId);
		
		foreach($pricingCustomizations as $customization)
		{
			$countries = $customization->xpath("./conditions/condition/shipToCountry/country");

			$attributes = $customization->attributes();
			$customizationId = $attributes["id"];
			
			$component = $customization->component;
			$attributes = $component->attributes();
			$type = $attributes["type"];
			
			if($type != "PRODUCT_PRICES")
				continue;
			
			$override = $component->override;
			$attributes = $override->attributes();
			$type = $attributes["type"];
		
			if($type == "FRONT_LOAD_PERCENT")
			{
				foreach($countries as $country)
				{
					$attributes = $country->attributes();
					$countryCode = $attributes["code"];
							
					$model = Mage::getModel("borderfreelocalization/lcp");
					$model->setRuleId($customizationId);
					$model->setCountryCode($countryCode);
					$model->setMerchantId($merchantId);
					$model->setName($customization->name);
					$model->setDescription($customization->description);
					$model->setMultiplier($override->lcp->multiplier);
					$model->save();
				}
			}
			else if($type == "ROUND_UP")
			{
				foreach($countries as $country)
				{
					$attributes = $country->attributes();
					$countryCode = $attributes["code"];

					$model = Mage::getModel("borderfreelocalization/rounding");
					$model->setRuleId($customizationId);
					$model->setCountryCode($countryCode);
					$model->setMerchantId($merchantId);
					$model->setName($customization->name);
					$model->setDescription($customization->description);
					$model->setAmount($override->amount);
					$model->save();
				}
			}
		}		
	
		$version->save();
		
	}
	
	public function getCurrencies($merchantId)
	{
		list($xml, $version, $newVersion) = $this->callApi("CURRENCIES", $merchantId, true);
		if(!$newVersion)
			return;
		
		$currencies = $xml->xpath("//currency");
		$allowedCurrencies = array();
		$data = array();

		if(count($currencies) != 0)
			$this->resetTable("borderfree_currency", $merchantId);
		
		foreach($currencies as $currency)
		{
			$attributes = $currency->attributes();
			$currencyCode = $attributes["code"];

			if($currency->isCurrencyEnabled)
				$allowedCurrencies[] = $currencyCode;
					
			$model = Mage::getModel("borderfreelocalization/currency");
			$model->setCurrencyCode($currencyCode);
			$model->setMerchantId($merchantId);
			$model->setName($currency->name);
			$model->setRoundMethod($currency->roundMethod);
			$model->setSymbol($currency->symbol);
			$model->setEnabled($currency->isCurrencyEnabled ==  "true" ? true : false);
			$model->save();

			$methods = $currency->xpath("./paymentMethodSupport/paymentMethod");

			if(count($methods) != 0)
			{
				$sql = "DELETE FROM borderfree_payment WHERE merchant_id = '$merchantId' AND currency_code = '$currencyCode'";		
				$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
				$connection->query($sql);
			}
			
			foreach($methods as $method)
			{				
				$model = Mage::getModel("borderfreelocalization/payment");
				$model->setCurrencyCode($currencyCode);
				$model->setMerchantId($merchantId);
				$model->setMethod($method);
				$model->save();
			}
		}
		
		if(count($allowedCurrencies))
		{
			$currencyList = implode(",", $allowedCurrencies);
			$config = new Mage_Core_Model_Config();
			$config->saveConfig("currency/options/allow", $currencyList, 'websites', Mage::app()->getStore()->getWebsiteId());
		}	

		$version->save();
	}

	public function getCountries($merchantId)
	{
		list($xml, $version, $newVersion) = $this->callApi("COUNTRIES", $merchantId, true);
		if(!$newVersion)
			return;		

		$countries = $xml->xpath("//country");

		if(count($countries) != 0)
			$this->resetTable("borderfree_countries", $merchantId);
		
		foreach($countries as $country)
		{
			$attributes = $country->attributes();
			$countryCode = $attributes["code"];
			
			$model = Mage::getModel("borderfreelocalization/country");
			$model->setCountryCode($countryCode);
			$model->setMerchantId($merchantId);
			$model->setName($country->name);
			$model->setShipTo($country->isShipToEnabled ==  "true" ? true : false);
			$model->setBillTo($country->isBillToEnabled ==  "true" ? true : false);
			$model->setPostalCode($country->isPostalCodeRequired ==  "true" ? true : false);
			$model->setCurrencyCode($country->currencyCode);
			$model->setLanguageCode($country->languageCode);
			$model->setLocale($country->locale);
			$model->setTranslationAvailable($country->isTranslationAvailable ==  "true" ? true : false);
			$model->setPricingModel($country->pricingModel);
			$model->save();
		
			if($country->pricingModel == "REGIONAL")
			{
				$regions = $country->xpath("./regions/region");
	
				if(count($regions) != 0)
				{
					$sql = "DELETE FROM borderfree_regions WHERE merchant_id = '$merchantId' AND country_code = '$countryCode'";		
					$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
					$connection->query($sql);
				}
				
				foreach($regions as $region)
				{
					$attributes = $region->attributes();
					$regionCode = $attributes["code"];
					
					$model = Mage::getModel("borderfreelocalization/region");
					$model->setCountryCode($countryCode);
					$model->setMerchantId($merchantId);
					$model->setName($region);
					$model->setRegionCode($regionCode);
					$model->save();
				}
			}
		}
			
		$version->save();
	}
	
	private function resetTable($name, $merchantId = NULL)
	{
		$sql = "DELETE FROM $name";
		
		if($merchantId != NULL)
			$sql .= " WHERE merchant_id = '$merchantId'";
		
		$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
		$connection->query($sql);
	}
	
	public function getFxRates()
	{
		$data = array("USD" => array());
		
		list($xml, $version, $newVersion) = $this->callApi("FX_RATES", 0, true);
		if(!$newVersion)
			return;
		
		$quotes = $xml->xpath("//quote");
		foreach($quotes as $quote)
		{
			$quoteId = 0;
			foreach($quote->attributes() as $name => $value)
			{
				if($name == "id")
					$quoteId = $value;
			}
			$model = Mage::getModel("borderfreelocalization/fxrate");
			$model->setBuyerCurrency($quote->buyerCurrency);
			$model->setMerchantCurrency($quote->merchantCurrency);
			$model->setFxRate($quote->fxRate);
			$model->setQuoteId($quoteId);
			$model->save();
			
			$value = abs(Mage::getSingleton('core/locale')->getNumber($quote->fxRate));
			$data[(string)$quote->merchantCurrency][(string)$quote->buyerCurrency] = $value;
		}

		Mage::getModel('directory/currency')->saveRates($data);
		
		$version->save();
	}
	
	public function getIPGeo()
	{
		list($xml, $version, $newVersion) = $this->callApi("IP_GEO", 0, true);
		if(!$newVersion)
			return;
		
		$countries = $xml->xpath("//country");
		
		if(count($countries) != 0)
			$this->resetTable("borderfree_localization_ip");
		
		foreach($countries as $country)
		{
			$attributes = $country->attributes();
			$countryCode = $attributes["code"];
			
			$ranges = explode(",", $country);
			foreach($ranges as $range)
			{ 
				$range = explode(":", $range);
				$model = Mage::getModel("borderfreelocalization/ip");
				$model->setCountryCode($countryCode);
				$model->setLow($range[0]);
				$model->setHigh($range[1]);
				$model->save();
			}
		}
				
		$version->save();
	}
	
	private function callApi($data_type, $merchantId, $quiet = false)
	{
		$collection = Mage::getModel("borderfreelocalization/cache")->getCollection()->addFieldToFilter("data_type", $data_type)->addFieldToFilter("merchant_id", $merchantId);
		if(count($collection) == 0)
		{
			$model = Mage::getModel("borderfreelocalization/cache");
			$model->setDataType($data_type);
			$model->setMerchantId($merchantId);
			$model->setVersion(0);
			$model->save();
		}
		else
			$model = $collection->getFirstItem();
		
		$version = $model->getVersion();
		$uuid = uniqid('', true);
		$quiet = $quiet ? " quiet=\"true\"" : "";
		$merchantId = $merchantId != 0 ? " merchantId=\"$merchantId\"" : "";
		$credentials = Mage::getStoreConfig('borderfree_options/settings/apiuser') . ":" . Mage::helper('core')->decrypt(Mage::getStoreConfig('borderfree_options/settings/apipassword'));
		$endpoint = Mage::getStoreConfig('borderfree_options/settings/staging') ? Mage::getStoreConfig('borderfree_options/localization/staging') : Mage::getStoreConfig('borderfree_options/localization/production');
		
		$request = "<message>\n";
		$request .= "  <payload>\n";
		$request .= "    <getLocalizationDataRequest id=\"$uuid\">\n";
		$request .= "      <dataTypes>\n";
		$request .= "        <dataType localVersion=\"$version\"$merchantId$quiet>$data_type</dataType>\n";
		$request .= "      </dataTypes>\n";
		$request .= "    </getLocalizationDataRequest>\n";
		$request .= "  </payload>\n";
		$request .= "</message>\n";
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $endpoint);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: application/xml; charset=utf-8"));
		curl_setopt($ch, CURLOPT_USERPWD, $credentials);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		$result = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);
		
		$xml = new SimpleXMLElement($result);
		
		$errors = $xml->xpath("//errors");
		if(count($errors))
			throw new Exception($errors[0]->asXML());

		$dataVersion = $xml->xpath("//dataVersion");
		$attributes = $dataVersion[0]->attributes();
		$model->setVersion((string)$attributes["version"]);
		
		$newVersion = $version != $model->getVersion();
		return array($xml, $model, $newVersion);
	}
	
}