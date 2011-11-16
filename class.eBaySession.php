<?php
class eBaySession
{
	public $requestToken;
	private $devID;
	private $appID;
	private $certID;
	private $serverUrl;
	private $compatLevel;
	private $siteID;
	private $verb;
	
	/**	__construct
		Constructor to make a new instance of eBaySession with the details needed to make a call
		Input:	$userRequestToken - the authentication token for the user making the call
				$endPoint - ebay API endpoint
				$compatabilityLevel - API version this is compatable with
				$callName  - The name of the call being made (e.g. 'GeteBayOfficialTime')
		Output:	Response string returned by the server
	*/
	public function __construct($endPoint, $compatabilityLevel, $callName)
	{
		$this->requestToken = 'xxxx';
		$this->devID = 'xxx';
		$this->appID = 'xxx';
		$this->certID = 'xxx';
		$this->compatLevel = $compatabilityLevel;
		$this->siteID = '0';
		$this->verb = $callName;
        	$this->serverUrl = $endPoint;	
	}
	
	
	/**	sendHttpRequest
		Sends a HTTP request to the server for this session
		Input:	$requestBody
		Output:	The HTTP Response as a String
	*/
	public function sendPostRequest($requestBody)
	{
		//build eBay headers using variables passed via constructor
		$headers = $this->buildEbayHeaders();
		
		//initialise a CURL session
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $this->serverUrl);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		
		//set method as POST
		curl_setopt($curl, CURLOPT_POST, 1);
		
		//set the XML body of the request
		curl_setopt($curl, CURLOPT_POSTFIELDS, $requestBody);
		
		//set it to return the transfer as a string from curl_exec
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		
		//Send the Request
		$response = curl_exec($curl);
		
		//close the connection
		curl_close($curl);
		
		//return the response
		return $response;
	}
	
	//sends Curl http get request and return 
	function geteBayListings($page = 1) {
		
		$url  = 'http://svcs.ebay.com/services/search/FindingService/v1';
		$url .= '?OPERATION-NAME=findItemsIneBayStores';
		$url .= '&SERVICE-VERSION=1.11.0';
		$url .= '&storeName=xxxx';
		$url .= '&SECURITY-APPNAME=xxxx';
		$url .= '&GLOBAL_ID=0';
		$url .= '&paginationInput.entriesPerPage=100';
		//$url .= '&paginationOutput.totalPages';
		$url .= '&RESPONSE-DATA-FORMAT=XML';
		
		if (isset($page)) {
			$url .= '&paginationInput.pageNumber='.$page;
		}
		$curl = curl_init()	;	
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER,array('Accept: application/xml'));
		curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate');
		$xml = new simpleXMLElement(curl_exec($curl));
		echo curl_error($curl);
		curl_close($curl);
		return $xml;
	}
		
	/**	buildEbayHeaders
		Generates an array of string to be used as the headers for the HTTP request to eBay
		Output:	String Array of Headers applicable for this call
	*/
	private function buildEbayHeaders()
	{
		$headers = array (
			//Regulates versioning of the XML interface for the API
			'X-EBAY-API-COMPATIBILITY-LEVEL: ' . $this->compatLevel,
			//version info for shopping api
			'X-EBAY-API-VERSION: ' . $this->compatLevel,
			//set the keys
			//'X-EBAY-API-DEV-NAME: ' . $this->devID,
			'X-EBAY-API-APP-NAME: ' . $this->appID,
			//'X-EBAY-API-CERT-NAME: ' . $this->certID,
			
			//Encoding - required for shopping api POST request
			'X-EBAY-API-REQUEST-ENCODING:XML',
			
			//the name of the call we are requesting
			'X-EBAY-API-CALL-NAME: ' . $this->verb,			
			
			//SiteID must also be set in the Request's XML
			//SiteID = 0  (US) - UK = 3, Canada = 2, Australia = 15, ....
			//SiteID Indicates the eBay site to associate the call with
			'X-EBAY-API-SITEID: ' . $this->siteID,
		);
		return $headers;
	}
	
	//get eBay item description from trading api
	public function getItemDescription($itemId){
		$requestXmlBody  = '<?xml version="1.0" encoding="utf-8" ?>';
		$requestXmlBody .= '<GetSingleItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
		$requestXmlBody .= "<ItemID>$itemId</ItemID>";
		$requestXmlBody .= '<RequesterCredentials>';
		$requestXmlBody .= "<eBayAuthToken>$this->requestToken</eBayAuthToken>";
		$requestXmlBody .= '</RequesterCredentials>';
		$requestXmlBody .= '<IncludeSelector>Details,Description,TextDescription,ItemSpecifics</IncludeSelector>';
		$requestXmlBody .= '</GetSingleItemRequest>';
	
		$responseXml = $this->sendPostRequest($requestXmlBody);
		return $responseXml;
	}
	
	public function getDiscogsListing($uri = NULL){
		if (!is_null($uri)) {
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $uri);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_HTTPHEADER,array('Accept: application/xml'));
			curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate');
			$xml = new simpleXMLElement(curl_exec($curl));
			echo curl_error($curl);
			curl_close($curl);
			return $xml;
		}
	}	
}
