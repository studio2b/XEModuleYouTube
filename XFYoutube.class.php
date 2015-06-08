<?php
//Copyright (c) 2014 Studio2b
//xFacility2014
//xFYoutube
//Studio2b(www.studio2b.kr)
//Michael Son(mson0129@gmail.com)
//02DEC2014(1.0.0.) - Newly added.
//07JUN2015(1.0.0.) - Ported for XpressEngine
class XFYoutube {
	var $token;
	var $activities, $channels, $playlists, $playlistItmes;
	
	function XFYoutube($token=null, $apiKey) {
		$this->activities = new XFYoutubeActivities($token, $apiKey);
		$this->channels = new XFYoutubeChannels($token, $apiKey);
		$this->playlists = new XFYoutubePlaylists($token, $apiKey);
		$this->playlistItems = new XFYoutubePlaylistItems($token, $apiKey);
		$this->videos = new XFYoutubeVideos($token, $apiKey);
	}
	
	//For XpressEngine
	function getChannelPlaylists($categoryId=NULL, $forUsername=NULL, $id=NULL, $managedByMe=NULL) {
		if(!is_null($categoryId) || !is_null($forUsername) || !is_null($id) || !is_null($managedByMe)) {
			//Get channel Info. and a recents playlist.
			$channel = $this->channels->lists("id,contentDetails", $categoryId, $forUsername, $id, $managedByMe);
			$return[0][id] = $channel[items][0][contentDetails][relatedPlaylists][uploads];
			$return[0][title] = "[=recents]";
			$return[0][channelId] = $channel[items][0][id];
			$channelId = $channel[items][0][id];
			//Get playlists
			while(true) {
				$playlists = $this->playlists->lists(null, $channelId, null, null, 50, null, null, $pageToken);
				$pos = count($return);
				foreach($playlists[items] as $key => $value) {
					$return[$key+$pos][id] = $value[id];
					$return[$key+$pos][title] = $value[snippet][title];
					$return[$key+$pos][channelId] = $value[snippet][channelId];
				}
				if(is_null($playlists[nextPageToken])) {
					break;
				} else {
					$pageToken = $playlists[nextPageToken];
				}
			}
		} else {
			$return = false;
		}
		return $return;
	}
	
	function getPlaylistItems($playlistId, $items=20, $page=1) {
		//Default values
		if(!is_numeric($items))
			$items = 20;
		if(!is_numeric($page))
			$page = 1;
		
		if(!is_null($playlistId)) {
			for($i=0; $i<$page; $i++) {
				$result = $this->playlistItems->browse(NULL, NULL, $playlistId, $items, $result[nextPageToken]);
				if($result===false) {
					//If it is not a playlist ID, it may be a channel name. 
					$channel = $this->channels->lists("id,contentDetails", NULL, $playlistId);
					$playlistId = $channel[items][0][contentDetails][relatedPlaylists][uploads];
					$result = $this->playlistItems->browse(NULL, NULL, $playlistId, $items, $result[nextPageToken]);
					if($result===false) {
						$return = false;
						break;
					}
				}
				$return = $result[items];
				$return[totalPages] = ceil($result[pageInfo][totalResults]/$result[pageInfo][resultsPerPage]);
				$return[totalVideos] = $result[pageInfo][totalResults];
			}
		} else {
			$return = false;
		}
		return $return;
	}
}
?>