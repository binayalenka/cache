<?php
/**
* This code is part of the Borderfree Magento Extension.
*
* @category Borderfree
* @package Borderfree_Localization
* @author Jamie Kail <jamie.kail@livearealabs.com>
* @copyright Copyright (c) 2013 Borderfree (http://www.borderfree.com)
*
*/
?>
<?php
/**
 * The Borderfree Localization helper class which provides function to assist in site localization.
 *
 * @category Borderfree
 * @package Borderfree_Localization
 * @author Jamie Kail <jamie.kail@livearealabs.com>
 */
class Borderfree_Localization_Helper_Data extends Mage_Core_Helper_Abstract
{	
	/**
	 * 
	 * @var Borderfree_Settings_Helper_Data
	 */
	private $settingsHelper = NULL;
	
	/**
	 * 
	 * @var Mage_Customer_Model_Session
	 */
	private $customerSession = NULL;

	public function __construct()
	{
		$this->settingsHelper = Mage::helper('borderfreesettings');
		$this->customerSession = Mage::getSingleton('customer/session');
	}
	
	/**
	 * Returns true if the product is restricted for the current ship to country
	 * 
	 * @param Mage_Catalog_Model_Product $product
	 * @return boolean
	 */
	public function isProductRestricted(Mage_Catalog_Model_Product $product)
	{
		if($this->settingsHelper->isBorderfreeEnabled())
			return true;
		else 
			return false;
	}
	
	/**
	 * Get the current ship to country
	 *
	 * @return string The current ship to country
	 */
	public function getShippingCountry()
	{		
		return $this->customerSession->getShippingCountry();
	}
		
	/**
	 * Sets the current shipping country
	 *
	 * @param string $shippingCountry The new ship to country
	 */
	public function setShippingCountry($shippingCountry)
	{
		if(empty($shippingCountry))
			return;

		$this->customerSession->setShippingCountry($shippingCountry);
		Mage::getModel('core/cookie')->set("shippingCountry", $shippingCountry, 0, "/");
		$this->setLCP();
	}
	
	/**
	 * Get the current ship to country
	 *
	 * @return string The current ship to country
	 */
	public function getIpCountry()
	{
		return $this->customerSession->getIPCountry();
	}
	
	/**
	 * Sets the current IP country
	 *
	 * @param string $ipCountry The new ship to country
	 */
	public function setIpCountry($ipCountry)
	{
		if(empty($ipCountry))
			return;
		
		$this->customerSession->setIPCountry($ipCountry);
		Mage::getModel('core/cookie')->set("ipCountry", $ipCountry, 0, "/");
		$this->setShippingCountry($ipCountry);
	}

	public function setLCP()
	{
		if($this->settingsHelper->isBorderfreeEnabled(true))
		{
			$collection = Mage::getModel("borderfreelocalization/lcp")->getCollection()->addFieldToFilter("country_code", $this->getShippingCountry());
			if($collection->count() != 0)
			{
				$lcp = $collection->getFirstItem();
				$this->customerSession->setLCPMultiplier($lcp->getMultiplier());
				$this->customerSession->setLCPRule($lcp->getRuleId());
				return;
			}
		}
	
		$this->customerSession->setLCPMultiplier(1);
		$this->customerSession->setLCPRule(0);
	}
	
	public function getLCPMultiplier()
	{
		$lcp = $this->customerSession->getLCPMultiplier();
		
		if($lcp == NULL)
			$lcp = 1;
		
		return $lcp;
	}
	
	public function getLCPRule()
	{
		return $this->customerSession->getLCPRule();
	}
	

	/**
	 * Gets the attribute option ID for the given attribute value.
	 * 
	 * @param string $attributeType
	 * @param string $attributeName
	 * @param string $value
	 * @return integer
	 */
	public function getOptionIdByValue($attributeType, $attributeName, $value)
	{
		$attributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode($attributeType, $attributeName);
		$attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
		return $attribute->getSource()->getOptionId($value);
	}
	
	/**
	 * Returns the ISO country code for the given country name.
	 *
	 * @param string $country Country Name
	 * @return string Country Code
	 */
	public function countryToCode($country)
	{
		return $this->countries[trim($country)];
	}
	
	/**
	 * Returns the country name for the givin ISO country code.
	 * 
	 * @param string $code
	 * @return string
	 */
	public function codeToCountry($code)
	{
		$codes = array_flip($this->countries);
		return $codes[$code];
	}
	
