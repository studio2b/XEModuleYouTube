<?php
//Copyright (c) 2021 Studio2b
//YouTubeModule
//YoutubeAdminView
//Studio2b(studio2b.github.io)
//Michael Son(mson0129@gmail.com)
//07JUN2015(1.0.0.) - This module was newly created.
//27JUN2015(1.1.0.) - Menu(Module Instance) Setup is available.
//05JUL2015(1.2.0.) - Category is supported.
class youtubeAdminView extends youtube {
	function init() {
		$this->setTemplatePath(sprintf("%stpl/", $this->module_path));
		$tplFile = strtolower(str_replace("dispYoutubeAdmin", "", $this->act));
		$this->setTemplateFile($tplFile);
	}
	
	function dispYoutubeAdminBrowse() {
		$args = new stdClass();
		$args->order = "desc";
		$args->page = is_numeric(Context::get("page")) ? Context::get("page") : 1;
		$args->listCount = 20;
		$args->moduleCategorySrl = Context::get("menuCategory");
		
		$target = Context::get("kind")=="browser_title" ? Context::get("kind") : "mid";
		$args->$target = Context::get("keyword");
		
		$result = executeQueryArray('youtube.browseYoutubeMenu', $args);
		ModuleModel::syncModuleToSite($result->data);
		
		Context::set("board_list", $result->data);
		
		//Page
		Context::set("total_count", $result->total_count);
		Context::set("total_page", $result->total_page);
		Context::set("page", $result->page);
		Context::set("page_navigation", $result->page_navigation);
	}
	
	function dispYoutubeAdminUpdate() {
		$module_srl = Context::get('module_srl');
		if(!$module_srl && !$this->module_srl) {
			return $this->stop("msg_invalid_request");
		} else if(!$module_srl && $this->module_srl) {
			$module_srl = $this->module_srl;
			Context::set('module_srl', $module_srl);
		}
		
		$oModuleModel = getModel('module');
		if($module_srl) {
			$moduleInfo = $oModuleModel->getModuleInfoByModuleSrl($module_srl);
			if(is_array(json_decode($moduleInfo->playlist_id, true))) {
				$moduleInfo->category_name = json_decode($moduleInfo->category_name, true);
				$moduleInfo->playlist_id = json_decode($moduleInfo->playlist_id, true);
			}
			
			//var_dump($moduleInfo);
			if(!$moduleInfo) {
				Context::set('module_srl','');
				$this->act = 'list';
			} else {
				ModuleModel::syncModuleToSite($moduleInfo);
				$this->module_info = $moduleInfo;
				$this->module_info->use_status = explode('|@|', $moduleInfo->use_status);
				Context::set('module_info', $moduleInfo);
			}
		}
		
		//Layout
		$oLayoutModel = getModel('layout');
		$layouts = $oLayoutModel->getLayoutList();
		$mobileLayouts = $oLayoutModel->getLayoutList(0,"M");
		$oModuleModel = getModel('module');
		$skins = $oModuleModel->getSkins($this->module_path);
		$mobileSkins = $oModuleModel->getSkins($this->module_path, "m.skins");
		
		Context::set('layout_list', $layouts);
		Context::set('mlayout_list', $mobileLayouts);
		Context::set('skin_list',$skins);
		Context::set('mskin_list', $mobileSkins);
	}
	
	public function dispYoutubeAdminUpdateAuthority() {
		$oModuleAdminModel = getAdminModel('module');
		$authority = $oModuleAdminModel->getModuleGrantHTML($this->module_info->module_srl, $this->xml_info->grant);
		Context::set('authority', $authority);
	}
	
	public function dispYoutubeAdminDelete() {
		$module_srl = Context::get('module_srl');
		if(!$module_srl && $this->module_srl) {
			$module_srl = $this->module_srl;
			Context::set('module_srl', $module_srl);
		}
		
		$oModuleModel = getModel('module');
		if($module_srl) {
			$moduleInfo = $oModuleModel->getModuleInfoByModuleSrl($module_srl);
			if(!$moduleInfo) {
				Context::set('module_srl','');
				$this->act = 'list';
			} else {
				ModuleModel::syncModuleToSite($moduleInfo);
				$this->module_info = $moduleInfo;
				$this->module_info->use_status = explode('|@|', $moduleInfo->use_status);
				Context::set('module_info',$moduleInfo);
			}
		}
	}
}
?>