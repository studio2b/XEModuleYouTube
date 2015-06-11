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
		$tplPath = sprintf("%sskins/%s/", $this->module_path, (!is_null($this->module_info->skin) && $this->module_info->skin=="" && is_dir(sprintf("%sskins/%s/", $this->module_path, $this->module_info->skin))) ? $this->module_info->skin : "default");
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
		
		$youtube = new XFYoutube(NULL, $this->module_info->api_key);
		
		//Playlist Default Value Setting
		if(!isset($this->module_info->playlist_id)) {
			//실시간 인기 동영상 - 한국
			$playlistId = "PLmtapKaZsgZt3g_uAPJbsMWdkVsznn_2R";
		} else {
			$playlistId = $this->module_info->playlist_id;
		}
		
		//Page
		$page = Context::get("page");
		$videoId = Context::get("video_id");
		$no = Context::get("no");
		$videosPerPage = is_numeric($this->module_info->list_count) ? $this->module_info->list_count : 20;
		if(!isset($page)) {
			if(isset($no)) {
				$videoPosition = $no;
			} else if(isset($videoId)){
				$videoPosition = $youtube->getPosition($playlistId, $videoId);
			}
			$page = is_numeric($videoPosition) ? ceil($videoPosition/$videosPerPage) : 1;
		}
		
		if($this->module_info->inverse_order=="Y")
			$reverse = true;
		
		//Get a playlist(Videos)
		$videos = $youtube->getPlaylistItems($playlistId, $videosPerPage, $page, $reverse);
		//var_dump($youtube->error);
		//var_dump($videos);
		if($videos!==false) {
			$page = min($page, $youtube->totalPages);
			
			foreach($videos as $key=>$val) {
				if($this->module_info->using_video_id=="Y")
					$videos[$key][url] = getNotEncodedUrl("", "mid", $this->mid, "page", $page, "video_id", $val[snippet][resourceId][videoId]);
				else
					$videos[$key][url] = getNotEncodedUrl("", "mid", $this->mid, "no", ($page-1)*$videosPerPage+$key+1);
				$videos[$key][channelUrl] = "//www.youtube.com/channel/".$val[snippet][channelId];
			}
			
			//StartPage
			$startPage = max(min($page-4, $youtube->totalPages-8), 1);
			$endPage = min(max($page+4, 9), $youtube->totalPages);
			
			Context::set("nowPage", $page);
			Context::set("startPage", $startPage);
			Context::set("endPage", $endPage);
			Context::set("totalPages", $youtube->totalPages);
			Context::set("totalVideos", $youtube->totalVideos);
			Context::set("videos", $videos);
			
			//Part: Peruse
			if(is_numeric($videoPosition)) {
				$video = $videos[($videoPosition-1)%$videosPerPage];
				$videoId = $video[snippet][resourceId][videoId];
			} else if(isset($videoId)) {
				$temp = $youtube->videos->browse("snippet", NULL, $videoId);
				$video = $temp[items][0];
			}
			//var_dump($video);
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
			//ERROR MSG
			$msg = Context::getLang($youtube->error);
			if(!$msg) $msg = $youtube->error;
			Context::set("error_msg", $msg);
			$this->setTemplateFile("error");
		}
	}
}
?>