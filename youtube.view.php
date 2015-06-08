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
		$tplPath = sprintf("%sskins/%s/", $this->module_path, is_dir(sprintf("%sskins/%s/", $this->module_path, $this->module_info->skin)) ? $this->module_info->skin : "default");
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
		$videosPerPage = is_numeric($this->module_info->list_count) ? $this->module_info->list_count : 20;
		if(!isset($page)) {
			if(isset($videoId)){
				$videoPosition = $youtube->getPosition($playlistId, $videoId);
			}
			$page = is_numeric($videoPosition) ? ceil($videoPosition/$videosPerPage) : 1;
		}
		
		//Get a playlist(Videos)
		$videos = $youtube->getPlaylistItems($playlistId, $videosPerPage, $page);
		//var_dump($videos);
		if($videos!==false) {
			foreach($videos as $key=>$val) {
				$videos[$key][url] = getNotEncodedUrl("", "mid", $this->mid, "page", $page, "video_id", $val[contentDetails][videoId]);
				$videos[$key][channelUrl] = "//www.youtube.com/channel/".$val[snippet][channelId];
			}
		}
		
		//StartPage
		$startPage = max(min($page-4, $videos[totalPages]-8), 1);
		$endPage = min(max($page+4, 9), $videos[totalPages]);
		
		Context::set("nowPage", $page);
		Context::set("startPage", $startPage);
		Context::set("endPage", $endPage);
		Context::set("totalPages", $videos[totalPages]);
		Context::set("totalVideos", $videos[totalVideos]);
		unset($videos[totalPages], $videos[totalVideos]);
		Context::set("videos", $videos);
		
		//Part: Peruse
		$video = $youtube->videos->browse(NULL, NULL, $videoId);
		//var_dump($video);
		if(isset($videoId)) {
			$video[items][0][url] = getNotEncodedUrl("", "mid", $this->mid, "page", $page, "video_id", $video[items][0][id]);
			$video[items][0][fullUrl] = $_SERVER["HTTP_HOST"].$video[items][0][url];
			$video[items][0][channelUrl] = "//www.youtube.com/channel/".$video[items][0][snippet][channelId];
			Context::addBrowserTitle($video[items][0][snippet][title]);
			Context::set("videoId", $videoId);
			Context::set("video", $video[items][0]);
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
	}
}
?>