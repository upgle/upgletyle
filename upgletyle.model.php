<?php
    /**
     * @class  upgletyleModel
     * @author NHN (developers@xpressengine.com)
     * @brief  upgletyle module Model class
     **/

    class upgletyleModel extends upgletyle {

        /**
         * @brief Initialization
         **/
        function init() {
        }

        /**
         * @brief get upgletyle custom menu
         **/
        function getUpgletyleCustomMenu() {
            static $custom_menu = null;

            if(is_null($custom_menu)) {
                $oModuleModel = &getModel('module');
                $config = $oModuleModel->getModuleConfig('upgletyle');
                $custom_menu->hidden_menu = $config->hidden_menu;
                if(!$custom_menu->hidden_menu) $custom_menu->hidden_menu = array();
                $custom_menu->attached_menu = $config->attached_menu;
                if(!$custom_menu->attached_menu) $custom_menu->attached_menu = array();
            }

            $output = ModuleHandler::triggerCall('upgletyle.getUpgletyleCustomMenu', 'after', $custom_menu);
            if(!$output->toBool()) return $output;

            return $custom_menu;
        }

        function isHiddenMenu($act) {
            $custom_menu = $this->getUpgletyleCustomMenu();
            if(!count($custom_menu->hidden_menu)) return false;

            return in_array(strtolower($act), $custom_menu->hidden_menu)?true:false;
        }

        function isAttachedMenu($act) {
            $custom_menu = $this->getUpgletyleCustomMenu();
            if(!count($custom_menu->attached_menu)) return false;

            foreach($custom_menu->attached_menu as $key => $val) {
                if(!count($val)) continue;
                foreach($val as $k => $v) {
                    if(strtolower($k) == strtolower($act)) return true;
                }
            }
        }

        /**
         * @brief get member upgletyle
         **/
        function getMemberUpgletyle($member_srl = 0) {
            if(!$member_srl && !Context::get('is_logged')) return new UpgletyleInfo();

            if(!$member_srl) {
                $logged_info = Context::get('logged_info');
                $args->member_srl = $logged_info->member_srl;
            } else {
                $args->member_srl = $member_srl;
            }

            $output = executeQueryArray('upgletyle.getMemberUpgletyle', $args);
            if(!$output->toBool() || !$output->data) return new UpgletyleInfo();

            $upgletyle = $output->data[0];

            $oUpgletyle = new UpgletyleInfo();
            $oUpgletyle->setAttribute($upgletyle);

            return $oUpgletyle;
        }

        /**
         * @brief Upgletyle return list
         **/
        function getUpgletyleList($args) {
            $output = executeQueryArray('upgletyle.getUpgletyleList', $args);
            if(!$output->toBool()) return $output;

            if(count($output->data)) {
                foreach($output->data as $key => $val) {
                    $oUpgletyle = null;
                    $oUpgletyle = new UpgletyleInfo();
                    $oUpgletyle->setAttribute($val);
                    $output->data[$key] = null;
                    $output->data[$key] = $oUpgletyle;
                }
            }
            return $output;
        }

        /**
         * @brief Upgletyle return
         **/
        function getUpgletyle($module_srl=0) {
            static $upgletyles = array();
            if(!isset($upgletyles[$module_srl])) $upgletyles[$module_srl] = new UpgletyleInfo($module_srl);
            return $upgletyles[$module_srl];
        }

        /**
         * @brief publishObject load
         **/
        function getPublishObject($module_srl, $document_srl = 0) {
            static $objects = array();

            require_once($this->module_path.'libs/publishObject.class.php');

            if(!isset($objects[$document_srl])) $objects[$document_srl] = new publishObject($module_srl, $document_srl);

            return $objects[$document_srl];
        }

        /**
         * @brief return upgletyle count
         **/
        function getUpgletyleCount($member_srl = null) {
            if(!$member_srl) {
                $logged_info = Context::get('logged_info');
                $member_srl = $logged_info->member_srl;
            }
            if(!$member_srl) return null;

            $args->member_srl = $member_srl;
            $output = executeQuery('upgletyle.getUpgletyleCount',$args);

            return $output->data->count;
        }

        function getUpgletyleGuestbookList($vars){
            $oMemberModel = &getModel('member');
            $oUpgletyleController = &getController('upgletyle');
            $logged_info = Context::get('logged_info');

            $args->module_srl = $vars->module_srl;
            $args->page = $vars->page;
            $args->list_count = $vars->list_count;
            if($vars->search_keyword) $args->content_search = $vars->search_keyword;
            $output = executeQueryArray('upgletyle.getUpgletyleGuestbookList',$args);
            if(!$output->toBool() || !$output->data) return array();

            foreach($output->data as $key => $val) {
                if($logged_info->is_site_admin || $val->is_secret!=1 || $val->member_srl == $logged_info->member_srl || $val->view_grant || $_SESSION['own_textyle_guestbook'][$val->textyle_guestbook_srl]){
                    $val->view_grant = true;
                    $oUpgletyleController->addGuestbookGrant($val->textyle_guestbook_srl);

                    foreach($output->data as $k => $v) {
                        if($v->parent_srl == $val->textyle_guestbook_srl){
                            $v->view_grant=true;
                        }
                    }
                }else{
                    $val->view_grant = false;
                }

                $profile_info = $oMemberModel->getProfileImage($val->member_srl);
                if($profile_info) $output->data[$key]->profile_image = $profile_info->src;
            }

            return $output;
        }

        function getUpgletyleGuestbook($textyle_guestbook_srl){
            $oMemberModel = &getModel('member');

            $args->textyle_guestbook_srl = $textyle_guestbook_srl;
            $output = executeQueryArray('upgletyle.getUpgletyleGuestbook',$args);
            if($output->data){
                foreach($output->data as $key => $val) {
                    if(!$val->member_srl) continue;
                    $profile_info = $oMemberModel->getProfileImage($val->member_srl);
                    if($profile_info) $output->data[$key]->profile_image = $profile_info->src;
                }
            }

            return $output;
        }

        function getDenyCacheFile($module_srl){
            return sprintf("./files/cache/upgletyle/textyle_deny/%d.php",$module_srl);
        }

        function getUpgletyleDenyList($module_srl){
            $args->module_srl = $module_srl;
            $cache_file = $this->getDenyCacheFile($module_srl);

            if($GlOBALS['XE_TEXTYLE_DENY_LIST'] && is_array($GLOBALS['XE_TEXTYLE_DENY_LIST'])){
                return $GLOBALS['XE_TEXTYLE_DENY_LIST'];
            }

            if(!file_exists(FileHandler::getRealPath($cache_file))) {
                $_textyle_deny = array();
                $buff = '<?php if(!defined("__ZBXE__")) exit(); $_textyle_deny=array();';
                $output = executeQueryArray('upgletyle.getUpgletyleDeny',$args);
                if(count($output->data) > 0){
                    foreach($output->data as $k => $v){
                        $_textyle_deny[$v->deny_type][$v->textyle_deny_srl] = $v->deny_content;
                        $buff .= sprintf('$_textyle_deny[\'%s\'][%d]="%s";',$v->deny_type,$v->textyle_deny_srl,$v->deny_content);
                    }
                }
                $buff .= '?>';

                if(!is_dir(dirname($cache_file))) FileHandler::makeDir(dirname($cache_file));
                FileHandler::writeFile($cache_file, $buff);
            }else{
                @include($cache_file);
            }
            $GLOBALS['XE_TEXTYLE_DENY_LIST'] = $_textyle_deny;

            return $GLOBALS['XE_TEXTYLE_DENY_LIST'];
        }

        function _checkDeny($module_srl,$type,$deny_content){
            $deny_content = trim($deny_content);
            if(strlen($deny_content) == 0) return false;

            $deny_list = $this->getUpgletyleDenyList($module_srl);

            if(!is_array($deny_list)) return false;
            if(!is_array($deny_list[$type])) return false;
            if(count($deny_list[$type])==0) return false;
            if(!in_array($deny_content,$deny_list[$type])) return false;

            return true;
        }

        function checkDenyIP($module_srl,$ip){
            $ip = trim($ip);
            if(!$ip) return false;

            return $this->_checkDeny($module_srl,'I',$ip);
        }

        function checkDenyEmail($module_srl,$email){
            $email = trim($email);
            if(!$email) return false;

            return $this->_checkDeny($module_srl,'M',$email);
        }

        function checkDenyUserName($module_srl,$user_name){
            $user_name = trim($user_name);
            if(!$user_name) return false;
            if(is_array($user_name)){
                foreach($user_name as $k => $v){
                    if(!$this->_checkDeny($module_srl,'N',$v)) return false;
                }
                return true;
            }else{
                return $this->_checkDeny($module_srl,'N',$user_name);
            }
        }

        function checkDenySite($module_srl,$site){
            $site = trim($site);
            if(!$site) return false;

            return $this->_checkDeny($module_srl,'S',$site);
        }

        function getSubscription($args){
            $output = executeQueryArray('upgletyle.getUpgletyleSubscription', $args);
            //$output->add('date',$publish_date);

            return $output;
        }

        function getSubscriptionMinPublishDate($module_srl){
            $args->module_srl = $module_srl;
            $output = executeQuery('upgletyle.getUpgletyleSubscriptionMinPublishDate', $args);

            return $output;
        }

        function getSubscriptionByDocumentSrl($document_srl){
            $args->document_srl = $document_srl;
            $output = executeQueryArray('upgletyle.getUpgletyleSubscriptionByDocumentSrl',$args);

            return $output;
        }

        /**
         * @brief get upgletyle photo source
         **/
        function getUpgletylePhotoSrc($member_srl) {
            $oMemberModel = &getModel('member');
            $info = $oMemberModel->getProfileImage($member_srl);
            $filename = $info->file;

            if(!file_exists($filename)) return $this->getUpgletyleDefaultPhotoSrc();
            return $info->src;
        }

        function getUpgletyleDefaultPhotoSrc(){
            return sprintf("%s%s%s", Context::getRequestUri(), $this->module_path, 'tpl/img/iconNoProfile.gif');
        }

        function getUpgletyleFaviconPath($module_srl) {
            return sprintf('files/attach/upgletyle/favicon/%s', getNumberingPath($module_srl,3));
        }

        function getUpgletyleFaviconSrc($module_srl) {
            $path = $this->getUpgletyleFaviconPath($module_srl);
            $filename = sprintf('%sfavicon.ico', $path);
            if(!is_dir($path) || !file_exists($filename)) return $this->getUpgletyleDefaultFaviconSrc();

            return Context::getRequestUri().$filename."?rnd=".filemtime($filename);
        }

        function getUpgletyleDefaultFaviconSrc(){
            return sprintf("%s%s", Context::getRequestUri(), 'modules/upgletyle/tpl/img/favicon.ico');
        }

        function getUpgletyleSupporterList($module_srl,$YYYYMM="",$sort_index="total_count"){
            $oMemberModel = &getModel('member');
            $oModuleModel = &getModel('module');

            $module_info = $oModuleModel->getModuleInfoByModuleSrl($module_srl);
            $site_admin_list = $oModuleModel->getSiteAdmin($module_info->site_srl);
            $site_admin_srls = array();
			if($site_admin_list){
				foreach($site_admin_list as $k => $v){
					$site_admin_srls[] = $v->member_srl;
				}
			}

            $args->module_srl = $module_srl;
            $args->sort_index = $sort_index;
            $args->list_count = $list_count;
            $args->page = $page;
            $args->regdate = $YYYYMM ? $YYYYMM : date('Ym');
            $output = executeQueryArray('upgletyle.getUpgletyleSupporterList', $args);

            $_data = array();
            if($output->data) {
                 foreach($output->data as $key => $val) {
                      if(in_array($val->member_srl,$site_admin_srls)) continue;

                      $_data[$key] = $val;
                      if($val->member_srl<1) continue;
                      $img = $oMemberModel->getProfileImage($val->member_srl);
                      if($img) $_data[$key]->profile_image = $img->src;
                 }
            }
            $output->data = $_data;
            return $output;
        }
        function getUpgletylePath($module_srl) {
            return sprintf("./files/attach/upgletyle/%s",getNumberingPath($module_srl));
        }

        function checkUpgletylePath($module_srl, $skin = null) {
            $path = $this->getUpgletylePath($module_srl);
            if(!file_exists($path)){
                $oUpgletyleController = &getController('upgletyle');
                $oUpgletyleController->resetSkin($module_srl, $skin);
            }
            return true;
        }

        function getUpgletyleUserSkinFileList($module_srl){
            $skin_path = $this->getUpgletylePath($module_srl);
            $skin_file_list = FileHandler::readDir($skin_path,'/(\.html|\.htm|\.css)$/');
            return $skin_file_list;
        }

        function getUpgletyleAPITest() {
            $oUpgletyleModel = &getModel('upgletyle');
            $oUpgletyleController = &getController('upgletyle');
            $oPublish = $oUpgletyleModel->getPublishObject($this->module_srl);

            $var = Context::getRequestVars();
            $output = $oPublish->getBlogAPIInfo($var->blogapi_type, $var->blogapi_url, $var->blogapi_user_id, $var->blogapi_password, $var->blogapi_blogid);
            if(!$output->toBool()) return $output;
            $url = $output->get('url');
            if(!$url) $this->setMessage('not_permit_blogapi');

            $this->add('site_url', $url);
            $this->add('title', $output->get('name'));
        }

        function getTrackbackUrl($domain,$document_srl){
            $oTrackbackModel = &getModel('trackback');
            $key = $oTrackbackModel->getTrackbackKey($document_srl);

            return getFullSiteUrl($domain,'','document_srl',$document_srl,'key',$key,'act','trackback');
        }

        function getBlogApiService($args=null){
            $srl = Context::get('textyle_blogapi_services_srl');
            if($srl) $args->textyle_blogapi_services_srl = $srl;
            $output = executeQueryArray('upgletyle.getBlogApiServices',$args);
            if($srl) $this->add('services',$output->data);
            return $output;
        }

		function getModulePartConfig($module_srl=0){
			static $configs = array();

            $oModuleModel = &getModel('module');
			$config = $oModuleModel->getModuleConfig('upgletyle');
			if(!$config || !$config->allow_service) {
				$config->allow_service = array('board'=>1,'page'=>1);
			} 

			if($module_srl){
				$part_config = $oModuleModel->getModulePartConfig('upgletyle', $module_srl);
				if(!$part_config){
					$part_config = $config;
				}else{
					$vars = get_object_vars($part_config);
					if($vars){
						foreach($vars as $k => $v){
							$config->{$k} = $v;
						}
					}
				}
			}

			$configs[$module_srl] = $config;

			return $configs[$module_srl];
		}

		function moduleExistCheck($module_name) {
			$path = _XE_PATH_ . 'modules/'.$module_name;
			return file_exists($path);
		}

	}
?>
