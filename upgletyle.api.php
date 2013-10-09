<?php
    /**
     * @class  upgletyleAPI
     * @author NHN (developers@xpressengine.com)
     * @brief  upgletyle module Action API class
     **/

    class upgletyleAPI extends upgletyle {

        /**
         * @brief check alias
         **/
        function dispUpgletylePostCheckAlias(&$oModule) {
            $oModule->add('document_srl',Context::get('document_srl'));
        }

    }

?>
