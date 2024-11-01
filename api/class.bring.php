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
 * Author: 	Smart Send
 * Url: 	www.SmartSend.dk
 * Date: 	05/08/14
 */

class Smartsend_Shipping_Bring {

    private $_endpoint, $_url;
    private $_response;
    public  $_responsenumber = 5;

    public function __construct( ) {
        
        $this->_setEndpoint("https://api.bring.com/pickuppoint/api/pickuppoint/");

    }

    private function _setUrl( $url ) {
        $this->_url = $url;
    }

    private function _getUrl() {
        return $this->_url;
    }
    
    private function _setEndpoint( $url ) {
        $this->_endpoint = $url;
    }

    private function _getEndpoint() {
        return $this->_endpoint;
    }
    
    public function getResponseNumber() {
        return $this->_responsenumber;
    }

    public function getResponse() {
        return json_decode( $this->_response );
    }

    private function _execute( $code ) {
    
    	/**
		* Set the url
		**/
		$this->_setUrl( $this->_getEndpoint().$code );
		
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

    /*
     * Find pickup points by zipcode
     */
    public function findByZip( $country, $zip ) {
        return $this->_execute( $country . "/postalCode/" . $zip . ".json?numberOfResponses=".$this->getResponseNumber() );
    }

    /*
     * Find pickup points by gps coordinates
     */
    public function findByCoordinate($country, $latitude, $longitude) {
        return $this->_execute( $country . "/location/". $latitude ."/". $longitude .".json?numberOfResponses=".$this->getReponseNumber() );
    }
    
    /*
     * Find pickup points by address
     */
    public function findByAddress( $country, $zip, $street=null, $streetNumber=null) {
        return $this->_execute( $country . "/postalCode/" . $zip . ".json?numberOfResponses=".$this->getResponseNumber().($street != null ? '&street='.$street : '').($streetNumber != null ? '&streetNumber='.$streetNumber : '') );
    }

} 