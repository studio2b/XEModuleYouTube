<?php
//Copyright (c) 2015 Studio2b
//YouTubeModule
//YoutubeModel
//Studio2b(www.studio2b.kr)
//Michael Son(mson0129@gmail.com)
//07JUN2015(1.0.0.) - This file was newly created.
//19JUN2015(1.1.0.) - This module was updated for cache.
//26JUN2015(1.1.1.) - The queries are converted to XE XML Query(from low-level to high-level).
class youtubeModel extends youtube {
	public function triggerModuleListInSitemap(&$arr) {
		array_push($arr, "youtube");
	}
	
	public function setCache($id, $items) {
		if(!is_null($id)) {
			foreach($items as $key=>$val) {
				if(is_numeric($key)) {
					$args = new stdClass();
					$args->id = $id;
					$args->no = $key;
					$result = executeQuery("youtube.peruseCache", $args);
					
					$args->item = json_encode($val);
					$args->timestamp = time();
					if(empty($result->data)) {
						$return = executeQuery("youtube.insertCache", $args);
					} else {
						$return = executeQuery("youtube.updateCache", $args);
					}
					unset($args);
				}
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
			$args = new stdClass();
			$args->id = $id;
			$result = executeQuery("youtube.perusePlaylistInfo", $args);
			
			$args->totalVideos = $totalVideos;
			$args->timestamp = time();
			if(empty($result->data)) {
				$return = executeQuery("youtube.insertPlaylistInfo", $args);
			} else {
				$return = executeQuery("youtube.updatePlaylistInfo", $args);
			}
			unset($args);
		} else {
			$return = false;
		}
		return $return;
	}
	
	public function getPlaylistInfo($id) {
		$args = new stdClass();
		$args->id = $id;
		$output = executeQuery("youtube.perusePlaylistInfo", $args);
		return ($output->toBool()===false) ? false : $output->data;
	}
}
?>