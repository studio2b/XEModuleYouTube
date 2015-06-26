<?php
//Copyright (c) 2015 Studio2b
//YouTubeModule
//YoutubeAdminController
//Studio2b(www.studio2b.kr)
//Michael Son(mson0129@gmail.com)
//07JUN2015(1.0.0.) - This module was newly created.
//27JUN2015(1.1.0.) - Menu(Module Instance) Setup is available.
class youtubeAdminController extends youtube {
	public function procYoutubeAdminUpdateSimpleSetup() {
		$args = Context::getRequestVars();
		$args->module = "youtube";
		$oModuleController = getController("module");
		$output = $oModuleController->updateModule($args);
	}
	
	function procYoutubeAdminUpdate() {
		$args = Context::getRequestVars();
		$args->module = "youtube";
		if($args->mskin=="0") {
			$args->use_mobile = "N";
			unset($args->mskin);
		} else {
			$args->use_mobile = "Y";
		}
		$oModuleController = getController("module");
		$output = $oModuleController->updateModule($args);
		$this->setRedirectUrl(getNotEncodedUrl('', 'module', 'admin', 'act', 'dispYoutubeAdminUpdate', 'module_srl', $output->get('module_srl')));
	}
	
	function procYoutubeAdminDelete() {
		$module_srl = Context::get('module_srl');
		// get the current module
		$oModuleController = getController('module');
		$output = $oModuleController->deleteModule($module_srl);
		if(!$output->toBool()) return $output;
		$this->setRedirectUrl(getNotEncodedUrl('', 'module', 'admin', 'act', 'dispYoutubeAdminBrowse'));
	}
}
?>