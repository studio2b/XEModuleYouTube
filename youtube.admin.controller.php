<?php
//Copyright (c) 2015 Studio2b
//YouTubeModule
//YoutubeAdminController
//Studio2b(www.studio2b.kr)
//Michael Son(mson0129@gmail.com)
//07JUN2015(1.0.0.) - This module was newly created.
class youtubeAdminController extends youtube {
	public function procYoutubeAdminUpdateSimpleSetup() {
		$args = Context::getRequestVars();
		$args->module = "youtube";
		$oModuleController = getController("module");
		$output = $oModuleController->updateModule($args);
	}
}
?>