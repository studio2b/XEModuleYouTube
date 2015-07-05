<?php
//Copyright (c) 2015 Studio2b
//YouTubeModule
//YoutubeController
//Studio2b(www.studio2b.kr)
//Michael Son(mson0129@gmail.com)
//07JUN2015(1.0.0.) - This module was newly created.
class youtubeController extends youtube {
	public function init() {
		//xFacility2014 - including the part of frameworks
		require_once($this->module_path."XFCurl.class.php");
		require_once($this->module_path."XFYoutube.class.php");
		require_once($this->module_path."XFYoutubeActivities.class.php");
		require_once($this->module_path."XFYoutubeChannels.class.php");
		require_once($this->module_path."XFYoutubePlaylistItems.class.php");
		require_once($this->module_path."XFYoutubePlaylists.class.php");
		require_once($this->module_path."XFYoutubeVideos.class.php");
	}
	
	public function procYoutubeUpdateCache($apiKey=null, $playlistId=null, $cacheTime=10, $items=null, $page=null, $asc=true) {
		$apiKey = !is_null($apiKey) ? $apiKey : $this->module_info->api_key;
		$playlistId = !is_null($playlistId) ? $playlistId : (!is_null($this->module_info->playlist_id) ? $this->module_info->playlist_id : "PLmtapKaZsgZt3g_uAPJbsMWdkVsznn_2R"); //실시간 인기 동영상 - 한국
		$cacheTime = is_numeric($cacheTime) ? $cacheTime : (is_numeric($this->module_info->cache_time) ? $this->module_info->cache_time : 10);
		$items = is_numeric($items) ? $items : null;
		$items = is_null($items) ? Context::get("items") : $items;
		$page = is_numeric($page) ? $page : null;
		$page = is_null($page) ? Context::get("page") : $page;
		$asc = is_null($asc) ? Context::get("asc") : $asc;
		$asc = $asc===false ? false : true;
		
		$start = microtime(true);
		$return = new stdClass();
		
		if(!is_null($apiKey)) {
			$youtube = new XFYoutube(null, $apiKey);
			$oYoutubeModel = getModel("youtube");
			$playlistId = $oYoutubeModel->getRealPlaylistId($apiKey, $playlistId);
			
			if(time() - $oYoutubeModel->getCacheTime($playlistId, $items, $page) > $cacheTime*60) {
				$return->loop = 0;
				$return->updatedVideos = 0;
				while(true) {
					$result = $youtube->playlistItems->browse("snippet", null, $playlistId, 50, $result[nextPageToken]);
					
					foreach($result[items] as $key=>$val) {
						$oYoutubeModel->setCache($playlistId, $return->loop*50+$key, $val);
						$return->updatedVideos++;
					}
					
					if(is_numeric($items) && is_numeric($page) && $asc && ($loop*50+count($result[items])>=($page)*$items || is_null($result[nextPageToken]))) {
						$return->message = "UPDATE_PART_OF_CACHE_ASC";
						$return->bool = true;
						if(is_null($result[nextPageToken])) {
							//END_OF_LIST
							$return->totalVideos = $return->updatedVideos;
						} else {
							$return->totalVideos = $result[pageInfo][totalResults];
						}
						break;
					} else if(is_numeric($items) && is_numeric($page) && $asc===false && $loop*50+count($result[items])>=$result[pageInfo][totalResults]-($page-1)*$items) {
						$return->message = "UPDATE_PART_OF_CACHE_DESC";
						$return->bool = true;
						$return->totalVideos = $result[pageInfo][totalResults];
						break;
					} else if(is_null($result[nextPageToken])) {
						$return->message = "END_OF_LIST";
						$return->bool = true;
						$return->totalVideos = $return->updatedVideos;
						break;
					} else $return->loop++;
				}
				
				$oYoutubeModel->setPlaylistInfo($playlistId, $return->totalVideos);
			} else {
				$temp = $oYoutubeModel->getPlaylistInfo($playlistId);
				$return->totalVideos = $temp->total_videos;
				$return->message = "NO_NEED_TO_UPDATE";
				$return->bool = false;
			}
		} else {
			$return->message = "NO_APIKEY";
			$return->bool = false;
		}
		$return->time = sprintf("%.9fs", microtime(true)-$start);
		if($_SERVER['HTTP_X_REQUESTED_WITH']=="XMLHttpRequest") {
			echo json_encode($return);
			exit;
		} else {
			return $return;
		}
	}
}
?>