<?php
//Copyright (c) 2021 Studio2b
//YouTubeModule
//YoutubeModel
//Studio2b(studio2b.github.io)
//Michael Son(mson0129@gmail.com)
//07JUN2015(1.0.0.) - This file was newly created.
//19JUN2015(1.1.0.) - This module was updated for cache.
//26JUN2015(1.1.1.) - The queries are converted to XE XML Query(from low-level to high-level).
//02JUL2015(2.0.0.) - Codes have been rewrited to apply new structure and logics.
//12AUG2021(1.0.1.) - Array keys are wrapped with double quotation marks("").
class youtubeModel extends youtube {
	function init() {
		//xFacility2014 - including the part of frameworks
		require_once($this->module_path."XFCurl.class.php");
		require_once($this->module_path."XFYoutube.class.php");
		require_once($this->module_path."XFYoutubeActivities.class.php");
		require_once($this->module_path."XFYoutubeChannels.class.php");
		require_once($this->module_path."XFYoutubePlaylistItems.class.php");
		require_once($this->module_path."XFYoutubePlaylists.class.php");
		require_once($this->module_path."XFYoutubeVideos.class.php");
	}
	
	public function triggerModuleListInSitemap(&$arr) {
		array_push($arr, "youtube");
	}
	
	//From YouTube Server
	public function getRealPlaylistId($apiKey, $playlistId) {
		$youtube = new XFYoutube(null, $apiKey);
		if(!is_null($apiKey)) {
			$return = !is_null($playlistId) ? $playlistId : (!is_null($this->module_info->playlist_id) ? $this->module_info->playlist_id : "PLmtapKaZsgZt3g_uAPJbsMWdkVsznn_2R"); //실시간 인기 동영상 - 한국
			$result = $youtube->playlistItems->browse("id", null, $return, 0);
			if($result===false) {
				$channel = $youtube->channels->browse("contentDetails", null, $return);
				if($channel===false || is_null($channel["items"][0]["contentDetails"]["relatedPlaylists"]["uploads"])) {
					$channel = $youtube->channels->browse("contentDetails", null, null, $return);
					if($channel===false || is_null($channel["items"][0]["contentDetails"]["relatedPlaylists"]["uploads"])) {
						$return = false;
					} else {
						$return = $channel["items"][0]["contentDetails"]["relatedPlaylists"]["uploads"];
					}
				} else {
					$return = $channel["items"][0]["contentDetails"]["relatedPlaylists"]["uploads"];
				}
			}
			unset($result, $channel);
		} else {
			$return = false;
		}
		return $return;
	}
	
	function getPosition($apiKey, $playlistId, $videoId) {
		$youtube = new XFYoutube(null, $apiKey);
		if(!is_null($playlistId) && !is_null($videoId)) {
			$counter = 0;
			while(true) {
				$result = $youtube->playlistItems->browse("contentDetails", NULL, $playlistId, 50, $result["nextPageToken"]);
				if($result===false) {
					$return = false;
					break;
				} else {
					foreach($result["items"] as $val) {
						$counter++;
						if($videoId==$val["contentDetails"]["videoId"]) {
							$return = $counter;
							break;
						}
					}
				}
				if(is_numeric($return)) {
					break;
				} else if(is_null($result["nextPageToken"])) {
					$return = false;
					break;
				}
			}
		} else {
			$return = false;
		}
	
		return $return;
	}
	
	public function getVideo($apiKey, $videoId) {
		$youtube = new XFYoutube(null, $apiKey);
		$result = $youtube->videos->browse("snippet", NULL, $videoId);
		return $result["items"][0];
	}
	
	//From/To Local DB
	public function setCache($id, $no, $item) {
		$args = new stdClass();
		$args->id = $id;
		$args->no = $no;
		$result = executeQuery("youtube.peruseCache", $args);
		
		$args->title = $item["snippet"]["title"];
		$args->description = $item["snippet"]["description"];
		$args->channel = $item["snippet"]["channelTitle"];
		$args->utc = $item["snippet"]["utc"]; //utc + 32400 = utc + 9h * 60m * 60s = kst
		$args->item = json_encode($item);
		$args->timestamp = time();
		if(empty($result->data)) {
			$return = executeQuery("youtube.insertCache", $args);
		} else {
			$return = executeQuery("youtube.updateCache", $args);
		}
		
		return true;
	}
	
	public function getCacheTime($id, $items=null, $page=null) {
		$args = new stdClass();
		$args->id = $id;
		if(is_numeric($items) && is_numeric($page)) {
			$args->start = $items*($page-1);
			$args->end = $items*$page;
			$args->items = $items;
			$result = executeQuery("youtube.browseSomeCacheTime", $args);
		} else {
			$result = executeQuery("youtube.browseCacheTime", $args);
		}
		return ($result->toBool()===false) ? false : $result->data->timestamp;
	}
	
	public function getCaches($playlistId=NULL, $items=20, $page=1, $asc=true) {
		$args = new stdClass();
		$args->id = !is_null($playlistId) ? $playlistId : (!is_null($this->module_info->playlist_id) ? $this->module_info->playlist_id : "PLmtapKaZsgZt3g_uAPJbsMWdkVsznn_2R"); //실시간 인기 동영상 - 한국
		$args->index = "no";
		$args->listCount = is_numeric($items) ? $items : 20;
		$args->page = is_numeric($page) ? $page : 1;
		$args->order = $asc===false ? "desc" : "asc";
		$output = executeQuery("youtube.browseCaches", $args);
		return ($output->toBool()===false) ? false : $output->data;
	}
	
	public function setPlaylistInfo($id, $totalVideos) {
		if(!is_null($id)) {
			$args = new stdClass();
			$args->id = $id;
			$result = executeQuery("youtube.perusePlaylistInfo", $args);
			
			$args->totalVideos = $totalVideos;
			$args->timestamp = time();
			if(empty($result->data)) {
				$return = executeQuery("youtube.insertPlaylistInfo", $args);
			} else {
				$return = executeQuery("youtube.updatePlaylistInfo", $args);
			}
			unset($args);
		} else {
			$return = false;
		}
		return $return;
	}
	
	public function getPlaylistInfo($id) {
		if(!is_null($id)) {
			$args = new stdClass();
			$args->id = $id;
			$result = executeQuery("youtube.perusePlaylistInfo", $args);
			if(empty($result->data)) {
				$return = false;
			} else {
				$return = $result->data;
			}
		} else {
			$return = false;
		}
		return $return;
	}
}
?>