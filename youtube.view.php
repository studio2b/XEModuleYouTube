<?php
//Copyright (c) 2015 Studio2b
//YouTubeModule
//YoutubeView
//Studio2b(www.studio2b.kr)
//Michael Son(mson0129@gmail.com)
//07JUN2015(1.0.0.) - This module was newly created.
//08JUN2015(1.0.1.) - The skin bug is fixed.(It can be ouccred when the skin value of module has nothing.)
//08JUN2015(1.1.0.) - channelUrl is supported.
//08JUN2015(1.1.1.) - The bug are fixed(Page numbering - a user peruse a video without a page number. The number of list items - Admin cannot edit the number of items in a page.).
//09JUL2015(1.1.2.) - Optimizing processing time
//11JUL2015(1.2.0.) - It's support showing playlists in invert order and an error msg.
//11JUL2015(1.2.1.) - Paging error when there is a unlisted or private video in a playlist was corrected.
//19JUN2015(1.3.0.) - This module was updated for cache.
//26JUN2015(1.3.1.) - When cache data are crashed, read data from YouTube server NOT from cache.
//02JUL2015(2.0.0.) - Codes have been rewrited to apply new structure and logics.
//05JUL2015(2.1.0.) - Category is supported.
class youtubeView extends youtube {
	function init() {
		//xFacility2014 - including the part of frameworks
		require_once($this->module_path."XFCurl.class.php");
		require_once($this->module_path."XFYoutube.class.php");
		require_once($this->module_path."XFYoutubeActivities.class.php");
		require_once($this->module_path."XFYoutubeChannels.class.php");
		require_once($this->module_path."XFYoutubePlaylistItems.class.php");
		require_once($this->module_path."XFYoutubePlaylists.class.php");
		require_once($this->module_path."XFYoutubeVideos.class.php");
		
		$oModuleModel = getModel("module");
		$config = $oModuleModel->getModulePartConfig("youtube", $this->module_info->module_srl);
		Context::set("config", $config);
		
		//Template
		$tplPath = sprintf("%sskins/%s/", $this->module_path, (!is_null($this->module_info->skin) && $this->module_info->skin!="" && is_dir(sprintf("%sskins/%s/", $this->module_path, $this->module_info->skin))) ? $this->module_info->skin : "default");
		$this->setTemplatePath($tplPath);
		$tplFile = strtolower(str_replace("dispYoutube", "", $this->act));
		$this->setTemplateFile($tplFile);
	}
	
