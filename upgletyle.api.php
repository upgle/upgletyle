<?php
    /**
     * @class  upgletyleAPI
     * @author UPGLE (admin@upgle.com)
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
