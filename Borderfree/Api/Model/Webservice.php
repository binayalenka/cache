<?php
/**
 * This code is part of the Borderfree Magento Extension.
 * 
 * @category Borderfree
 * @package Borderfree_Api
 * @author Jamie Kail <jamie.kail@livearealabs.com>
 * @copyright Copyright (c) 2013 Borderfree (http://www.borderfree.com)
 *
 */
?>
<?php
/**
 * Borderfree Webservice API
 * 
 * This Model creates and sends XML requests to the Borderfree API
 *
 * @category Borderfree
 * @package Borderfree_Api
 * @author Jamie Kail <jamie.kail@livearealabs.com>
 * @see XMLWriter
 */
class Borderfree_Api_Model_Webservice extends XMLWriter
{

	/**
	 * Initalizes the XML document for the Borderfree API
	 */
	public function __construct()
	{
		$this->openMemory();
		$this->setIndent(true);
		$this->setIndentString('   ');
		$this->startDocument('1.0', 'UTF-8');
		 
		$this->startElement('message');
		$this->startElement('payload');
	}
	
	/**
	 * Sets the API request Type
	 * 
	 * @param string $request API Request Type
	 */
	public function startRequest($request)
	{
		$this->startElement($request);
		$this->writeAttribute("id", uniqid('', true));
	}
	
	/**
	 * Starts an XML element with an optional array of attributes in kay => value form.
	 * 
	 * @param $name string XML Element Name
	 * @param $attributes array An array of attribute key value pairs.
	 * @see XMLWriter::startElement()
	 */
	public function startElement($name, $attributes = NULL)
	{
		parent::startElement($name);
		
		if($attributes != null)
		{
			foreach($attributes as $attributeName => $value)
			{
				$this->writeAttribute($attributeName, $value);
			}
		}
	}
	
	/**
	 * Writes an XML element to the request. 
	 * 
	 * @param $name string Name of the XML Element
	 * @param $content string The XML element text.
	 * @param $strip boolean Strip HTML from element text.
	 * @see XMLWriter::writeElement()
	 */
	public function writeElement($name, $content = null, $strip = false)
	{
		if($strip)
			$content = html_entity_decode(strip_tags($content), ENT_COMPAT, "UTF-8");
		
		parent::writeElement($name, $content);
	}
	
	/**
	 * Submits the API request to Borderfree
	 * 
	 * @param $endpoint string The Borderfree Endpoint for this request.
	 * @return string The result received from the Borderfree API request 
	 */
	public function submitRequest($endpoint)
	{
		$credentials = Mage::helper('borderfreesettings')->getApiCredentials();
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $endpoint);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: application/xml; charset=utf-8"));
		curl_setopt($ch, CURLOPT_USERPWD, $credentials);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->getDocument());
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		$result = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);
		
		return $result;	
	}

	/**
	 * Returns the complete XML API request;
	 * 
	 * @return string
	 */
    public function getDocument()
    {
        $this->endElement();
        $this->endElement();
        $this->endElement();
        $this->endDocument();
        return $this->outputMemory(false);
    }
}