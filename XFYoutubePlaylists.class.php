<?php
//Copyright (c) 2014 Studio2b
//xFacility2014
//xFYoutubePlaylists
//Studio2b(www.studio2b.kr)
//Michael Son(mson0129@gmail.com)
//01DEC2014(1.0.0.) - Newly added.
//07JUN2015(1.0.0.) - Ported for XpressEngine
class XFYoutubePlaylists {
	var $api_key, $token, $token_type;
	var $apiUri = "https://www.googleapis.com/youtube/v3/playlists";
	
	function XFYoutubePlaylists($token=NULL, $api_key=NULL, $token_type=NULL) {
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
	
	function browse($part=NULL, $channelId=NULL, $id=NULL, $mine=NULL, $maxResults=5, $onBehalfOfContentOwner=NULL, $onBehalfOfContentOwnerChannel=NULL, $pageToken=NULL) {
		if(!is_null($this->token) || !is_null($channelId) || !is_null($id)) {
			$data[key] = $this->api_key;
			if(!is_null($part)) {
				$data[part] = $part;
			} else {
				$data[part] = "id,snippet,status";
			}
			if(!is_null($channelId)) {
				$data[channelId] = $channelId;
			} else if(!is_null($id)) {
				$data[id] = $id;
			} else if(!is_null($this->token)) {
				$data[mine] = "true"; //It's "true", not true(==1).
				$headerFlag = true;
			}
			if(!is_null($maxResults))
				$data[maxResults] = $maxResults;
			if(!is_null($onBehalfOfContentOwner)) {
				$data[onBehalfOfContentOwner] = $onBehalfOfContentOwner;
				$headerFlag=true;
			}
			if(!is_null($onBehalfOfContentOwnerChannel)) {
				$data[onBehalfOfContentOwnerChannel] = $onBehalfOfContentOwnerChannel;
				$headerFlag=true;
			}
			if(!is_null($pageToken))
				$data[pageToken] = $pageToken;
			
			if($headerFlag==true && !is_null($this->token))
				$header[] = "Authorization: ".$this->token_type." ".$this->token;
			
			$curlClass = new XFCurl("GET", $this->apiUri, $header, $data);
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
	
	function insert($part="snippet", $onBehalfOfContentOwner=NULL, $onBehalfOfContentOwnerChannel=NULL, $fields=NULL, $data) {
		if(!is_null($this->token)) {
			$header[] = "Authorization: ".$this->token_type." ".$this->token;
			$header[] = "Content-Type:  application/json";
			$param[key] = $this->api_key;
			if(!is_null($part)) {
				$param[part] = $part;
			} else {
				$param[part] = "snippet";
			}
			if(!is_null($data[status][privacyStatus]) && strpos($part, "status")===false)
				$param[part] .= ",status";
			$getParam = "?".XFCurl::getParameter($param);
			
			$curlClass = new XFCurl("POST", $this->apiUri.$getParam, $header, $data);
			$return = json_decode($curlClass->body, true);
			
			if($curlClass->httpCode==200 && is_array($return)) {
				$return = true;
			} else {
				$return = false;
			}
		} else {
			$return = false;
		}
		return $return;
	}
	
	function update($part, $onBehalfOfContentOwner=NULL, $fields=NULL, $data) {
		if(!is_null($this->token)) {
			$header[] = "Authorization: ".$this->token_type." ".$this->token;
			$header[] = "Content-Type:  application/json";
			$param[key] = $this->api_key;
			if(!is_null($part)) {
				$param[part] = $part;
			} else {
				$param[part] = "snippet";
			}
			if(!is_null($data[status][privacyStatus]) && strpos($part, "status")===false)
				$param[part] .= ",status";
			if(!is_null($fields))
				$param[fields] = $fields;
			$getParam = "?".XFCurl::getParameter($param);
				
			$curlClass = new XFCurl("PUT", $this->apiUri.$getParam, $header, $data);
			$return = json_decode($curlClass->body, true);
			
			if($curlClass->httpCode==200 && is_array($return)) {
			
			} else {
				$return = false;
			}
		} else {
			$return = false;
		}
		return $return;
	}
	
	function delete($id, $onBehalfOfContentOwner=NULL) {
		if(!is_null($this->token)) {
			$header[] = "Authorization: ".$this->token_type." ".$this->token;
			$param[key] = $this->api_key;
			$param[id] = $id;
			$getParam = "?".XFCurl::getParameter($param);
			$curlClass = new XFCurl("DELETE", $this->apiUri.$getParam, $header, $data);
			
			if($curlClass->httpCode==204) {
				$return = true;
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