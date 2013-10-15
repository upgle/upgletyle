<?php
	require_once(_XE_PATH_ . 'modules/upgletyle/upgletyle.view.php');

	class upgletyleMobile extends upgletyleView {

		function init() {
			parent::init();

			$oUpgletyleModel = &getModel('upgletyle');
			if((preg_match("/UpgletyleTool/",$this->act) || $oUpgletyleModel->isAttachedMenu($this->act))) {
				Context::addJsFile("./common/js/jquery.js", true, '', -100000);
				Context::addJsFile("./common/js/x.js", true, '', -100000);
				Context::addJsFile("./common/js/common.js", true, '', -100000);
				Context::addJsFile("./common/js/js_app.js", true, '', -100000);
				Context::addJsFile("./common/js/xml_handler.js", true, '', -100000);
				Context::addJsFile("./common/js/xml_js_filter.js", true, '', -100000);
				Context::addCSSFile("./common/css/default.css", true, 'all', '', -100000);
				Context::addCSSFile("./common/css/button.css", true, 'all', '', -100000);
			}else{
				$template_path = sprintf("%sm.skins/%s/",$this->module_path, $this->module_info->mskin);
				if(!is_dir($template_path)||!$this->module_info->mskin) {
					$this->module_info->mskin = 'default';
					$template_path = sprintf("%sm.skins/%s/",$this->module_path, $this->module_info->mskin);
				}
				$this->setTemplatePath($template_path);
			}
		}

        function initService(&$oModule, $is_other_module = false)
		{
			parent::initService($oModule, true, true);

			$template_path = sprintf("%sm.skins/%s/",$this->module_path, $this->module_info->mskin);
			if(!is_dir($template_path)||!$this->module_info->mskin) {
				$this->module_info->mskin = 'default';
				$template_path = sprintf("%sm.skins/%s/",$this->module_path, $this->module_info->mskin);
			}

			$oTemplateHandler = &TemplateHandler::getInstance();
			$html = $oTemplateHandler->compile($oModule->getTemplatePath(), $oModule->getTemplateFile());
			Context::set('content', $html);

			$oModule->setTemplatePath($template_path);
			$oModule->setTemplateFile('upgletyle');
		}

		function dispUpgletyle(){
            $oUpgletyleModel = &getModel('upgletyle');
            $oUpgletyleController = &getController('upgletyle');
            $oDocumentModel = &getModel('document');

            $args->category_srl = Context::get('category_srl');
            $args->search_target = Context::get('search_target');
            $args->search_keyword = Context::get('search_keyword');
			$args->module_srl = $this->module_srl;
			$args->site_srl = $this->site_srl;

            $args->page = Context::get('page');
            $args->page = $args->page>0 ? $args->page : 1;
            Context::set('page',$args->page);

            // set category
            $category_list = $oDocumentModel->getCategoryList($this->module_srl);
            Context::set('category_list', $category_list);

			$document_srl = Context::get('document_srl');

			if($document_srl) {
				$oDocument = $oDocumentModel->getDocument($document_srl,false,false);

				if($oDocument->isExists()) {
					if($oDocument->get('module_srl')!=$this->module_info->module_srl ) return $this->stop('msg_invalid_request');

					Context::setBrowserTitle($this->upgletyle->get('browser_title') . ' »  ' . $oDocument->getTitle());

					// meta keywords category + tag
					$tag_array = $oDocument->get('tag_list');
					if($tag_array) {
						$tag = htmlspecialchars(join(', ',$tag_array));
					} else {
						$tag = '';
					}
					$category_srl = $oDocument->get('category_srl');
					if($tag && $category_srl >0) $tag = $category_list[$category_srl]->title .', ' . $tag;
					Context::addHtmlHeader(sprintf('<meta name="keywords" content="%s" />',$tag));

					if($this->grant->manager) $oDocument->setGrant();

				} else {
					Context::set('document_srl','',true);
					$oDocument = $oDocumentModel->getDocument(0,false,false);
				}

			}

			if(!$document_srl){
				if($args->category_srl || ($args->search_target && $args->search_keyword)){
					$args->list_count = 10;
					$output = $oDocumentModel->getDocumentList($args, false, false);
					$document_list = $output->data;
					Context::set('page_navigation', $output->page_navigation);
					Context::set('document_list', $document_list);

					$this->setTemplateFile('list');
				}
			}


			if((!$oDocument || !$oDocument->isExists()) && !$document_list){
                $args->list_count = 1;
                $output = $oDocumentModel->getDocumentList($args, false, false);
                if($output->data && count($output->data)) $oDocument = array_pop($output->data);
			}

			if($oDocument && $oDocument->isExists()){
				$args->document_srl = $oDocument->document_srl;
				$output = executeQuery('upgletyle.getNextDocument', $args);
				if($output->data->document_srl) Context::set('prev_document', new documentItem($output->data->document_srl,false));
				$output = executeQuery('upgletyle.getPrevDocument', $args);
				if($output->data->document_srl) Context::set('next_document', new documentItem($output->data->document_srl,false));

				Context::set('oDocument', $oDocument);
				$this->setTemplateFile('read');

				Context::addJsFilter($this->module_path.'tpl/filter', 'insert_comment.xml');
			}
		}

        function dispUpgletyleProfile() {
			parent::dispUpgletyleProfile();
			$this->setTemplateFile('profile');
		}

		function dispUpgletyleCommentReply() {
			parent::dispUpgletyleCommentReply();
			$this->setTemplateFile('comment_form');
		}

		function dispUpgletylePasswordForm() {
			$type = Context::get('type');

			if($type=='delete_comment'){
				$callback_url = getUrl('','document_srl',Context::get('document_srl'
			));
			}elseif($type=='delete_guestbook'){
				$callback_url = getUrl('','act','dispUpgletyleGuestbook','mid',$this->mid);
			}
			Context::set('callback_url', $callback_url);
			$this->setTemplateFile('input_password_form');
		}

		function dispUpgletyleGuestbookWrite() {
			$upgletyle_guestbook_srl = Context::get('upgletyle_guestbook_srl');
			if($upgletyle_guestbook_srl){
				$oUpgletyleModel = &getModel('upgletyle');
				$output = $oUpgletyleModel->getUpgletyleGuestbook($upgletyle_guestbook_srl);
				$guestbook_list = $output->data;
				if(is_array($guestbook_list) && count($guestbook_list)){
					if(!$guestbook_list[0]->parent_srl) Context::set('guestbook',$guestbook_list[0]);
				}
			}

            Context::addJsFilter($this->module_path.'tpl/filter', 'insert_guestbook.xml');
			$this->setTemplateFile('guestbook_form');
		}

        function dispUpgletyleGuestbook() {
            $page = Context::get('page');
            $page = $page ? $page : 1;
            Context::set('page',$page);

            $args->module_srl = $this->module_srl;
            $args->search_text = Context::get('search_text');
            $args->page = $page;
			$args->list_count = $this->upgletyle->getGuestbookListCount();

            $oUpgletyleModel = &getModel('upgletyle');
            $output = $oUpgletyleModel->getUpgletyleGuestbookList($args);
            Context::set('guestbook_list',$output->data);
            Context::set('page_navigation', $output->page_navigation);

			$this->setTemplateFile('guestbook');
		}

		function dispUpgletyleCategory() {
            $oDocumentModel = &getModel('document');
            $category_list = $oDocumentModel->getCategoryList($this->module_srl);
            Context::set('category_list', $category_list);

			$this->setTemplateFile('category');
		}

		function getUpgletyleCommentPage() {
			$document_srl = Context::get('document_srl');
			$oDocumentModel =& getModel('document');
			if(!$document_srl) return new Object(-1, "msg_invalid_request");
			$oDocument = $oDocumentModel->getDocument($document_srl);
			if(!$oDocument->isExists()) return new Object(-1, "msg_invalid_request");
			Context::set('oDocument', $oDocument);
			$oTemplate = new TemplateHandler;
			$html = $oTemplate->compile($this->getTemplatePath(), "comment.html");
			$this->add("html", $html);
		}

		function dispUpgletylePostWrite(){
            $oDocumentModel = &getModel('document');

			$document_srl = Context::get('document_srl');
			if($document_srl){
				$oDocument = $oDocumentModel->getDocument($document_srl,false,false);
			}
			if(!$oDocument || $oDocument->isExists()){
				$oDocument = $oDocumentModel->getDocument(0,false,false);
			}

			Context::set('oDocument',$oDocument);

            $category_list = $oDocumentModel->getCategoryList($this->module_srl);
            Context::set('category_list', $category_list);

			$this->setTemplateFile('post_form');
            Context::addJsFilter($this->template_path.'filter', 'insert_mpost.xml');
		}

		function dispUpgletyleMenu(){
			$menu = array();
            $menu['Home'] = getFullSiteUrl($this->upgletyle->domain);
			$menu['Profile'] = getSiteUrl($this->upgletyle->domain,'','mid',$this->module_info->mid,'act','dispUpgletyleProfile');
            $menu['Guestbook'] = getSiteUrl($this->upgletyle->domain,'','mid',$this->module_info->mid,'act','dispUpgletyleGuestbook');
            //$menu['Tags'] = getSiteUrl($this->upgletyle->domain,'','mid',$this->module_info->mid,'act','dispUpgletyleTag');

			$args->site_srl = $this->site_srl;
			$output = executeQueryArray('upgletyle.getExtraMenus',$args);
			if($output->toBool() && $output->data){
				foreach($output->data as $v){
					$menu[$v->name] = getUrl('','mid',$v->mid);
				}
			}

			$logged_info = Context::get('logged_info');
			if($logged_info->is_site_admin) $menu['Write Post'] = getSiteUrl($this->upgletyle->domain,'','mid',$this->module_info->mid,'act','dispUpgletylePostWrite');
			Context::set('menu',$menu);
			$this->setTemplateFile('menu');
		}

		function procUpgletylePostWrite(){
			$logged_info = Context::get('logged_info');
			if(!$logged_info->is_site_admin) return new Object(-1,'msg_invalid_request');

			$args = Context::getRequestVars();
			$args->content = $args->content_text;
			$args->module_srl = $this->module_srl;
			$oUpgletyleController = &getController('upgletyle');
			$output = $oUpgletyleController->insertPost($args);
			return $output;
		}
	}


?>
