<?php
//Copyright (c) 2014 Studio2b
//xFacility2014
//xFYoutubePlaylistItems
//Studio2b(www.studio2b.kr)
//Michael Son(mson0129@gmail.com)
//02DEC2014(1.0.0.) - Newly added.
//07JUN2015(1.0.0.) - Ported for XpressEngine
class XFYoutubePlaylistItems {
	var $api_key, $token, $token_type;
	var $apiUri = "https://www.googleapis.com/youtube/v3/playlistItems";
	
	function XFYoutubePlaylistItems($token=NULL, $api_key=NULL) {
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
	
	function browse($part, $id=NULL, $playlistId=NULL, $maxResults=NULL, $pageToken=NULL, $videoId=NULL) {
		if(!is_null($id) || !is_null($playlistId)) {
			$data[key] = $this->api_key;
			if(!is_null($part)) {
				$data[part] = $part;
			} else {
				$data[part] = "id, snippet, contentDetails, status";
			}
			if(!is_null($id)) {
				$data[id] = $id;
			} else if(!is_null($playlistId)) {
				$data[playlistId] = $playlistId;
			}
			if(!is_null($maxResults))
				$data[maxResults] = $maxResults;
			if(!is_null($pageToken))
				$data[pageToken] = $pageToken;
			if(!is_null($videoId))
				$data[videoId] = $videoId;
			
			$curlClass = new XFCurl("GET", $this->apiUri, $header, $data);
			$return = json_decode($curlClass->body, true);
			//var_dump($curlClass->body);
			if($curlClass->httpCode==200 && is_array($return)) {
				//Timestamp
				if(is_array($return[items])) {
					foreach($return[items] as $row => $columns) {
						if(!is_null($columns[snippet][publishedAt])) {
							//2015-01-11T02:38:28.000Z
							$year = sprintf("%d", substr($columns[snippet][publishedAt], 0, 4));
							$month = sprintf("%d", substr($columns[snippet][publishedAt], 5, 2));
							$day = sprintf("%d", substr($columns[snippet][publishedAt], 8, 2));
							$hour = sprintf("%d", substr($columns[snippet][publishedAt], 11, 2));
							$minute = sprintf("%d", substr($columns[snippet][publishedAt], 14, 2));
							$second = sprintf("%d", substr($columns[snippet][publishedAt], 17, 2));
							$return[items][$row][snippet][utc] = mktime($hour, $minute, $second, $month, $day, $year);
							$return[items][$row][snippet][kst] = mktime($hour+9, $minute, $second, $month, $day, $year);
						}
					}
				}
			} else {
				$return = false;
			}
		} else {
			$return = false;
		}
		return $return;
	}
	
	function insert($part, $onBehalfOfContentOwner=NULL, $fields=NULL, $data) {
		if(!is_null($this->token)) {
			$header[] = "Authorization: ".$this->token_type." ".$this->token;
			$header[] = "Content-Type:  application/json";
			$param[key] = $this->api_key;
			if(!is_null($part)) {
				$param[part] = $part;
			} else {
				$param[part] = "snippet, contentDetails, status";
			}
			if(!is_null($data[status][privacyStatus]) && strpos($part, "contentDetails")===false)
				$param[part] .= ",contentDetails";
			$getParam = "?".XFCurl::getParameter($param);
				
			$curlClass = new XFCurl("POST", $this->apiUri.$getParam, $header, $data);
			$return = json_decode($curlClass->body, true);
			
			if($curlClass->httpCode==200 && is_array($return)) {
				//$return = true;
			} else {
				$return = false;
			}
		} else {
			$return = false;
		}
		return $return;
	}
	
	function update($part, $fields=NULL, $data) {
		if(!is_null($this->token)) {
			$header[] = "Authorization: ".$this->token_type." ".$this->token;
			$header[] = "Content-Type:  application/json";
			$param[key] = $this->api_key;
			if(!is_null($part)) {
				$param[part] = $part;
			} else {
				$param[part] = "snippet, contentDetails, status";
			}
			if(!is_null($data[status][privacyStatus]) && strpos($part, "contentDetails")===false)
				$param[part] .= ",contentDetails";
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
	
	function delete($id) {
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