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
	function getChannelPlaylists($categoryId=NULL, $forUsername=NULL, $id=NULL, $managedByMe=NULL) {
		if(!is_null($categoryId) || !is_null($forUsername) || !is_null($id) || !is_null($managedByMe)) {
			//Get channel Info. and a recents playlist.
			$channel = $this->channels->browse("id,contentDetails", $categoryId, $forUsername, $id, $managedByMe);
			$return[0][id] = $channel[items][0][contentDetails][relatedPlaylists][uploads];
			$return[0][title] = "[=recents]";
			$return[0][channelId] = $channel[items][0][id];
			$channelId = $channel[items][0][id];
			//Get playlists
			while(true) {
				$playlists = $this->playlists->browse(null, $channelId, null, null, 50, null, null, $pageToken);
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
	
	function getPlaylistItems($playlistId, $items=20, $page=1, $reverse=false) {
		//Default values
		if(!is_numeric($items))
			$items = 20;
		if(!is_numeric($page))
			$page = 1;
		
		if(!is_null($playlistId)) {
			$loop=0;
			while(true) {
				$result = $this->playlistItems->browse("snippet", NULL, $playlistId, 50, $result[nextPageToken]);
				if($result===false) {
					$channel = $this->channels->browse("contentDetails", NULL, $playlistId);
					if($channel===false || is_null($channel[items][0][contentDetails][relatedPlaylists][uploads])) {
						$channel = $this->channels->browse("contentDetails", NULL, NULL, $playlistId);
						if($channel===false || is_null($channel[items][0][contentDetails][relatedPlaylists][uploads])) {
							$this->error = $this->playlistItems->error;
							$return = false;
							break;
						} else {
							$playlistId = $channel[items][0][contentDetails][relatedPlaylists][uploads];
							$result = $this->playlistItems->browse("snippet", NULL, $playlistId, 50, $result[nextPageToken]);
						}
					} else {
						$playlistId = $channel[items][0][contentDetails][relatedPlaylists][uploads];
						$result = $this->playlistItems->browse("snippet", NULL, $playlistId, 50, $result[nextPageToken]);
					}
				}
				
				foreach($result[items] as $key=>$val) {
					$videos[$loop*50+$key] = $val;
				}
				
				if(is_null($result[nextPageToken])) {
					$result[pageInfo][totalResults] = count($videos);
					$page = min($page, ceil($result[pageInfo][totalResults]/$items));
				} else if($reverse) {
					$result[pageInfo][totalResults] = $this->getTotalVideos($playlistId);
					$page = min($page, ceil($result[pageInfo][totalResults]/$items));
				}
				
				if(($loop*50+count($result[items])>=($page)*$items || is_null($result[nextPageToken])) && $reverse!=true) {
					for($i=($page-1)*$items; $i<$page*$items; $i++) {
						if($videos[$i]==NULL) break;
						$return[] = $videos[$i];
					}
					break;
				} else if($loop*50+count($result[items])>=$result[pageInfo][totalResults]-($page-1)*$items && $reverse) {
					for($i=$result[pageInfo][totalResults]-($page-1)*$items-1; $i>$result[pageInfo][totalResults]-$page*$items-1; $i--) {
						if($videos[$i]==NULL) break;
						$return[] = $videos[$i];
					}
					break;
				} else if(is_null($result[nextPageToken])){
					$this->error = "END_OF_LIST";
					$return = false;
					break;
				} else {
					$loop++;
				}
			}
			
			$this->items = $videos;
			$this->totalVideos = $result[pageInfo][totalResults];
		} else {
			$this->error = "NO_PLAYLIST_ID";
			$return = false;
		}
		
		return $return;
	}
	
	function getTotalVideos($playlistId) {
		while(true) {
			$result = $this->playlistItems->browse("id", NULL, $playlistId, 50, $result[nextPageToken]);
			$return += count($result[items]);
			if(is_null($result[nextPageToken]))
				break;
		}
		return $return;
	}
	
	function getPosition($playlistId, $videoId) {
		if(!is_null($playlistId) && !is_null($videoId)) {
			$counter = 0;
			while(true) {
				$result = $this->playlistItems->browse("contentDetails", NULL, $playlistId, 50, $result[nextPageToken]);
				if($result===false) {
					$return = false;
					break;
				} else {
					foreach($result[items] as $val) {
						$counter++;
						if($videoId==$val[contentDetails][videoId]) {
							$return = $counter;
							break;
						}
					}
				}
				
				if(is_numeric($return)) {
					break;
				} else if(is_null($result[nextPageToken])) {
					$return = false;
					break;
				}
			}
		} else {
			$return = false;
		}
		
		return $return;
	} 
}
?>