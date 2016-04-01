<?php

/**
 * This class is use for performing CURD operation in Wordpress database
 *
 * @author Maksud-Ul-Alam
 */
if (!class_exists('WPDatabaseHelper')) {

    class WPDatabaseHelper {

        private static $instance;

        private function __construct() {
            
        }

        public static function getInstance() {
            if (!isset(self::$instance)) {
                self::$instance = new WPDatabaseHelper();
            }

            return self::$instance;
        }

        public function CreateMLSListingTables() {

            global $wpdb;

            $table_name = $wpdb->prefix . CloudApiConst::mlsListingTableName;
            $photo_table_name = $wpdb->prefix . CloudApiConst::mlsListingPhotoTableName;


            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (" .
                    CloudApiConst::mlsListingTableFields
                    . ") $charset_collate; "
                    . "CREATE TABLE $photo_table_name (" .
                    CloudApiConst::mlsListingPhotosTableField
                    . ") $charset_collate;";

            //return $sql;
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta($sql);
        }

        public function DropMlsListingTables() {
            //drop all custom db table
            global $wpdb;
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}mlslistings");
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}mlslistingsphotos");
        }

        public function DumpData($dataFilePath) {
            global $wpdb;
            $table_name = $wpdb->prefix . CloudApiConst::mlsListingTableName;
            //Delete all rows from the table
            $wpdb->query($wpdb->prepare("TRUNCATE TABLE $table_name"));
            //Insert all the listing from the csv
            $wpdb->query($wpdb->prepare("LOAD DATA LOCAL INFILE '" . $dataFilePath . "' INTO TABLE $table_name "
                            . "FIELDS TERMINATED BY ',' "
                            . "LINES TERMINATED BY '\\n'"));
            error_log(print_r($wpdb->queries, true));
        }

        public function DumpAllListingsPhotos($xmlFilePath) {
            global $wpdb;
            $table_name = $wpdb->prefix . CloudApiConst::mlsListingPhotoTableName;

            //Delete all rows from the table
            $wpdb->query($wpdb->prepare("TRUNCATE TABLE $table_name"));
            //Insert all the listing from the csv
            $wpdb->query($wpdb->prepare("LOAD XML LOCAL INFILE '" . $xmlFilePath . "' INTO TABLE $table_name "
                            . "ROWS IDENTIFIED BY '<Photo>'"));

            error_log(print_r($wpdb->queries, true));
        }

        public function getAllLinstingsNumbers() {
            global $wpdb;
            $table_name = $wpdb->prefix . CloudApiConst::mlsListingTableName;
            $listingsNumbers = $wpdb->get_col($wpdb->prepare("SELECT  listingnumber FROM  $table_name"));
            return $listingsNumbers;
        }

    }

}
?>