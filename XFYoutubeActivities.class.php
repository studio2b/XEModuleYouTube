<?php
//Copyright (c) 2014 Studio2b
//xFacility2014
//xFYoutubeActivities
//Studio2b(www.studio2b.kr)
//Michael Son(mson0129@gmail.com)
//01DEC2014(1.0.0.)
//07JUN2015(1.0.0.) - Ported for XpressEngine
class XFYoutubeActivities {
	var $api_key, $token, $token_type;
	
	function XFYoutubeActivities($token=NULL, $api_key=NULL, $token_type=NULL) {
		$xFGoogle['token_type'] = "Bearer";
		if(!is_null($token))
			$this->token = $token;
		if(!is_null($api_key)) {
			$this->api_key = $api_key;
		} else {
			$this->api_key = $xFGoogle['api_key'];
		}
		if(!is_null($token_type)) {
			$this->token_type = $token_type;
		} else {
			$this->token_type = $xFGoogle['token_type'];
		}
	}
	
	function browse($part, $channelId=NULL, $home=NULL, $mine=NULL, $maxResults=5, $pageToken=NULL, $publishedAfter=NULL, $publishedBefore=NULL, $regionCode=NULL) {
		//Not Tested
		if(!is_null($this->token) || !is_null($channelId)) {
			//Essential
			$data[key] = $this->api_key;
			$data[part] = $part;
			//Selectional
			if(!is_null($this->token)) {
				if(!is_null($home) || !is_null($mine)) {
					$header[] = "Authorization: ".$this->token_type." ".$this->token;
					if(!is_null($home)) {
						$data[home] = $home;
					} else {
						$data[mine] = $mine;
					}
				} else {
					$data[channelId] = $channelId;
				}
			}
			//Additional(Optional)
			if(!is_null($maxResults))
				$data[maxResults] = $maxResults;
			if(!is_null($pageToken))
				$data[pageToken] = $pageToken;
			if(!is_null($publishedAfter))
				$data[publishedAfter] = $publishedAfter;
			if(!is_null($publishedBefore))
				$data[publishedBefore] = $publishedBefore;
			if(!is_null($regionCode)) {
				$data[regionCode] = $regionCode;
			} else {
				$languageClass = new XFLanguage();
				$languages = $languageClass->selectLanguages;
				list($trashcan, $data[regionCode]) = explode("-", $languages[0]);
			}
			
			$curlClass = new XFCurl("GET", "https://www.googleapis.com/youtube/v3/activities", $header, $data);
			$return = json_decode($curlClass->body, true);
			if($curlClass->httpCode==200 && is_array($return)) {
				//
			} else {
				$return = false;
			}
		} else {
			$return = false;
		}
		return $return;
	}
	
	function insert($part, $fields=NULL, $bodyArray) {
		//Not Tested
		$data[key] = $this->api_key;
		$data[part] = $part;
		if(!is_null($fields))
			$data[fields] = $fields;
		$getParam = "?".XFCurl::getParameter($data);
		unser($data);
		if(strpos($part, "snippet") && !is_null($bodyArray[snippet][description])) {
			$data = json_encode($bodyArray);
			$curlClass = new XFCurl("GET", "https://www.googleapis.com/youtube/v3/activities".$getParam, $header, $data);
			
			if($curlClass->httpCode==200) {
				$return = json_decode($curlClass->body, true);
			} else {
				$return = false;
			}
		} else {
			$return = false;
		}
		return $return;
	}
}
?>