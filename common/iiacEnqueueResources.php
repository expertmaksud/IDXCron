<?php

/**
 * Description of EnqueueResources
 *
 * @author Maksud ul Alam
 */
if (!class_exists("iiacEnqueueResources")) {

    class iiacEnqueueResources {

        private static $instance;

        private function __construct() {
            
        }

        public static function getInstance() {
            if (!isset(self::$instance)) {
                self::$instance = new iiacEnqueueResources();
            }
            return self::$instance;
        }

        public function loadStandardJavaScript() {
            wp_enqueue_script("jquery");
        }

        public function loadJavascripts() {
            
            wp_register_script("iiac-blockUI", plugins_url("assets/js/libs/jquery.blockUI.js", dirname(__FILE__)), "jquery");
            
            wp_register_script("iiac-admin", plugins_url("assets/js/customs/iiac.admin.js", dirname(__FILE__)), "jquery");
            wp_localize_script('iiac-admin', 'iiacAjax', array('ajaxurl' => admin_url('admin-ajax.php'), 'postNonce' => wp_create_nonce('iiacajax-post-nonce')));

            
            wp_enqueue_script("iiac-blockUI");
            wp_enqueue_script("iiac-admin");
        }

        public function loadCss() {
           
        }

    }

}
?>
