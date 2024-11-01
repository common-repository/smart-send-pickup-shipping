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
	 * This class will enable access to the GLS Pakkeshop webservcie.
	 * The service descriptio is located at http://www.gls.dk/webservices_v2/wsPakkeshop.asmx
	 * Methods available in this class matches technical documentation v.1.0.0.2 from GLS.
	 *
	 * Requirements:
	 * PHP 5 with
	 * --enable-libxml
	 * --enable-soap
	 * 
	 * @author Dan Storm
	 * @created 15/01/2012
	 * @info http://catalystcode.net
	 * @version 1.0
	 */

	class Smartsend_Shipping_Gls
	{
		private $client, $encoding;
		public $error;
		
		
		/**
		 * This is the constructor method.
		 * This starts the SoapClient to GLS wsPakkeshop SOAP service.
		 * 
		 * @access	public
		 * @params	string	The encoding you wish to use - default is ISO-8859-1.
		 */
		public function __construct($encoding = 'UTF-8')//'ISO-8859-1')
		{
			$this->client = new SoapClient("http://www.gls.dk/webservices_v2/wsPakkeshop.asmx?WSDL", array('encoding' => $encoding));
		}
		
		/**
		 * This method returns an array of objects with all
		 * registrered parcel shops.
		 *
		 * @access	public
		 * @return	mixed	An array of objects or boolean false if service call failed.
		 */
		public function GetAllParcelShops()
		{
			try
			{	
				$shops = $this->client->GetAllParcelShops(array());
				return $shops->GetAllParcelShopsResult->PakkeshopData;		
			}
			catch(Exception $e)
			{
				$this->error = __METHOD__.': '.$e->getMessage();
				return false;
			}			
		}
		
		
		/**
		 * WARNING: This method is experimental according to the documentation
		 *
		 * This method returns an array of objects with parcel shops
		 * near the exact address specfied. The street name and number
		 * needs to be provided for this to work.
		 * The service call will fail is address doesn't exist.
		 *
		 * @acess	public
		 * @param	string	Exact street address with streetnumber
		 * @param	int	The zipcode for the provided address
		 * @param	int	The amount of parcel shops returned - default is 5.
		 * @return	mixed	An array of objects or boolean false if service call failed.
		 */
		public function GetNearstParcelShops( $street, $zipcode, $amount = 5)
		{
			try
			{
				$shops = $this->client->GetNearstParcelShops(array('street' => $street, 'zipcode' => $zipcode, 'Amount' => $amount));
				return $shops->GetNearstParcelShopsResult->parcelshops->PakkeshopData;
			}
			catch(Exception $e)
			{
				$this->error = __METHOD__.': '.$e->getMessage();
				return false;
			}
		}
		
		/**
		 * This method returns an object for the specific parcel shop from it's
		 * parcel shop number.
		 *
		 * @access	public
		 * @param	int	The number of the parcel shop.
		 * @return	mixed	An object of the parcel shop or boolean false if service call failed.
		 */
		public function GetOneParcelShop( $ParcelShopNumber )
		{
			try
			{	
				$shop = $this->client->GetOneParcelShop(array('ParcelShopNumber' => $ParcelShopNumber));
				return $shop->GetOneParcelShopResult;
			}
			catch(Exception $e)
			{
				$this->error = __METHOD__.': '.$e->getMessage();
				return false;
			}
			
		}

		/**
		 * This method returns an array of objects with parcel shops
		 * near in the specified zipcode.
		 *
		 * @access	public
		 * @param	int	The zipcode to find parcel shops in.
		 * @return	mixed	An array of objects or boolean false if service call failed.
		 */
		public function GetParcelShopsInZipcode( $zipcode )
		{
			try
			{	
				$shops = $this->client->GetParcelShopsInZipcode(array('zipcode' => $zipcode));
				
				if(isset($shops->GetParcelShopsInZipcodeResult->PakkeshopData))
				{
					if(!is_array($shops->GetParcelShopsInZipcodeResult->PakkeshopData))					
						return array($shops->GetParcelShopsInZipcodeResult->PakkeshopData);
					else
						return $shops->GetParcelShopsInZipcodeResult->PakkeshopData;
				}
				else
					return array();
			}
			catch(Exception $e)
			{
				$this->error = __METHOD__.': '.$e->getMessage();
				return false;
			}
			
		}	

		/**
		 * This method returns an array of objects with parcel shops
		 * near the exact address specfied OR the zipcode provided.
		 * If the streetname and number cannot be found in the provided zipcode
		 * the search for nearest parcel shops is expanded and limited to parcel 
		 * shops in the  provided zipcode.
		 *
		 * @access	public
		 * @param	string	Exact street address with streetnumber
		 * @param	int	The zipcode for the provided address
		 * @param	int	The amount of parcel shops returned - default is 5.
		 * @return	mixed	An array of objects or boolean false if service call failed.
		 */		
		public function SearchNearestParcelShops( $street, $zipcode, $amount = 5)
		{
			try
			{
				$shops = $this->client->SearchNearestParcelShops(array('street' => $street, 'zipcode' => $zipcode, 'Amount' => $amount));
				
				if(!is_array($shops->SearchNearestParcelShopsResult->parcelshops->PakkeshopData))
					return array($shops->SearchNearestParcelShopsResult->parcelshops->PakkeshopData);
				
				return $shops->SearchNearestParcelShopsResult->parcelshops->PakkeshopData;
			}
			catch(Exception $e)
			{
				$this->error = __METHOD__.': '.$e->getMessage();
				return false;
			}			
		}
		
	}
	
?>