<?php
require_once(_XE_PATH_.'modules/board/board.view.php');

class youtubeMobile extends youtubeView {
	function init() {
		//xFacility2014 - including the part of frameworks
		require_once($this->module_path."XFCurl.class.php");
		require_once($this->module_path."XFYoutube.class.php");
		require_once($this->module_path."XFYoutubeActivities.class.php");
		require_once($this->module_path."XFYoutubeChannels.class.php");
		require_once($this->module_path."XFYoutubePlaylistItems.class.php");
		require_once($this->module_path."XFYoutubePlaylists.class.php");
		require_once($this->module_path."XFYoutubeVideos.class.php");
		
		$oModuleModel = getModel("module");
		$config = $oModuleModel->getModulePartConfig("youtube", $this->module_info->module_srl);
		Context::set("config", $config);
		
		//Template
		$tplPath = sprintf("%sm.skins/%s/", $this->module_path, (!is_null($this->module_info->mskin) && $this->module_info->mskin!="" && is_dir(sprintf("%sm.skins/%s/", $this->module_path, $this->module_info->mskin))) ? $this->module_info->mskin : "default");
		$this->setTemplatePath($tplPath);
		$tplFile = strtolower(str_replace("dispYoutube", "", $this->act));
		$this->setTemplateFile($tplFile);
	}
}
?>