	//Called
	function dispYoutubeBrowse() {
		//Part: Browse
		//Grant
		if(!$this->grant->browse) {
			return new Object(-1, "msg_not_permitted");
		}
		
		$apiKey = $this->module_info->api_key;
		$oYoutubeModel = getModel("youtube");
		
		//Playlist Default Value Setting
		if(empty($this->module_info->playlist_id)) {
			//실시간 인기 동영상 - 한국
			$playlistId = "PLmtapKaZsgZt3g_uAPJbsMWdkVsznn_2R";
		} else {
			if(is_array($temp = json_decode($this->module_info->playlist_id, true))) {
				$category = Context::get("category");
				if(is_null($category) || !array_key_exists($category, $temp))
					$category = 0;
				if(empty($temp[$category]))
					$playlistId = "PLmtapKaZsgZt3g_uAPJbsMWdkVsznn_2R";
				else
					$playlistId = $oYoutubeModel->getRealPlaylistId($apiKey, $temp[$category]);
				unset($temp);
				$categoryName = json_decode($this->module_info->category_name, true);
				Context::set("categoryName", $categoryName);
				Context::set("category", $category);
			} else {
				$playlistId = $oYoutubeModel->getRealPlaylistId($apiKey, $this->module_info->playlist_id);
			}
			if($playlistId===false)
				$this->dispYoutubeShowError("PLAYLISTID_IS_NOT_VALID");
		}
		
		//Page
		$nowPage = Context::get("page");
		$videoId = Context::get("video_id");
		$no = Context::get("no");
		$videosPerPage = is_numeric($this->module_info->list_count) ? $this->module_info->list_count : 20;
		if(!isset($nowPage)) {
			if(isset($no)) {
				$videoPosition = $no;
			} else if(isset($videoId)){
				$videoPosition = $oYoutubeModel->getPosition($apiKey, $playlistId, $videoId);
			}
			$nowPage = is_numeric($videoPosition) ? ceil($videoPosition/$videosPerPage) : 1;
		}
		$pages = is_null($this->module_info->page_count) ? 9 : $this->module_info->page_count;
		if($this->module_info->inverse_order=="Y") {
			$asc = false;
			$reverse = true; //legacy
		}
		
		//updateCache
		$oYoutubeController = getController("youtube");
		$cacheTime = is_numeric($this->module_info->cache_time) ? ($this->module_info->cache_time-0) : 10;
		$updater = $oYoutubeController->procYoutubeUpdateCache($this->module_info->api_key, $playlistId, $cacheTime, $videosPerPage, $nowPage, $asc);
		$totalVideos = $updater->totalVideos;
		//getCacheData
		$temp = $oYoutubeModel->getCaches($playlistId, $videosPerPage, $nowPage, $reverse);
		foreach($temp as $key=>$val) {
			$videos[] = json_decode($val->item, true);
		}
		
		$totalPages = ceil($totalVideos/$videosPerPage);
		
		if($videos!==false || !is_array($videos)) {
			$nowPage = min($nowPage, $totalPages);
			
			foreach($videos as $key=>$val) {
				if($this->module_info->using_video_id=="Y")
					$videos[$key][url] = getNotEncodedUrl("", "mid", $this->mid, "page", $nowPage, "category", $category, "video_id", $val[snippet][resourceId][videoId]);
				else
					$videos[$key][url] = getNotEncodedUrl("", "mid", $this->mid, "category", $category, "no", ($nowPage-1)*$videosPerPage+$key+1);
				$videos[$key][channelUrl] = "//www.youtube.com/channel/".$val[snippet][channelId];
			}
			
			//StartPage
			$startPage = max(min($nowPage-(ceil($pages/2)-1), $totalPages-($pages-1)), 1);
			$endPage = min(max($nowPage+floor($pages/2), $pages), $totalPages);
			
			Context::set("nowPage", $nowPage);
			Context::set("startPage", $startPage);
			Context::set("endPage", $endPage);
			Context::set("totalPages", $totalPages);
			Context::set("totalVideos", $totalVideos);
			Context::set("videos", $videos);
			Context::set("updater", $updater);
			
			//Part: Peruse
			if(is_numeric($videoPosition)) {
				$video = $videos[($videoPosition-1)%$videosPerPage];
				$videoId = $video[snippet][resourceId][videoId];
			} else if(isset($videoId)) {
				//Precise Video Info
				$video = $oYoutubeModel->getVideo($apiKey, $videoId);
			}
			if(isset($videoId) || isset($no)) {
				$video[url] = getNotEncodedUrl("", "mid", $this->mid, "video_id", $videoId);
				$video[fullUrl] = $_SERVER["HTTP_HOST"].$video[url];
				$video[channelUrl] = "//www.youtube.com/channel/".$video[snippet][channelId];
				Context::addBrowserTitle($video[snippet][title]);
				Context::set("videoId", $videoId);
				Context::set("video", $video);
				//Player Width
				if(is_null($this->module_info->player_width)) {
					$playerSize[width] = 640;
				} else {
					$playerSize[width] = $this->module_info->player_width;
				}
				//Player Height
				if(is_null($this->module_info->player_height)) {
					if(is_null($this->module_info->player_width)) {
						$playerSize[height] = 480;
					} else {
						$playerSize[height] = $playerSize[width]/4*3;
					}
				} else {
					$playerSize[height] = $this->module_info->player_height;
				}
				Context::set("videoSize", $playerSize);
			}
		} else {
			$this->dispYoutubeShowError("CACHE_IS_NOT_AVAILABLE");
		}
	}
	
	private function dispYoutubeShowError($message) {
		$plainMessage = Context::getLang($message);
		if(!$plainMessage) $plainMessage = $message;
		Context::set("error_msg", $plainMessage);
		$this->setTemplateFile("error");
		exit;
	}
}
?>