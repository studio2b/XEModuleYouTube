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
class XFYoutube {
	var $token;
	var $activities, $channels, $playlists, $playlistItmes;
	var $totalPages, $totalVideos, $error;
	
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
		
		//Brand New Code
		if(!is_null($playlistId)) {
			$loop=0;
			while(true) {
				$result = $this->playlistItems->browse("snippet", NULL, $playlistId, 50, $result[nextPageToken]);
				if($result===false) {
					$channel = $this->channels->browse("contentDetails", NULL, $playlistId);
					$playlistId = $channel[items][0][contentDetails][relatedPlaylists][uploads];
					$result = $this->playlistItems->browse("snippet", NULL, $playlistId, $pageItems[$i], $result[nextPageToken]);
					if($result===false) {
						$channel = $this->channels->browse("contentDetails", NULL, NULL, $playlistId);
						$playlistId = $channel[items][0][contentDetails][relatedPlaylists][uploads];
						$result = $this->playlistItems->browse("snippet", NULL, $playlistId, $pageItems[$i], $result[nextPageToken]);
						if($result===false) {
							$this->error = $this->playlistItems->error;
							$return = false;
							break;
						}
					}
				}
				
				foreach($result[items] as $key=>$val) {
					$videos[$loop*50+$key] = $val;
				}
				
				if($loop*50+count($result[items])>=($page-1)*$items && $reverse!=true) {
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
				} else if($loop*50+count($result[items])>=$result[pageInfo][totalResults]) {
					$this->error = "END_OF_LIST";
					break;
				} else {
					$loop++;
				}
			}
			
			$this->totalPages = ceil($result[pageInfo][totalResults]/$items);
			$this->totalVideos = $result[pageInfo][totalResults];
		} else {
			$this->error = "NO_PLAYLIST_ID";
			$return = false;
		}
		
		return $return;
	}
	
	/*Orig
	function getPlaylistItems($playlistId, $items=20, $page=1) {
		//Default values
		if(!is_numeric($items))
			$items = 20;
		if(!is_numeric($page))
			$page = 1;
		
		$loop = ceil($items*$page/50);
		for($i=0;$i<$loop;$i++) {
			if($i==$loop-1) {
				 if($items*$page-($loop-1)*50<$items) {
				 	$pageItems[$i] = $items;
				 	if($i>0) {
				 		$pageItems[$i-1] -= $items-($items*$page-($loop-1)*50);
				 	}
				 } else {
				 	$pageItems[$i] = $items*$page-($loop-1)*50;
				 }
			} else {
				$pageItems[$i] = 50;
			}
		}
		if(!is_null($playlistId)) {
			for($i=0; $i<$loop; $i++) {
				//$nowItems = $i==$loop-1 ? $lastPageItems : 50;
				$result = $this->playlistItems->browse("snippet", NULL, $playlistId, $pageItems[$i], $result[nextPageToken]);
				//var_dump($result);
				if($result===false) {
					//If it is not a playlist ID, it may be a channel name.
					$channel = $this->channels->browse("contentDetails", NULL, $playlistId);
					$playlistId = $channel[items][0][contentDetails][relatedPlaylists][uploads];
					$result = $this->playlistItems->browse("snippet", NULL, $playlistId, $pageItems[$i], $result[nextPageToken]);
					if($result===false) {
						$return = false;
						break;
					}
				}
			}
				
			for($j=0; $j<min($items, count($result[items])); $j++) {
				$return[] = $result[items][$pageItems[$loop-1]-$items+$j];
			}
			$return[totalPages] = ceil($result[pageInfo][totalResults]/$items);
			$return[totalVideos] = $result[pageInfo][totalResults];
		} else {
			$return = false;
		}
		
		return $return;
	}
	*/
	
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