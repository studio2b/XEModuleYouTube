<?php
//Copyright (c) 2014 Studio2b
//xFacility2014
//xFYoutubeChannels
//Studio2b(www.studio2b.kr)
//Michael Son(mson0129@gmail.com)
//02DEC2014(1.0.0.) - Newly added.
//07JUN2015(1.0.0.) - Ported for XpressEngine
class XFYoutubeChannels {
	var $api_key, $token, $token_type;
	var $apiUri = "https://www.googleapis.com/youtube/v3/channels";

	function XFYoutubeChannels($token=NULL, $api_key=NULL) {
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
	
	function browse($part, $categoryId=NULL, $forUsername=NULL, $id=NULL, $managedByMe=NULL, $mine=NULL, $maxResults=5, $onBehalfOfContentOwner=NULL, $pageToken=NULL) {
		if(!is_null($this->token) || !is_null($categoryId) || !is_null($forUsername) || !is_null($id)) {
			//Body
			$data[key] = $this->api_key;
			if(!is_null($part)) {
				$data[part] = $part;
			} else {
				$data[part] = "id, snippet, brandingSettings, contentDetails, invideoPromotion, statistics, topicDetails";
			}
			if(!is_null($categoryId)) {
				$data[categoryId] = $categoryId;
			} else if(!is_null($forUsername)) {
				$data[forUsername] = $forUsername;
			} else if(!is_null($id)) {
				$data[id] = $id;
			} else if(!is_null($this->token)) {
				$data[mine] = "true";
				$headerFlag = true;
			}
			if(!is_null($maxResults))
				$data[maxResults] = $maxResults;
			if(!is_null($onBehalfOfContentOwner)) {
				$data[onBehalfOfContentOwner] = $onBehalfOfContentOwner;
				$headerFlag=true;
			}
			if(!is_null($pageToken))
				$data[pageToken] = $pageToken;
			//Header
			if($headerFlag==true && !is_null($this->token))
				$header[] = "Authorization: ".$this->token_type." ".$this->token;
			
			$curlClass = new XFCurl("GET", $this->apiUri, $header, $data);
			$return = json_decode($curlClass->body, true);
			
			if($curlClass->httpCode!=200 || !is_array($return))
				$return = false;
		} else {
			$return = false;
		}
		return $return;
	}
	
	function update() {
		
	}
}
?>