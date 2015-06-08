<?php
//Copyright (c) 2015 Studio2b
//xFacility2014
//xFYoutubeVideos
//Studio2b(www.studio2b.kr)
//Michael Son(mson0129@gmail.com)
//17JAN2015(0.1.0.) - Newly added.
//07JUN2015(1.0.0.) - Ported for XpressEngine
class XFYoutubeVideos {
	var $api_key, $token, $token_type;
	var $apiUri = "https://www.googleapis.com/youtube/v3/videos";
	
	function XFYoutubeVideos($token=NULL, $api_key=NULL) {
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
	
	function browse($part, $chart=null, $id=null, $myRating=null, $maxResults=null, $onBehalfOfContentOwner=null, $pageToken=null, $regionCode=null, $videoCategoryId=null) {
		if(!is_null($chart) || !is_null($id) || !is_null($myRating)) {
			$data[key] = $this->api_key;
			if(!is_null($part)) {
				$data[part] = $part;
			} else {
				$data[part] = "id, snippet, contentDetails, liveStreamingDetails, player, recordingDetails, statistics, status, topicDetails";
			}
			if(!is_null($chart) && $this->token) {
				$data[chart] = "mostPopular";
			} else if(!is_null($id)) {
				$data[id] = $id;
			} else if(!is_null($myRating) && $this->token) {
				$data[myRating] = (strtolower($myRating)=="like"||strtolower($myRating)=="dislike") ? strtolower($myRating) : "like";
			}
			if(is_numeric($maxResults) && !is_null($maxResults))
				$data[maxResults] = $maxResults;
			if(!is_null($onBehalfOfContentOwner)) {
				$data[onBehalfOfContentOwner] = $onBehalfOfContentOwner;
				$headerFlag=true;
			}
			if(!is_null($pageToken))
				$data[pageToken] = $pageToken;
			if(!is_null($regionCode))
				$data[regionCode] = $regionCode;
			if(!is_null($videoCategoryId))
				$data[videoCategoryId] = $videoCategoryId;
				
			if($headerFlag==true && !is_null($this->token))
				$header[] = "Authorization: ".$this->token_type." ".$this->token;
			
			$curlClass = new XFCurl("GET", $this->apiUri, $header, $data);
			$return = json_decode($curlClass->body, true);
			
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
	
	//part, autoLevels, onBehalfOfContentOwner, onBehalfOfContentOwnerChannel, stabilize
	function insert($part, $autoLevels=null, $onBehalfOfContentOwner=null, $onBehalfOfContentOwnerChannel=null, $stabilize=null, $fields=NULL, $data) {
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
			if($autoLevels==0 || $autoLevels==1)
				$param[autoLevels] = $autoLevels;
			if(!is_null($onBehalfOfContentOwner))
				$param[onBehalfOfContentOwner] = $onBehalfOfContentOwner;
			if(!is_null($onBehalfOfContentOwnerChannel))
				$param[onBehalfOfContentOwnerChannel] = $onBehalfOfContentOwnerChannel;
			if($stabilize==0 || $stabilize==1)
				$param[stabilize] = $stabilize;
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
	
	//part, onBehalfOfContentOwner
	function update($part, $onBehalfOfContentOwner=null, $fields=NULL, $data) {
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
			if(!is_null($onBehalfOfContentOwner))
				$param[onBehalfOfContentOwner] = $onBehalfOfContentOwner;
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
	
	function rate($id, $rating, $onBehalfOfContentOwner=null) {
		if(!is_null($this->token) && !is_null($id) && !is_null($rating)) {
			$header[] = "Authorization: ".$this->token_type." ".$this->token;
			$param[key] = $this->api_key;
			$param[id] = $id;
			$param[rating] = (strtolower($rating)=="like"||strtolower($rating)=="dislike"||strtolower($rating)=="none") ? strtolower($rating):"none";
			if(!is_null($onBehalfOfContentOwner))
				$param[onBehalfOfContentOwner] = $onBehalfOfContentOwner;
			$getParam = "?".XFCurl::getParameter($param);
			
			$curlClass = new XFCurl("POST", $this->apiUri."/rate".$getParam, $header, $data);
			$return = json_decode($curlClass->body, true);
			
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
	
	
	function getRating($id, $onBehalfOfContentOwner=null) {
		if(!is_null($this->token) && !is_null($id)) {
			$header[] = "Authorization: ".$this->token_type." ".$this->token;
			$param[key] = $this->api_key;
			$param[id] = $id;
			if(!is_null($onBehalfOfContentOwner))
				$param[onBehalfOfContentOwner] = $onBehalfOfContentOwner;
			$getParam = "?".XFCurl::getParameter($param);
			
			$curlClass = new XFCurl("GET", $this->apiUri."/getRating".$getParam, $header, $data);
			$return = json_decode($curlClass->body, true);
			
			if($curlClass->httpCode!=200 || !is_array($return)) {
				$return = false;
			}
		} else {
			$return = false;
		}
		return $return;
	}
	
	//
	function delete($id, $onBehalfOfContentOwner=null) {
		if(!is_null($this->token)) {
			$header[] = "Authorization: ".$this->token_type." ".$this->token;
			$param[key] = $this->api_key;
			$param[id] = $id;
			if(!is_null($onBehalfOfContentOwner))
				$param[onBehalfOfContentOwner] = $onBehalfOfContentOwner;
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
		
		return $return;
	}
}
?>