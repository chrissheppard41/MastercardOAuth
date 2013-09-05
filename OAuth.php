<?php

class OAuthException extends Exception {
  // pass
}


class OAuthKeys {
	public $customerKey;
	public $privatekey;

	function __construct($custKey, $privKey) {
		$this->customerKey 	= $custKey;
		$this->privatekey 		= $privKey;
	}
}


abstract class AOAuth {
	abstract function api($url, $method, $body);
}

class OAuth extends AOAuth {
	protected $oAuthKeys;

	function __construct($poAuthKeys) {
		$this->oAuthKeys 		= $poAuthKeys;
	}

	public function api($url, $method, $body = NULL) {

		if($method == "GET") {
			$params = array("oauth_consumer_key"		=> $this->oAuthKeys->customerKey,
							"oauth_nonce"				=> time() . rand(1000, 9999),
							"oauth_timestamp"			=> time(),
							"oauth_version"				=> "1.0",
							"oauth_signature_method"	=> "RSA-SHA1");
		} else if($method == "POST" || $method == "PUT") {
			$hash = OAuthCommands::build_body_hash($body);
			$params = array("oauth_consumer_key"		=> $this->oAuthKeys->customerKey,
							"oauth_nonce"				=> time() . rand(1000, 9999),
							"oauth_timestamp"			=> time(),
							"oauth_version"				=> "1.0",
							"oauth_body_hash"			=> $hash,
							"oauth_signature_method"	=> "RSA-SHA1");
		}

		$request = new OAuthRequest($method, $url, $params);
		$request->build_signature($this->oAuthKeys->privatekey);
		echo $hash;
		echo "<hr>";
		echo OAuthCommands::urlencode_rfc3986($request->get_encoded_string());
		echo "<hr>";
		echo $request->get_base_string();
		echo "<hr>";
		echo $request->build_Authorization_header();


		$this->header[0] = $request->build_Authorization_header();
		if($method == "POST" || $method == "PUT") {
			$this->header[1] = "content-type: application/xml";
			$this->header[2] = "content-length: ".strlen($body);
		}


		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);

		if($method != "GET") {
			if($method == "POST"){
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
			}

			if($method == "PUT") {
    	curl_setopt($ch, CURLOPT_PUT, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
			}

			if($method == "DELETE") {
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
			}
		}

		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->header);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

		$response = curl_exec($ch);

		curl_close($ch);

		return $response;


	}

	public function __toString() {
	    return "OAuth[key=$this->customerKey,privateKey=$this->privateKey]";
	}
}







class OAuthRequest {
	protected $http_url;
	protected $http_method;
	protected $parameters;
	protected $OAuthHeaderParams;
	protected $OAuthHeader;

	protected $privateKey;

	protected $baseString;
	protected $encodedString;

	protected $bodyHash;

	function __construct($pMethod, $pUrl, $pParams) {
		$this->http_method 			= $pMethod;
		$this->http_url 			= $pUrl;
		$this->OAuthHeaderParams 	= $pParams;

		$arr = parse_url($pUrl);
		$this->parameters = array();
		parse_str($arr['query'], $this->parameters);

	}



	public function get_signature_base_string() {
	    $parts = array(
	    	$this->get_normalized_http_method(),
	    	$this->get_normalized_http_url(),
	    	$this->get_signable_parameters()
	    );
	    $parts = OAuthCommands::urlencode_rfc3986($parts);

	    return implode('&', $parts);
	}

	public function build_signature($key) {
		$this->privateKey = $key;
	    $this->baseString = $this->get_signature_base_string();

	    $ok = openssl_sign($this->baseString, $signature, $this->privateKey);

		$this->encodedString = base64_encode($signature);

		$arr = array("oauth_signature" => $this->encodedString );
		$this->OAuthHeaderParams = array_merge($arr, $this->OAuthHeaderParams);

	    return $this->encodedString;
  	}

  	public function build_Authorization_header($realm = NULL) {
	    $first = true;
		if($realm) {
	      	$out = 'Authorization: OAuth realm="' . OAuthCommands::urlencode_rfc3986($realm) . '"';
	      	$first = false;
	    } else
	      	$out = 'Authorization: OAuth';

	    $total = array();
	    foreach ($this->OAuthHeaderParams as $k => $v) {
	      	if (substr($k, 0, 5) != "oauth") continue;
	      	if (is_array($v)) {
	        	throw new OAuthException('Arrays not supported in headers');
	      	}
	      	$out .= ($first) ? ' ' : ',';
	      	$out .= OAuthCommands::urlencode_rfc3986($k) .
	              '="' .
	              OAuthCommands::urlencode_rfc3986($v) .
	              '"';
	      	$first = false;
	    }

	    return $out;
  	}






  	public function get_base_string() {
  		return $this->baseString;
  	}
  	public function get_encoded_string() {
  		return $this->encodedString;
  	}

	private function get_normalized_http_method() {
	    return strtoupper($this->http_method);
	}

	private function get_normalized_http_url() {
	    $parts = parse_url($this->http_url);

	    $scheme = (isset($parts['scheme'])) ? $parts['scheme'] : 'http';
	    $port = (isset($parts['port'])) ? $parts['port'] : (($scheme == 'https') ? '443' : '80');
	    $host = (isset($parts['host'])) ? strtolower($parts['host']) : '';
	    $path = (isset($parts['path'])) ? $parts['path'] : '';

	    if (($scheme == 'https' && $port != '443')
	        || ($scheme == 'http' && $port != '80')) {
	      	$host = "$host:$port";
	    }
	    return "$scheme://$host$path";
	  }

  	private function get_signable_parameters() {
	    $params 		= $this->parameters;
	    $oAuthparams 	= $this->OAuthHeaderParams;

	    if (isset($oAuthparams['oauth_signature'])) {
	      unset($oAuthparams['oauth_signature']);
	    }
	    return $this->build_http_query($params, $oAuthparams);
	}

	private function build_http_query($params, $oAuthparams = NULL) {
	    if (!$params) return '';
	    $params = OAuthCommands::urlencode_rfc3986($params);

	    if(is_array($oAuthparams)) {
	    	$params = array_merge($params, $oAuthparams);
	    }

	    uksort($params, 'strcmp');

	    $pairs = array();
	    foreach ($params as $parameter => $value) {
	      	if (is_array($value)) {
	        	sort($value, SORT_STRING);
	        	foreach ($value as $duplicate_value) {
	          	$pairs[] = $parameter . '=' . $duplicate_value;
	        	}
	      	} else {
	        	$pairs[] = $parameter . '=' . $value;
	      	}
	    }
	    return implode('&', $pairs);
	}
}





class OAuthCommands {
	public static function urlencode_rfc3986($input) {
		if (is_array($input)) {
		    return array_map(array('OAuthCommands', 'urlencode_rfc3986'), $input);
		} else if (is_scalar($input)) {
		    return str_replace(
		      '+',
		      ' ',
		      str_replace('%7E', '~', rawurlencode($input))
		    );
		} else {
		    return '';
		}
	}
  	public static function build_body_hash($body) {
  		if(empty($body)) {
  			throw new OAuthException('Requires Body with a PUT/POST request');
  		}

  		return base64_encode(sha1(utf8_encode($body),true));
  	}
}


?>
