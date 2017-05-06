<?php
/**
 * PHP-SSLLabs-API
 * 
 * This PHP library provides basic access to the SSL Labs API
 * and is build upon the official API documentation at
 * https://github.com/ssllabs/ssllabs-scan/blob/master/ssllabs-api-docs.md
 * 
 * @author Björn Roland <https://github.com/bjoernr-de>
 * @license GNU GENERAL PUBLIC LICENSE v3
 */

class sslLabsApi
{
	CONST API_URL = "https://api.ssllabs.com/api/v2";
	
	private $returnJsonObjects;
	
	/**
	 * sslLabsApi::__construct()
	 */
	public function __construct($returnJsonObjects = false)
	{
		$this->returnJsonObjects = (boolean) $returnJsonObjects;
	}
	
	/**
	 * sslLabsApi::fetchApiInfo()
	 * 
	 * API Call: info
	 * @see https://github.com/ssllabs/ssllabs-scan/blob/master/ssllabs-api-docs.md
	 */
	public function fetchApiInfo()
	{
		return ($this->sendApiRequest('info'));
	}
	
	/**
	 * sslLabsApi::fetchHostInformation()
	 * 
	 * API Call: analyze
	 * @see https://github.com/ssllabs/ssllabs-scan/blob/master/ssllabs-api-docs.md
	 * 
	 * @param string $host Hostname to analyze
	 * @param boolean $publish
	 * @param boolean $startNew
	 * @param boolean $fromCache
	 * @param int $maxAge
	 * @param string $all 
	 * @param boolean $ignoreMismatch
	 */
	public function fetchHostInformation($host, $publish = false, $startNew = false, $fromCache = false, $maxAge = NULL, $all = NULL, $ignoreMismatch = false)
	{
		$apiRequest = $this->sendApiRequest
		(
			'analyze',
			array
			(
				'host'				=> $host,
				'publish'			=> $publish,
				'startNew'			=> $startNew,
				'fromCache'			=> $fromCache,
				'maxAge'			=> $maxAge,
				'all'				=> $all,
				'ignoreMismatch'	=> $ignoreMismatch
			)
		);
		
		return ($apiRequest);
	}
	
	/**
	 * sslLabsApi::fetchHostInformationCached()
	 *
	 * API Call: analyze
	 * Same as fetchHostInformation() but prefer to receive cached information
	 *
	 * @param string $host
	 * @param int $maxAge
	 * @param string $publish
	 * @param string $ignoreMismatch
	 */
	public function fetchHostInformationCached($host, $maxAge, $publish = false, $ignoreMismatch = false)
	{
		return($this->fetchHostInformation($host, $publish, false, true, $maxAge, 'done', $ignoreMismatch));
	}
	
	/**
	 * sslLabsApi::fetchEndpointData()
	 * 
	 * API Call: getEndpointData
	 * @see https://github.com/ssllabs/ssllabs-scan/blob/master/ssllabs-api-docs.md
	 * 
	 * @param string $host
	 * @param string $s
	 * @param string $fromCache
	 * @return string 
	 */
	public function fetchEndpointData($host, $s, $fromCache = false)
	{
		$apiRequest = $this->sendApiRequest
		(
			'getEndpointData',
			array
			(
				'host'		=> $host,
				's'			=> $s,
				'fromCache'	=> $fromCache
			)
		);
		
		return ($apiRequest);
	}
	
	/**
	 * sslLabsApi::fetchStatusCodes()
	 * 
	 * API Call: getStatusCodes 
	 */
	public function fetchStatusCodes()
	{
		return ($this->sendApiRequest('getStatusCodes'));
	}
	
	/**
	 * sslLabsApi::sendApiRequest()
	 * 
	 * Send API request
	 * 
	 * @param string $apiCall
	 * @param array $parameters
	 * @return string JSON from API
	 */
	public function sendApiRequest($apiCall, $parameters = array())
	{
		//we also want content from failed api responses
		$context = stream_context_create
		(
			array
			(
				'http' => array
				(
					'ignore_errors' => true
				)
			)
		);
		
		$apiResponse = file_get_contents(self::API_URL . '/' . $apiCall . $this->buildGetParameterString($parameters), false, $context);
		
		if($this->returnJsonObjects)
		{
			return (json_decode($apiResponse));
		}		
		
		return ($apiResponse);	
	}
	
	/**
	 * sslLabsApi::setReturnJsonObjects()
	 * 
	 * Setter for returnJsonObjects
	 * Set true to return all API responses as JSON object, false returns it as simple JSON strings (default)
	 *  
	 * @param boolean $returnJsonObjects
	 */
	public function setReturnJsonObjects($returnJsonObjects)
	{
		$this->returnJsonObjects = (boolean) $returnJsonObjects;
	}
	
	/**
	 * sslLabsApi::getReturnJsonObjects()
	 * 
	 * Getter for returnJsonObjects
	 * 
	 * @return boolean true returns all API responses as JSON object, false returns it as simple JSON string
	 */
	public function getReturnJsonObjects()
	{
		return ($this->returnJsonObjects);
	}
	
	/**
	 * sslLabsApi::buildGetParameterString()
	 * 
	 * Helper function to build get parameter string for URL
	 * 
	 * @param array $parameters
	 * @return string
	 */
	private function buildGetParameterString($parameters)
	{
		$string = '';
			
		$counter = 0;
		foreach($parameters as $name => $value)
		{	
			if(!is_string($name) || (!is_string($value) && !is_bool($value) && !is_int($value)))
			{
				continue;
			}
			
			if(is_bool($value))
			{
				$value = ($value) ? 'on' : 'off';
			}
			
			$string .= ($counter == 0) ? '?' : '&';
			$string .= urlencode($name) . '=' . urlencode($value);
			
			$counter++;
		}
	
		return ($string);
	}
}