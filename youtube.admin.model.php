<?php
//Copyright (c) 2015 Studio2b
//YouTubeModule
//YoutubeAdminModel
//Studio2b(www.studio2b.kr)
//Michael Son(mson0129@gmail.com)
//07JUN2015(1.0.0.) - This module was newly created.
//05JUL2015(1.1.0.) - Category is supported.
class youtubeAdminModel extends youtube {
	public function getYoutubeAdminSimpleSetup($moduleSrl, $setupUrl) {
		if (!$moduleSrl)
			return;
		//Module
		$oModuleModel = getModel("module");
		$moduleInfo = $oModuleModel->getModuleInfoByModuleSrl($moduleSrl);
		
		if(!$moduleInfo)
			return;
		if(is_array(json_decode($moduleInfo->playlist_id, true))) {
			$moduleInfo->category_name = json_decode($moduleInfo->category_name, true);
			$moduleInfo->playlist_id = json_decode($moduleInfo->playlist_id, true);
		}
		Context::set("module_info", $moduleInfo);
		//Config
		$config = $oModuleModel->getModulePartConfig("youtube", $moduleSrl);
		Context::set("config", $config);
		
		$oTemplate = TemplateHandler::getInstance();
		$html = $oTemplate->compile($this->module_path . "tpl/", "simple_setup");
		
		return $html;
	}
}
?>