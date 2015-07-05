<?php
//Copyright (c) 2014 Studio2b
//xFacility2014
//xFYoutube
//Studio2b(www.studio2b.kr)
//Michael Son(mson0129@gmail.com)
//02DEC2014(1.0.0.) - Newly added.
//07JUN2015(1.1.0.) - It's ported for XpressEngine. And getPlaylistItems is added.
//08JUN2015(1.2.0.) - getPage is added.
//09JUN2015(1.2.1.) - API Costs(https://developers.google.com/youtube/v3/determine_quota_cost) are optimized.
//11JUL2015(1.3.0.) - It's support showing playlists in invert order and an error msg.
//11JUL2015(1.3.1.) - List error when a playlist get by channel id or username was corrected.
//11JUL2015(1.3.2.) - Paging error when there is a unlisted or private video in a playlist was corrected.
//19JUL2015(1.3.3.) - Paging error caused by requesting over 50 items was fixed. var $totalPage is not more available.
class XFYoutube {
	var $token;
	var $activities, $channels, $playlists, $playlistItmes;
	var $items, $totalVideos, $error;
	
	function XFYoutube($token=null, $apiKey) {
		$this->activities = new XFYoutubeActivities($token, $apiKey);
		$this->channels = new XFYoutubeChannels($token, $apiKey);
		$this->playlists = new XFYoutubePlaylists($token, $apiKey);
		$this->playlistItems = new XFYoutubePlaylistItems($token, $apiKey);
		$this->videos = new XFYoutubeVideos($token, $apiKey);
	}
	
	//For XpressEngine
	function getTotalVideos($playlistId) {
		while(true) {
			$result = $this->playlistItems->browse("id", NULL, $playlistId, 50, $result[nextPageToken]);
			$return += count($result[items]);
			if(is_null($result[nextPageToken]))
				break;
		}
		return $return;
	}
}
?>