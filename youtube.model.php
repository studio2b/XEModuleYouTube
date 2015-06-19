<?php
//Copyright (c) 2015 Studio2b
//YouTubeModule
//YoutubeModel
//Studio2b(www.studio2b.kr)
//Michael Son(mson0129@gmail.com)
//07JUN2015(1.0.0.) - This file was newly created.
//19JUN2015(1.1.0.) - This module was updated for cache.
class youtubeModel extends youtube {
	public function triggerModuleListInSitemap(&$arr) {
		array_push($arr, "youtube");
	}
	
	public function setCache($id, $items) {
		if(!is_null($id)) {
			$temp = new DB();
			if(key($temp->priority_dbms)!="mssql") {
				$className = "DB".ucfirst(key($temp->priority_dbms));
				$db = new $className();
				foreach($items as $key=>$val) {
					if(is_numeric($key)) {
						$query = sprintf("INSERT INTO `xe_youtube_video` (`id`, `no`, `item`, `timestamp`) VALUES ('%s', '%s', '%s', '%s') ON DUPLICATE KEY UPDATE `item`='%s', `timestamp`='%s'", $id, $key, addslashes(json_encode($val)), time(), addslashes(json_encode($val)), time());
						$result = $db->_query($query);
					}
				}
			} else {
				$return = false;
			}
		} else {
			$return = false;
		}
		return false;
	}
	
	public function getCacheTimestamp($id, $items=20, $page=1) {
		$args = new stdClass();
		$args->id = $id;
		$args->start = $items*($page-1);
		$args->end = $items*$page;
		$args->items = $items;
		$output = executeQuery("youtube.getCacheTimestamp", $args);
		if($output->toBool()===false)
			$return = false;
		else
			$return = $output->data;
		return $return;
	}
	
	public function getCache($id, $items=20, $page=1, $reverse=false) {
		$args = new stdClass();
		$args->id = $id;
		$args->index = "no";
		$args->order = $reverse==true ? "desc":"asc";
		$args->listCount = $items;
		$args->page = $page;
		$output = executeQuery("youtube.getCache", $args);
		return ($output->toBool()===false) ? false : $output->data;;
	}
	
	public function setPlaylistInfo($id, $totalVideos) {
		if(!is_null($id)) {
			$temp = new DB();
			if(key($temp->priority_dbms)!="mssql") {
				$className = "DB".ucfirst(key($temp->priority_dbms));
				$db = new $className();
				$query = sprintf("INSERT INTO `xe_youtube_playlist` (`id`, `total_videos`,`timestamp`) VALUES ('%s', '%s', '%s') ON DUPLICATE KEY UPDATE `total_videos`='%s', `timestamp`='%s'", $id, $totalVideos, time(), $totalVideos, time());
				$return = $db->_query($query);
			} else {
				$return = false;
			}
		} else {
			$return = false;
		}
		return $return;
	}
	
	public function getPlaylistInfo($id) {
		$args = new stdClass();
		$args->id = $id;
		$output = executeQuery("youtube.getPlaylistInfo", $args);
		return ($output->toBool()===false) ? false : $output->data;
	}
}
?>