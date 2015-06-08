<?php
//Copyright (c) 2015 Studio2b
//YouTubeModule
//YoutubeAdminView
//Studio2b(www.studio2b.kr)
//Michael Son(mson0129@gmail.com)
//07JUN2015(1.0.0.) - This module was newly created.
class youtubeAdminView extends youtube {
	function dispYouTubeAdminBrowse() {
		$this->setTemplatePath(sprintf("%stpl/", $this->module_path));
		$this->setTemplateFile("browse");
	}
}
?>