<?php
//Copyright (c) 2021 Studio2b
//YouTubeModule
//Youtube
//Studio2b(studio2b.github.io)
//Michael Son(mson0129@gmail.com)
//07JUN2015(1.0.0.) - This module was newly created.
//12AUG2021(1.0.1.) - "new Object" are changed into "new stdClass". And array keys are wrapped with double quotation marks("").
class youtube extends ModuleObject {
	private $triggers = array(
		array("name"=>"menu.getModuleListInSitemap", "module"=>"youtube", "type"=>"model", "func"=>"triggerModuleListInSitemap", "position"=>"after")
	);
	
	public function moduleInstall() {
		$oModuleController = getController("module");
		//Add triggers
		foreach($this->triggers as $trigger) {
			$oModuleController->insertTrigger($trigger["name"], $trigger["module"], $trigger["type"], $trigger["func"], $trigger["position"]);
		}
		return new stdClass();
	}
	
	public function checkUpdate() {
		$oModuleModel = getModel("module");
		foreach($this->triggers as $trigger) {
			$res = $oModuleModel->getTrigger($trigger["name"], $trigger["module"], $trigger["type"], $trigger["func"], $trigger["position"]);
			if(!$res)
				return true;
		}
		return false;
	}
	
	public function moduleUpdate() {
		$oModuleModel = getModel("module");
		$oModuleController = getController("module");
		foreach($this->triggers as $trigger) {
			$res = $res = $oModuleModel->getTrigger($trigger["name"], $trigger["module"], $trigger["type"], $trigger["func"], $trigger["position"]);
			if(!$res) {
				$oModuleController->insertTrigger($trigger["name"], $trigger["module"], $trigger["type"], $trigger["func"], $trigger["position"]);
			}
		}
		return new stdClass(0,"success_updated");
	}
	
	public function moduleUninstall() {
		$oModuleController = getController("module");
		foreach($this->triggers as $trigger) {
			$res = $oModuleModel->deleteTrigger($trigger["name"], $trigger["module"], $trigger["type"], $trigger["func"], $trigger["position"]);
		}
		return new stdClass();
	}
	
	public function recompileCache() {}
}
?>