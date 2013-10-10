<?php
    /**
     * @class  upgletyleAdminView
     * @author UPGLE (admin@upgle.com)
     * @brief  upgletyle module admin view class
     **/

    class upgletyleAdminView extends upgletyle {

        /**
         * @brief Initialization
         **/
        function init() {
            $oUpgletyleModel = &getModel('upgletyle');

            $this->setTemplatePath($this->module_path."/tpl/");
            $template_path = sprintf("%stpl/",$this->module_path);
            $this->setTemplatePath($template_path);
        }

        function dispUpgletyleAdminList() {
            $vars = Context::getRequestVars();
            $oUpgletyleModel = &getModel('upgletyle');

            $page = Context::get('page');
            if(!$page) $page = 1;

            if($vars->search_target && $vars->search_keyword) {
                $args->{'s_'.$vars->search_target} = strtolower($vars->search_keyword);
            }

            $args->list_count = 20;
            $args->page = $page;
            $args->list_order = 'regdate';
            $output = $oUpgletyleModel->getUpgletyleList($args);
            if(!$output->toBool()) return $output;

            Context::set('upgletyle_list', $output->data);
            Context::set('page_navigation', $output->page_navigation);
			Context::Set('use_textyle', $oUpgletyleModel->moduleExistCheck('textyle'));

            $this->setTemplateFile('list');
        }

        function dispUpgletyleAdminTextyleList() {
			global $lang;

            $oUpgletyleModel = &getModel('upgletyle');
			$use_textyle = $oUpgletyleModel->moduleExistCheck('textyle');
			if(!$use_textyle) return new Object(-1,'msg_invalid_request');

            $vars = Context::getRequestVars();
            $oTextyleModel = &getModel('textyle');

            $page = Context::get('page');
            if(!$page) $page = 1;

            if($vars->search_target && $vars->search_keyword) {
                $args->{'s_'.$vars->search_target} = strtolower($vars->search_keyword);
            }

            $args->list_count = 20;
            $args->page = $page;
            $args->list_order = 'regdate';
            $output = $oTextyleModel->getTextyleList($args);
            if(!$output->toBool()) return $output;

            Context::set('upgletyle_list', $output->data);
            Context::set('page_navigation', $output->page_navigation);
			Context::Set('use_textyle', $use_textyle);

            $this->setTemplateFile('list');
        }
		

        function dispUpgletyleAdminInsert() {
            $oModuleModel = &getModel('module');
            $oMemberModel = &getModel('member');
			
            //set identifier type of admin
        	$memberConfig = $oMemberModel->getMemberConfig();
            foreach($memberConfig->signupForm as $item){
            	if($item->isIdentifier) $identifierName = $item->name;
            }
            Context::set('identifier',$identifierName);
            
            $module_srl = Context::get('module_srl');
            if($module_srl) {
                $oUpgletyleModel = &getModel('upgletyle');
                $upgletyle = $oUpgletyleModel->getUpgletyle($module_srl);
                Context::set('upgletyle', $upgletyle);

                $admin_list = $oModuleModel->getSiteAdmin($upgletyle->site_srl);
                $site_admin = array();
                if(is_array($admin_list)){
                    foreach($admin_list as $k => $v){
                    	if($identifierName == 'user_id')  $site_admin[] = $v->user_id;
                    	   else $site_admin[] = $v->email_address;
                    }

                    Context::set('site_admin', join(',',$site_admin));
                }
            }
            
            
            $skin_list = $oModuleModel->getSkins($this->module_path);
            Context::set('skin_list',$skin_list);

            $this->setTemplateFile('insert');
        }

        function dispUpgletyleAdminDelete() {
            if(!Context::get('module_srl')) return $this->dispUpgletyleAdminList();
            $module_srl = Context::get('module_srl');

            $oUpgletyleModel = &getModel('upgletyle');
            $oUpgletyle = $oUpgletyleModel->getUpgletyle($module_srl);
            $upgletyle_info = $oUpgletyle->getObjectVars();

            $oDocumentModel = &getModel('document');
            $document_count = $oDocumentModel->getDocumentCount($upgletyle_info->module_srl);
            $upgletyle_info->document_count = $document_count;

            Context::set('upgletyle_info',$upgletyle_info);

            $this->setTemplateFile('upgletyle_delete');
        }


        function dispUpgletyleAdminSwitch() {
            if(!Context::get('module_srl')) return $this->dispUpgletyleAdminList();
            $module_srl = Context::get('module_srl');

			//get module info
			$oModuleModel = &getModel('module');
			$columnList = array('module_srl', 'module');
			$module_info = $oModuleModel->getModuleInfoByModuleSrl($module_srl, $columnList);

			if($module_info->module == 'upgletyle') {
				$oUpgletyleModel = &getModel('upgletyle');
				$oUpgletyle = $oUpgletyleModel->getUpgletyle($module_srl);
				$upgletyle_info = $oUpgletyle->getObjectVars();
			}
			elseif($module_info->module == 'textyle') {
				$oTextyleModel = &getModel('textyle');
				$oTextyle = $oTextyleModel->getTextyle($module_srl);
				$upgletyle_info = $oTextyle->getObjectVars();
				$upgletyle_info->upgletyle_title = $upgletyle_info->textyle_title;
			}

            $oDocumentModel = &getModel('document');
            $document_count = $oDocumentModel->getDocumentCount($upgletyle_info->module_srl);
            $upgletyle_info->document_count = $document_count;

            Context::set('upgletyle_info',$upgletyle_info);

            $this->setTemplateFile('upgletyle_switch');
        }


        function dispUpgletyleAdminCustomMenu() {
            $oUpgletyleModel = &getModel('upgletyle');
            $custom_menu = $oUpgletyleModel->getUpgletyleCustomMenu();
            Context::set('custom_menu', $custom_menu);

            $this->setTemplateFile('upgletyle_custom_menu');
        }

        function dispUpgletyleAdminBlogApiConfig(){
            $textyle_blogapi_services_srl = Context::get('textyle_blogapi_services_srl');

            $oUpgletyleModel = &getModel('upgletyle');
            $output = $oUpgletyleModel->getBlogApiService();
            if($output->toBool() && $output->data){
                if($textyle_blogapi_services_srl){
                    foreach($output->data as $k => $v){
                        if($v->textyle_blogapi_services_srl == $textyle_blogapi_services_srl){
                            Context::set('service',$v);
                        }
                    }
                }else{
                    Context::set('blogapi_services_list',$output->data);
                }
            }
            $this->setTemplateFile('upgletyle_blogapi_config');
        }

        function dispUpgletyleAdminExportList(){
			$args->page = Context::get('page');
			$output = executeQueryArray('upgletyle.getExportList',$args);			
			Context::set('export_list',$output->data);
			Context::set('page_navigation',$output->page_navigation);
            $this->setTemplateFile('upgletyle_export_list');
        }

		function dispUpgletyleAdminExtraMenu(){
            $module_srl = Context::get('module_srl');

			$oUpgletyleModel = &getModel('upgletyle');
			$config = $oUpgletyleModel->getModulePartConfig($module_srl);
			Context::set('config',$config);

            $oModuleModel = &getModel('module');
            $installed_module_list = $oModuleModel->getModulesXmlInfo();
            foreach($installed_module_list as $key => $val) {
                if($val->category != 'service') continue;
                $service_modules[] = $val;
            }
            Context::set('service_modules', $service_modules);
            $this->setTemplateFile('upgletyle_extra_menu_config');
		}
    }
?>
