<?php
//Copyright (c) 2015 Studio2b
//YouTubeModule
//YoutubeView
//Studio2b(www.studio2b.kr)
//Michael Son(mson0129@gmail.com)
//07JUN2015(1.0.0.) - This module was newly created.
class youtubeView extends youtube {
	function init() {
		$this->setTemplatePath($this->module_path."tpl");
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
		
		$tplPath = sprintf("%sskins/%s/", $this->module_path, $this->module_info->skin==NULL ? "default" : $this->module_info->skin);
		$this->setTemplatePath($tplPath);
		
		//$tplFile = str_replace("dispYouTube", "", $this->act);
		//$this->setTemplateFile($tplFile);
	}
	
	//Called
	function dispYoutubeDisplay() {
		//Part: Browse
		//Grant
		if(!$this->grant->browse) {
			return new Object(-1, "msg_not_permitted");
		}
		
		$page = Context::get("page");
		if(!isset($page))
			$page = 1;
		
		//Playlist Default Value Setting
		if(!isset($this->module_info->playlist_id)) {
			//실시간 인기 동영상 - 한국
			$playlistId = "PLmtapKaZsgZt3g_uAPJbsMWdkVsznn_2R";
		} else {
			$playlistId = $this->module_info->playlist_id;
		}
		
		$youtube = new XFYoutube(NULL, $this->module_info->api_key);
		$videos = $youtube->getPlaylistItems($playlistId, $this->module_info->items, $page);
		//var_dump($result);
		if($videos!==false) {
			foreach($videos as $key=>$val) {
				$videos[$key][url] = getNotEncodedUrl("", "mid", $this->mid, "video_id", $val[contentDetails][videoId]);
			}
		}
		Context::set("nowPage", $page);
		Context::set("totalPages", $videos[totalPages]);
		Context::set("totalVideos", $videos[totalVideos]);
		unset($videos[totalPages], $videos[totalVideos]);
		Context::set("videos", $videos);
		
		//Part: Peruse
		$videoId = Context::get("video_id");
		$video = $youtube->videos->browse(NULL, NULL, $videoId);
		//var_dump($result);
		if(isset($videoId)) {
			$video[items][0][url] = getNotEncodedUrl("", "mid", $this->mid, "video_id", $video[items][0][id]);
			$video[items][0][fullUrl] = $_SERVER["HTTP_HOST"].$video[items][0][url];
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
		
		$this->setTemplateFile("browse");
	}
}
?>