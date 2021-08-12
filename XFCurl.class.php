<?php
//Copyright (c) 2021 Studio2b
//xFacility2014
//xFCurl
//Studio2b(studio2b.github.io)
//Michael Son(mson0129@gmail.com)
//30NOV2014(1.0.0.) - Newly added.
//07JUN2015(1.0.0.) - Ported for XpressEngine
//09JUL2015(1.0.1.) - The error returing false when there is no data to send is fixed.
//12AUG2021(1.0.2.) - getParameter() is clearly static method.
class XFCurl {
	var $method, $url, $what;
	var $protocol, $host, $port, $uri; //Parsing URL
	var $request, $result, $header, $body, $httpCode;

	function __construct($method, $url, $header=NULL, $data=NULL) {
		//method
		$this->method = strtolower($method);
		//url
		if(!is_null($url)) {
			$this->url = $url;
			if(!is_null($header) || !is_null($data)) {
				$this->request($header, $data);
			}
		}
	}
	
	static function getParameter($data) {
		if(is_array($data)) {
			$step = 0;
			$temp[$step] = $data;
			$i=0;
			while(true) {
				if(count($temp[$step])>0) {
					foreach($temp[$step] as $key => $value) {
						unset($temp[$step][$key]);
						$varNames[$step] = $key;
						if(is_array($value)) {
							$temp[++$step] = $value;
							break;
						} else {
							if(!is_null($param))
								$param .= "&";
							foreach($varNames as $varKey => $varName) {
								if($varKey==0)
									$param .= $varName;
								else
									$param .= sprintf("[%s]", $varName);
							}
							$param .= "=".urlencode($value);
						}
					}
				} else {
					if($step==0) {
						break;
					} else {
						unset($varNames[$step]);
						$step--;
					}
				}
			}
		}
		
		return $param;
	}
	
	function request($header=NULL, $data=NULL) {
		$crlf = "\r\n";
		
		if($this->method=="get") {
			if(!is_null($data)) {
				$param = $this->getParameter($data);
				if(!is_null($param))
					$this->url .= "?".$param;
			}
		} else {
			if(is_array($header)) {
				foreach($header as $value) {
					if(strpos($value, "json")!==false) {
						$jsonFlag = true;
						break;
					}
				}
			}
			if(!is_null($data)) {
				if($jsonFlag==true) {
					$param = json_encode($data);
				} else {
					$param = $this->getParameter($data);
				}
			}
			$postParam = $param;
		}
		
		$opt = array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER         => true,
			CURLINFO_HEADER_OUT		=> true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_ENCODING       => "",
			CURLOPT_USERAGENT      => "xFacility",
			CURLOPT_AUTOREFERER    => true,
			CURLOPT_CONNECTTIMEOUT => 120,
			CURLOPT_TIMEOUT        => 120,
			CURLOPT_MAXREDIRS      => 10,
			CURLOPT_SSLVERSION => 1,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_VERBOSE        => 1
		);
		
		$ch = curl_init($this->url);
		curl_setopt_array($ch, $opt);
		if($this->method=="post") {
			curl_setopt($ch, CURLOPT_POST, true);
		} else {
			curl_setopt($ch, CURLOPT_POST, false);
			if($this->method!="get") {
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);
			}
			if($this->method=="head") {
				curl_setopt($ch, CURLOPT_NOBODY, true);
			}
		}
		if(!is_null($postParam) && $this->method!="head")
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postParam);
		if(is_array($header) && !is_null($header))
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		$return = curl_exec($ch);
		$this->request = curl_getinfo($ch, CURLINFO_HEADER_OUT);
		$this->httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		list($this->header, $this->body) = explode("\r\n\r\n", $return);
		$this->result = $return;
		
		return $return;
	}
}
?>
