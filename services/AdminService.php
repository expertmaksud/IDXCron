<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Provide all kind of admin related service
 *
 * @author Maksud-Ul-Alam
 */
if (!class_exists("AdminService")) {

    class AdminService {

        private static $instance;

        private function __construct() {
            
        }

        public static function getInstance() {
            if (!isset(self::$instance)) {
                self::$instance = new AdminService();
            }

            return self::$instance;
        }

        public function generateAdminPage() {
            require(IDX_CRON_DIR.'/templates/AdminPage_tmpl.php');
            //echo "Test Success";
        }

        public function createMenu() {

            add_management_page("Data Importer", "Import IDx Data", "import", "Import_Idx_Listing", array($this, 'generateAdminPage'));
        }

    }

}
