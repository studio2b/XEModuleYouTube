<?php
//Copyright (c) 2015 Studio2b
//YouTubeModule
//YoutubeAdminModel
//Studio2b(www.studio2b.kr)
//Michael Son(mson0129@gmail.com)
//07JUN2015(1.0.0.) - This module was newly created.
class youtubeAdminModel extends youtube {
	public function getYoutubeAdminSimpleSetup($moduleSrl, $setupUrl)
	{
		if (!$moduleSrl)
		{
			return;
		}

		$oModuleModel = getModel("module");
		$moduleInfo = $oModuleModel->getModuleInfoByModuleSrl($moduleSrl);
		if (!$moduleInfo)
		{
			return;
		}

		Context::set("module_info", $moduleInfo);

		$config = $oModuleModel->getModulePartConfig("youtube", $moduleSrl);
		Context::set("config", $config);

		$oTemplate = TemplateHandler::getInstance();
		$html = $oTemplate->compile($this->module_path . "tpl/", "simple_setup");

		return $html;
	}
}
?>