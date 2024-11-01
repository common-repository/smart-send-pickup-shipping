<?php
/**
	Copyright: (c) 2014 Smart Send ApS (email : kontakt@smartsend.dk)
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
	
	This module and all files are subject to the GNU General Public License v3.0
	that is bundled with this package in the file license.txt.
	It is also available through the world-wide-web at this URL:
	http://www.gnu.org/licenses/gpl-3.0.html
	If you did not receive a copy of the license and are unable to
	obtain it through the world-wide-web, please send an email
	to license@smartsend.dk so we can send you a copy immediately.

	DISCLAIMER
	Do not edit or add to this file if you wish to upgrade the plugin to newer
	versions in the future. If you wish to customize the plugin for your
	needs please refer to http://www.smartsend.dk
*/

/**
 * Post Danmark PHP API Integration
 *
 * This is a PHP composer package for simplifying Post Danmark integration
 * into PHP based webshops.
 *
 * @author Anders Bilfeldt, Smart Send ApS <anders@smartsend.dk>
 * @link http://www.smartsend.dk
 */
 
class Smartsend_Shipping_Postdanmark
{
	public $_consumerId = '992b32b6-7c13-4474-a2dc-1f8bcd58887d';
	public $_amount = 5;
	private $_endpoint, $_url;
	
	private $_response;
	private $_error;
	
	/**
	 * The constructor needs your Webshop GUID provided by SwipBox
	 * http://www.swipbox.com/
	 *
	 * By setting the second parameter to (boolean) true you enable
	 * testing mode.
	 * 
	 * @param string $guid The Webshop GUID provided by Swipbox
	 * @param boolean $test Set to true if you are testing. Defaults to false
	 */
	public function __construct($test = true)
	{		
		if( $test )
		{
			$_endpoint = "http://api.postnord.com/wsp/rest/BusinessLocationLocator/Logistics/ServicePointService_1.0/";
		}
		else
		{
			$_endpoint = "http://api.postnord.com/wsp/rest/BusinessLocationLocator/Logistics/ServicePointService_1.0/";
		}

		$this->_setEndpoint($_endpoint);
		//$this->_setGUID($_guid);		
		
	}
	
	/**
	 * set functions
	 */
	private function _setEndpoint($_endpoint) {
		$this->_endpoint = $_endpoint;
	}
	
	private function _setConsumerId($consumerId) {
		$this->_consumerId = $consumerId;
	}
	
	private function _setUrl($url) {
		$this->_url = $url;
	}
	
	private function _setResponse($response) {
		$this->_response = $response;
	}
	
	private function _setError($error) {
		$this->_error = $error;
	}
	
	private function _setAmount($amount) {
		$this->_amount = $amount;
	}
	
	/**
	 * get functions
	 */
	private function _getEndpoint() {
		return $this->_endpoint;
	}
	
	private function _getConsumerId() {
		return $this->_consumerId;
	}
	
	private function _getUrl() {
		return $this->_url;
	}
	
	public function getResponse() {
		return $this->_response;
	}
	
	public function getError() {
		return $this->_error;
	}
	
	public function getAmount() {
		return $this->_amount;
	}
	
	/**
	 * Find all parcel recieving stations
	 *
	 * @param Array $params An array of parameters as specified in the docs.
	 * @throws \Swipbox\Exception
	 * @return Array A decoded JSON array
	 */			
	public function findNearestByAddress( Array $params )
	{
		// Add number of stations to find
		$params['numberOfServicePoints'] = $this->getAmount();
		
		return $this->_execute('findNearestByAddress', $params);
	}
	
	
	private function _execute($service_name, $data)
	{
		/**
		* Set the url
		**/
		$this->_setUrl( $this->_getEndpoint().$service_name.'.json?'.http_build_query(array_merge($data, array('consumerId' => $this->_getConsumerId() ) ) ) );
		
		/**
		* Get the response
		**/
		$response = $this->_post();
		
		/**
		* If response is true, return output
		**/
		if($response != false) {
			return $response;
		} else {
			return false;
		}
	}
	
	private function _post() {

		/**
		* Post the response using CURL
		**/
		$ch = curl_init( $this->_getUrl() );
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		/**
		* Catch errors from CURL if any
		**/
		$error = curl_errno($ch);
		
		
		/**
		* If no CURL errors
		**/
		if(!$error) {
		
			$output 		= curl_exec($ch);
			$contenttype	= curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
			curl_close($ch);
		
			/**
			* Write reponse decoded and raw
			**/
			return array(
				'isSuccessful'	=> true,
				'output' 		=> $output, 
				'CONTENT_TYPE' 	=> $contenttype,
				);

		}
		/**
		* If CURL errors
		**/
		else {
			$this->_setError($error);
			return false;
		}
		
	}
	
	
}