	/**
	 * An array that maps country name => country code.
	 * 
	 * @var array
	 */
	private $countries = array(
			"Afghanistan" => "AF",
			"Åland Islands" => "AX",
			"Albania" => "AL",
			"Algeria" => "DZ",
			"American Samoa" => "AS",
			"Andorra" => "AD",
			"Angola" => "AO",
			"Anguilla" => "AI",
			"Antarctica" => "AQ",
			"Antigua and Barbuda" => "AG",
			"Argentina" => "AR",
			"Armenia" => "AM",
			"Aruba" => "AW",
			"Australia" => "AU",
			"Austria" => "AT",
			"Azerbaijan" => "AZ",
			"Bahamas" => "BS",
			"Bahrain" => "BH",
			"Bangladesh" => "BD",
			"Barbados" => "BB",
			"Belarus" => "BY",
			"Belgium" => "BE",
			"Belize" => "BZ",
			"Benin" => "BJ",
			"Bermuda" => "BM",
			"Bhutan" => "BT",
			"Bolivia" => "BO",
			"Bosnia and Herzegovina" => "BA",
			"Botswana" => "BW",
			"Bouvet Island" => "BV",
			"Brazil" => "BR",
			"British Indian Ocean Territory" => "IO",
			"Brunei Darussalam" => "BN",
			"Bulgaria" => "BG",
			"Burkina Faso" => "BF",
			"Burundi" => "BI",
			"Cambodia" => "KH",
			"Cameroon" => "CM",
			"Canada" => "CA",
			"Cape Verde" => "CV",
			"Cayman Islands" => "KY",
			"Central African Republic" => "CF",
			"Chad" => "TD",
			"Chile" => "CL",
			"China" => "CN",
			"Christmas Island" => "CX",
			"Cocos (Keeling) Islands" => "CC",
			"Colombia" => "CO",
			"Comoros" => "KM",
			"Congo" => "CG",
			"Congo, The Democratic Republic of The" => "CD",
			"Cook Islands" => "CK",
			"Costa Rica" => "CR",
			"Cote D'ivoire" => "CI",
			"Croatia" => "HR",
			"Cuba" => "CU",
			"Cyprus" => "CY",
			"Czech Republic" => "CZ",
			"Denmark" => "DK",
			"Djibouti" => "DJ",
			"Dominica" => "DM",
			"Dominican Republic" => "DO",
			"Ecuador" => "EC",
			"Egypt" => "EG",
			"El Salvador" => "SV",
			"Equatorial Guinea" => "GQ",
			"Eritrea" => "ER",
			"Estonia" => "EE",
			"Ethiopia" => "ET",
			"Falkland Islands (Malvinas)" => "FK",
			"Faroe Islands" => "FO",
			"Fiji" => "FJ",
			"Finland" => "FI",
			"France" => "FR",
			"French Guiana" => "GF",
			"French Polynesia" => "PF",
			"French Southern Territories" => "TF",
			"Gabon" => "GA",
			"Gambia" => "GM",
			"Georgia" => "GE",
			"Germany" => "DE",
			"Ghana" => "GH",
			"Gibraltar" => "GI",
			"Greece" => "GR",
			"Greenland" => "GL",
			"Grenada" => "GD",
			"Guadeloupe" => "GP",
			"Guam" => "GU",
			"Guatemala" => "GT",
			"Guernsey" => "GG",
			"Guinea" => "GN",
			"Guinea-bissau" => "GW",
			"Guyana" => "GY",
			"Haiti" => "HT",
			"Heard Island and Mcdonald Islands" => "HM",
			"Holy See (Vatican City State)" => "VA",
			"Honduras" => "HN",
			"Hong Kong" => "HK",
			"Hungary" => "HU",
			"Iceland" => "IS",
			"India" => "IN",
			"Indonesia" => "ID",
			"Iran, Islamic Republic of" => "IR",
			"Iraq" => "IQ",
			"Ireland" => "IE",
			"Isle of Man" => "IM",
			"Israel" => "IL",
			"Italy" => "IT",
			"Jamaica" => "JM",
			"Japan" => "JP",
			"Jersey" => "JE",
			"Jordan" => "JO",
			"Kazakhstan" => "KZ",
			"Kenya" => "KE",
			"Kiribati" => "KI",
			"Korea, Democratic People's Republic of" => "KP",
			"Korea, Republic of" => "KR",
			"Kuwait" => "KW",
			"Kyrgyzstan" => "KG",
			"Lao People's Democratic Republic" => "LA",
			"Latvia" => "LV",
			"Lebanon" => "LB",
			"Lesotho" => "LS",
			"Liberia" => "LR",
			"Libyan Arab Jamahiriya" => "LY",
			"Liechtenstein" => "LI",
			"Lithuania" => "LT",
			"Luxembourg" => "LU",
			"Macao" => "MO",
			"Macedonia, The Former Yugoslav Republic of" => "MK",
			"Madagascar" => "MG",
			"Malawi" => "MW",
			"Malaysia" => "MY",
			"Maldives" => "MV",
			"Mali" => "ML",
			"Malta" => "MT",
			"Marshall Islands" => "MH",
			"Martinique" => "MQ",
			"Mauritania" => "MR",
			"Mauritius" => "MU",
			"Mayotte" => "YT",
			"Mexico" => "MX",
			"Micronesia, Federated States of" => "FM",
			"Moldova, Republic of" => "MD",
			"Monaco" => "MC",
			"Mongolia" => "MN",
			"Montenegro" => "ME",
			"Montserrat" => "MS",
			"Morocco" => "MA",
			"Mozambique" => "MZ",
			"Myanmar" => "MM",
			"Namibia" => "NA",
			"Nauru" => "NR",
			"Nepal" => "NP",
			"Netherlands" => "NL",
			"Netherlands Antilles" => "AN",
			"New Caledonia" => "NC",
			"New Zealand" => "NZ",
			"Nicaragua" => "NI",
			"Niger" => "NE",
			"Nigeria" => "NG",
			"Niue" => "NU",
			"Norfolk Island" => "NF",
			"Northern Mariana Islands" => "MP",
			"Norway" => "NO",
			"Oman" => "OM",
			"Pakistan" => "PK",
			"Palau" => "PW",
			"Palestinian Territory, Occupied" => "PS",
			"Panama" => "PA",
			"Papua New Guinea" => "PG",
			"Paraguay" => "PY",
			"Peru" => "PE",
			"Philippines" => "PH",
			"Pitcairn" => "PN",
			"Poland" => "PL",
			"Portugal" => "PT",
			"Puerto Rico" => "PR",
			"Qatar" => "QA",
			"Reunion" => "RE",
			"Romania" => "RO",
			"Russian Federation" => "RU",
			"Rwanda" => "RW",
			"Saint Helena" => "SH",
			"Saint Kitts and Nevis" => "KN",
			"Saint Lucia" => "LC",
			"Saint Pierre and Miquelon" => "PM",
			"Saint Vincent and The Grenadines" => "VC",
			"Samoa" => "WS",
			"San Marino" => "SM",
			"Sao Tome and Principe" => "ST",
			"Saudi Arabia" => "SA",
			"Senegal" => "SN",
			"Serbia" => "RS",
			"Seychelles" => "SC",
			"Sierra Leone" => "SL",
			"Singapore" => "SG",
			"Slovakia" => "SK",
			"Slovenia" => "SI",
			"Solomon Islands" => "SB",
			"Somalia" => "SO",
			"South Africa" => "ZA",
			"South Georgia and The South Sandwich Islands" => "GS",
			"Spain" => "ES",
			"Sri Lanka" => "LK",
			"Sudan" => "SD",
			"Suriname" => "SR",
			"Svalbard and Jan Mayen" => "SJ",
			"Swaziland" => "SZ",
			"Sweden" => "SE",
			"Switzerland" => "CH",
			"Syrian Arab Republic" => "SY",
			"Taiwan, Province of China" => "TW",
			"Tajikistan" => "TJ",
			"Tanzania, United Republic of" => "TZ",
			"Thailand" => "TH",
			"Timor-leste" => "TL",
			"Togo" => "TG",
			"Tokelau" => "TK",
			"Tonga" => "TO",
			"Trinidad and Tobago" => "TT",
			"Tunisia" => "TN",
			"Turkey" => "TR",
			"Turkmenistan" => "TM",
			"Turks and Caicos Islands" => "TC",
			"Tuvalu" => "TV",
			"Uganda" => "UG",
			"Ukraine" => "UA",
			"United Arab Emirates" => "AE",
			"United Kingdom" => "GB",
			"United States" => "US",
			"United States Minor Outlying Islands" => "UM",
			"Uruguay" => "UY",
			"Uzbekistan" => "UZ",
			"Vanuatu" => "VU",
			"Venezuela" => "VE",
			"Viet Nam" => "VN",
			"Virgin Islands, British" => "VG",
			"Virgin Islands, U.S." => "VI",
			"Wallis and Futuna" => "WF",
			"Western Sahara" => "EH",
			"Yemen" => "YE",
			"Zambia" => "ZM",
			"Zimbabwe" => "ZW",
			"Multi-Sourced" => "ZZ",
			"All" => "All"
	);
}
?>