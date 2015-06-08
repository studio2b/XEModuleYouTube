<?php
//Copyright (c) 2015 Studio2b
//YouTubeModule
//YoutubeModel
//Studio2b(www.studio2b.kr)
//Michael Son(mson0129@gmail.com)
//07JUN2015 - This file was newly created.
class youtubeModel extends youtube {
	public function triggerModuleListInSitemap(&$arr) {
		array_push($arr, "youtube");
	}	
}
?>