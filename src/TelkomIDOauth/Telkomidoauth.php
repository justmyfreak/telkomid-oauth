<?php
//OAuth v1.1
namespace TelkomIDOauth;

define('RUNTIME_O2_AUTHORIZE_URL', 'https://runtime.appprime.net/RuntimeBizO2_TelkomID/oauth/authorize');
define('RUNTIME_O2_TOKEN_URL', 'https://runtime.appprime.net/RuntimeBizO2_TelkomID/oauth/token');
define('RUNTIME_O2_LOGOUT_URL', 'https://runtime.appprime.net/RuntimeBizO2_TelkomID/user/logout');

define('RUNTIME_O2_RESOURCE_USER_PROFILE_PUBLIC_JSON_URL', 'https://runtime.appprime.net/RuntimeBizO2_TelkomID/resources/user_profile/public/json');
define('RUNTIME_O2_RESOURCE_USER_PROFILE_PRIVATE_JSON_URL', 'https://runtime.appprime.net/RuntimeBizO2_TelkomID/resources/user_profile/private/json');

define('RUNTIME_O2_RESOURCE_USER_SERVICES_PUBLIC_JSON_URL', 'https://runtime.appprime.net/RuntimeBizO2_TelkomID/resources/user_services/public/json');
define('RUNTIME_O2_RESOURCE_USER_SERVICES_PRIVATE_JSON_URL', 'https://runtime.appprime.net/RuntimeBizO2_TelkomID/resources/user_services/private/json');



class Telkomidoauth {

	/** public var */
	var $client_id;							
	var $client_secret;						
	var $redirect_uri;
	var $loginURL;
	var $logoutURL;

	/** private var */
	var $access_token;
	var $refresh_token;
	var $scope;		
	var $code;
	
	/** consutructor */
	function __construct($config=NULL) {
		
		$this->client_id = '';
		$this->client_secret = '';
		$this->redirect_uri = '';
		$this->scope = '';
		if (isset($config['code'])) $this->code = $config['code'];
		
	}

	function getLoginURL(){
		$params = array(
			'client_id' => $this->client_id,
			'response_type' => "code",
			'redirect_uri' => $this->redirect_uri,
    	'scope' => $this->scope
		);

		$this->setParam($params);
		$this->loginURL = RUNTIME_O2_AUTHORIZE_URL . '?' . http_build_query($this->params);

		return $this->loginURL;
	}
	
	function getAccessToken(){
		if (!isset($this->access_token)){
			$param="code=".$this->code."&redirect_uri=".$this->redirect_uri."&client_id=".$this->client_id."&client_secret=".$this->client_secret."&grant_type=authorization_code";
			$curl = curl_init(RUNTIME_O2_TOKEN_URL);
			curl_setopt( $curl, CURLOPT_POST, true );
			curl_setopt( $curl, CURLOPT_POSTFIELDS, $param);
			curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1);
			$auth = curl_exec( $curl );
			curl_close($curl);
			$secret = json_decode($auth);
			if (isset($secret->access_token)){
				$this->refresh_token=$secret->refresh_token;
				return $this->access_token=$secret->access_token;
			}else
				return $auth;
		}else
			return $this->access_token;
	}
	
	function refreshToken(){
			$param="client_id=".$this->client_id."&refresh_token=".$this->refresh_token."&client_secret=".$this->client_secret."&grant_type=refresh_token";
			$curl = curl_init(RUNTIME_O2_TOKEN_URL);
			curl_setopt( $curl, CURLOPT_POST, true );
			curl_setopt( $curl, CURLOPT_POSTFIELDS, $param);
			curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1);
			$auth = curl_exec( $curl );
			curl_close($curl);
			$secret = json_decode($auth);
			if (isset($secret->access_token))
				return $this->access_token=$secret->access_token;
			else
				return $auth;
	}
	
	function setAccessToken($token){
		return $this->access_token = $token;
	}
	
	function getUserProfile($state="public"){	
		if ($state == "public"){
			$url = RUNTIME_O2_RESOURCE_USER_PROFILE_PUBLIC_JSON_URL;
		}elseif ($state == "private"){
			$url = RUNTIME_O2_RESOURCE_USER_PROFILE_PRIVATE_JSON_URL;
		}else
			return "{error:\"parameter not defined\"}";
		$curl = curl_init($url);
		curl_setopt( $curl, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $this->access_token));
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt( $curl, CURLOPT_POST, true );
		$res=curl_exec($curl);
		$resource=json_decode($res);
		curl_close($curl);
	  return $resource;
	}
	
	function getUserServices($state="public"){	
		if ($state == "public"){
			$url = RUNTIME_O2_RESOURCE_USER_SERVICES_PUBLIC_JSON_URL;
		}elseif ($state == "private"){
			$url = RUNTIME_O2_RESOURCE_USER_SERVICES_PRIVATE_JSON_URL;
		}else
			return "{error:\"parameter not defined\"}";
		$curl = curl_init($url);
		curl_setopt( $curl, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $this->access_token));
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1);
		$resource=json_decode(curl_exec($curl));
		curl_close($curl);
		return $resource;
	}
	
	function logout(){	
		$url = RUNTIME_O2_LOGOUT_URL;
		$curl = curl_init($url);
		curl_setopt( $curl, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $this->access_token));
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt( $curl, CURLOPT_POST, true );
		$resource=json_decode(curl_exec($curl));
		curl_close($curl);
		return $resource;
	}
	
	function setClientID($key){
		$this->client_id=$key;
	}
	
	function getClientID(){
		return $this->client_id;
	}
	
	function setClientSecret($key){
		$this->client_secret=$key;
	}
	
	function getClientSecret(){
		return $this->client_secret;
	}
		
	function setDebugResponseFormat($format){
		if ($format == "JSON" || $format == "ARRAY")
			$this->debugResponseFormat = $format;
	}
	
	function setParam($param){
		$this->params = $param;
	}
	
}
?>