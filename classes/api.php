<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
* Handles all API calls for duitku plugin.
* @package   enrol_duitku
* @copyright maikdi
* @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

namespace enrol_duitku;

defined('MOODLE_INTERNAL') || die();

final class duitku_api {

	/**
	 * Alerts site admin of potential problems.
	 *
	 * @param string   	$apiKey				API key
	 * @param string   	$merchantCode		Merchant Code
	 * @param string	$merchantOrderId	Order ID generated by Moodle
	 * @param string	$productDetails		Course name
	 * @param string	$customerVaName		Customer name
	 * @param string	$email				Customer email
	 * @param string	$callbackUrl		Callback URL
	 * @param string	$returnUrl			Return URL
	 * @param string	$timestamp			Current timestamp in UNIX
	 * @param int		$paymentAmount		Amount to pay	
	 */
	public static function create_invoice($apiKey, $merchantCode, $merchantOrderId, $productDetails, $customerVaName, $email, $callbackUrl, $returnUrl, $timestamp, $paymentAmount){
		$params = array(
			'merchantOrderId' => $merchantOrderId,
			'productDetails' => $productDetails,
			'customerVaName' => $customerVaName,
			'email' => $email,
			'callbackUrl' => $callbackUrl,
			'returnUrl' => $returnUrl,
			'paymentAmount' => $paymentAmount,
			// 'expiryPeriod' => $expiryPeriod,
			// 'paymentMethod' => 'VA'
		);
		
		$params_string = json_encode($params);

		$timestamp = round(microtime(true) * 1000); //in milisecond
		$signature = hash('sha256', $merchantCode.$timestamp.$apiKey);
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params_string);                                                                  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(          
			'Content-Type: application/json',                                                                                
			'Content-Length: ' . strlen($params_string),
			'x-duitku-signature:' . $signature ,
			'x-duitku-timestamp:' . $timestamp ,
			'x-duitku-merchantcode:' . $merchantCode    
			)                                                                       
		);   
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		
		//execute post
		$request = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		if($httpCode == 200)
		{
			$result = json_decode($request, true);
			header('location: '. $result['paymentUrl']);
		}
		else
		{
			echo $httpCode;
		}
	}
}