<?php
    /**
     * @class  UpgletyleInfo
     * @author UPGLE (admin@upgle.com)
     * @brief  upgletyle module Upgletyle info class
     **/

    class UpgletyleInfo extends Object {

        var $site_srl = null;
        var $domain = null;
        var $upgletyle_srl = null;
        var $module_srl = null;
        var $member_srl = null;
        var $upgletyle_title = null;
        var $colorset = null;
        var $timezone = null;

        function UpgletyleInfo($upgletyle_srl = 0) {
            if(!$upgletyle_srl) return;
            $this->setUpgletyle($upgletyle_srl);
        }

        function setUpgletyle($upgletyle_srl) {
            $this->module_srl = $this->upgletyle_srl = $upgletyle_srl;
            $this->_loadFromDB();
        }

        function _loadFromDB() {
            $oUpgletyleModel = &getModel('upgletyle');

			if(!$this->upgletyle_srl) return;
            $args->module_srl = $this->upgletyle_srl;
            $output = executeQuery('upgletyle.getUpgletyle', $args);
            if(!$output->toBool()||!$output->data) return;
            $this->setAttribute($output->data);

            $config = $oUpgletyleModel->getModulePartConfig($this->module_srl);
            if($config && count($config)) {
                foreach($config as $key => $val) {
                    $this->add($key, $val);
                }
            }
        }

        function setAttribute($attribute) {
            if(!$attribute->module_srl) {
                $this->upgletyle_srl = null;
                return;
            }
            $this->module_srl = $this->upgletyle_srl = $attribute->module_srl;
            $this->member_srl = $attribute->member_srl;
            $this->colorset = $attribute->colorset;
            $this->domain = $attribute->domain;
            $this->site_srl = $attribute->site_srl;
            $this->timezone = $attribute->timezone;
            $this->default_language = $attribute->default_language;

            $this->adds($attribute);
        }

        function isHome() {
            $module_info = Context::get('module_info');
            if($this->getModuleSrl() == $module_info->module_srl) return true;
            return false;
        }

        function getBrowserTitle() {
            if(!$this->isExists()) return;
            return $this->get('browser_title');
        }

		function getUpgletyleTitle() {
            if(!$this->isExists()) return;
            return $this->get('upgletyle_title');
        }

        function getMid() {
            if(!$this->isExists()) return;
            return $this->get('mid');
        }

        function getMemberSrl() {
            if(!$this->isExists()) return;
            return $this->get('member_srl');
        }

        function getModuleSrl() {
            if(!$this->isExists()) return;
            return $this->getUpgletyleSrl();
        }

        function getUpgletyleSrl() {
            if(!$this->isExists()) return;
            return $this->upgletyle_srl;
        }

        function getUpgletyleMid() {
            if(!$this->isExists()) return;
            return $this->get('mid');
        }

        function getNickName() {
            if(!$this->isExists()) return;
            $nick_name = $this->get('nick_name');
            if(!$nick_name) $nick_name = $this->getUserId();
            return $nick_name;
        }

        function getUserName() {
            if(!$this->isExists()) return;
            return $this->get('user_name');
        }
        function getProfileContent() {
            if(!$this->isExists()) return;
            return $this->get('profile_content');
        }
        function getTextyleContent() {
            if(!$this->isExists()) return;
            return $this->get('upgletyle_content');
        }
        function getUpgletyleContent() {
            if(!$this->isExists()) return;
            return $this->get('upgletyle_content');
        }
        function getEmail() {
            if(!$this->isExists()) return;
            return $this->get('email_address');
        }

        function getPostStyle() {
            if(!$this->isExists()) return;
            return $this->get('post_style');
        }
        function getPostEditorSkin() {
            if(!$this->isExists()) return;
            return $this->get('post_editor_skin');
        }


        function getPostListCount() {
            if(!$this->isExists()) return;
            return $this->get('post_list_count');
        }

        function getCommentListCount() {
            if(!$this->isExists()) return;
            return $this->get('comment_list_count');
        }

        function getGuestbookListCount() {
            if(!$this->isExists()) return;
            return $this->get('guestbook_list_count');
        }

        function getInputEmail(){
            if(!$this->isExists()) return;
            return $this->get('input_email');
        }

        function getInputWebsite(){
            if(!$this->isExists()) return;
            return $this->get('input_website');
        }

        function getEnableMe2day() {
            return $this->get('enable_me2day')=='Y'?true:false;
        }

        function getMe2dayUserID() {
            return $this->get('me2day_userid');
        }

        function getMe2dayUserKey() {
            return $this->get('me2day_userkey');
        }
        
    	function getTwitterConsumerKey() {
            return $this->get('twitter_consumer_key');
        }
        
    	function getTwitterConsumerSecret() {
            return $this->get('twitter_consumer_secret');
        }
        
    	function getTwitterOauthToken() {
            return $this->get('twitter_oauth_token');
        }
        
    	function getTwitterOauthTokenSecret() {
            return $this->get('twitter_oauth_token_secret');
        }

        function getEnableTwitter() {
            return $this->get('enable_twitter')=='Y'?true:false;
        }

        function getTwitterUserID() {
            return $this->get('twitter_userid');
        }

        function getTwitterPassword() {
            return $this->get('twitter_password');
        }

        function getPostUsePrefix(){
            if(!$this->isExists()) return;
            return $this->get('post_use_prefix');
        }

        function getPostUseSuffix(){
            if(!$this->isExists()) return;
            return $this->get('post_use_suffix');
        }

        function getPostPrefix($force=false) {
            if(!$this->isExists()) return;
            if($force || $this->getPostUsePrefix()=='Y') return $this->get('post_prefix');
            else return;
        }

        function getPostSuffix($force=false) {
            if(!$this->isExists()) return;
            if($force || $this->getPostUsesuffix()=='Y') return $this->get('post_suffix');
            else return;
        }

        function getUserID() {
            if(!$this->isExists()) return;
            return $this->get('user_id');
        }

        function getSubscriptionDate() {
            if(!$this->isExists()) return;
            return $this->get('subscription_date');
        }

        function getProfilePhotoSrc(){
            if(!$this->isExists()) return;
            $oUpgletyleModel = &getModel('upgletyle');
            $src = $oUpgletyleModel->getUpgletylePhotoSrc($this->member_srl);
            return $src;
        }

        function getProfileDefaultPhotoSrc(){
            $oUpgletyleModel = &getModel('upgletyle');
            $src = $oUpgletyleModel->getUpgletyleDefaultPhotoSrc();
            return $src;
        }

        function getFaviconSrc(){
            if(!$this->isExists()) return;
            $oUpgletyleModel = &getModel('upgletyle');
            return $oUpgletyleModel->getUpgletyleFaviconSrc($this->module_srl);
        }

        function getDefaultFaviconSrc(){
            $oUpgletyleModel = &getModel('upgletyle');
            $src = $oUpgletyleModel->getUpgletyleDefaultFaviconSrc();
            return $src;
        }

        function isExists() {
            return $this->upgletyle_srl?true:false;
        }

        function getPermanentUrl() {
            if(!$this->isExists()) return;
            return getUrl('','mid',$this->getMid());
        }

        function getPostCount(){
            if(!$this->isExists()) return;
            $oDocumentModel = &getModel('document');
            $count = 0;
            $count += $oDocumentModel->getDocumentCount($this->module_srl);
            $count += $oDocumentModel->getDocumentCount($this->module_srl * -1);
            return $count;
        }

        function getPostTempCount(){
            if(!$this->isExists()) return;
            $oDocumentModel = &getModel('document');
            $count = 0;
            $count += $oDocumentModel->getDocumentCount($this->member_srl);
            return $count;
        }

        function getCommentAllCount($flag=1){
            if(!$this->isExists()) return;
            $oCommentModel = &getModel('comment');
            return $oCommentModel->getCommentAllCount($this->module_srl*$flag);
        }

        function getTrackbackAllCount($flag=1){
            if(!$this->isExists()) return;
            $oTrackbackModel = &getModel('trackback');
            return $oTrackbackModel->getTrackbackAllCount($this->module_srl*$flag);
        }

        function isRssEnabled() {
            static $open_rss = null;
            if(!$this->isExists()) return;
            if(is_null($open_rss)) {
                $oRssModel = &getModel('rss');
                $module_info = $oRssModel->getRssModuleConfig($this->getModuleSrl());
                $open_rss = $module_info->open_rss;
            }
            return $open_rss=='Y'?true:false;

        }

        function getFontFamily() {
            static $font_family = null;
            if(is_null($font_family)) {
                $oEditorModel = &getModel('editor');
                $editor_config = $oEditorModel->getEditorConfig($this->getModuleSrl());
                $font_family = $editor_config->content_font;
            }
            return str_replace('"','',$font_family);
        }

        function getFontSize() {
            static $font_size = null;
            if(is_null($font_size)) {
                $oEditorModel = &getModel('editor');
                $editor_config = $oEditorModel->getEditorConfig($this->getModuleSrl());
                $font_size = $editor_config->content_font_size;
            }
            return $font_size;
        }

        function getCommentGrant() {
            return $this->get('comment_grant');
        }

        function isEnableComment() {
            if(!$this->getCommentGrant() || Context::get('is_logged')) return true;
        }

        function getGuestbookGrant() {
            return $this->get('guestbook_grant');
        }

        function isEnableGuestbook() {
            if(!$this->getGuestbookGrant() || Context::get('is_logged')) return true;
        }

        function getApis() {
            $args->module_srl = $this->module_srl;
            $output = executeQueryArray('upgletyle.getApis', $args);
            return $output->data;
        }
   }
?>
