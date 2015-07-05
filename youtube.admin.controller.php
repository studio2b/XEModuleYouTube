<?php
//Copyright (c) 2015 Studio2b
//YouTubeModule
//YoutubeAdminController
//Studio2b(www.studio2b.kr)
//Michael Son(mson0129@gmail.com)
//07JUN2015(1.0.0.) - This module was newly created.
//27JUN2015(1.1.0.) - Menu(Module Instance) Setup is available.
//05JUL2015(1.2.0.) - Category is supported.
class youtubeAdminController extends youtube {
	public function procYoutubeAdminUpdateSimpleSetup() {
		//$args = $this->module_info;
		$args = Context::getRequestVars();
		$args->module = "youtube";
		if($args->cache_time==0) $args->cache_time='-0';
		
		if(is_array($args->playlist_id)) {
			$args->category_name = json_encode($args->category_name);
			$args->playlist_id = json_encode($args->playlist_id);
		}
		
		//moduleController->updateModule Default Values
		$args->skin = $this->module_info->skin;
		$args->is_skin_fix = $this->module_info->is_skin_fix;
		$args->mskin = $this->module_info->mskin;
		$args->is_mskin_fix = $this->module_info->is_mskin_fix;
		
		//fwrite($log = fopen(__DIR__."/log.txt", "a"), print_r($this, true)."\r\n".print_r($args, true));
		//fclose($log);
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
		if($args->cache_time==0) $args->cache_time='-0';
		
		//Category
		if(is_array($args->playlist_id)) {
			$args->category_name = json_encode($args->category_name);
			$args->playlist_id = json_encode($args->playlist_id);
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