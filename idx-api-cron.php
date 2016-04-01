<?php

/*
  Plugin Name: IDX Api Cron
  Plugin URI: http://www.github.com
  Description: This is a wordpress cron to collect MSL listing using ihomefinder cloudIDX bulk api
  Version: 1.0
  Author: Maksud-Ul-Alam
  Contributors: IDX, LLC
  Author URI: http://expertmaksud.elance.com

 */

define("IDX_CRON_DIR", dirname(__FILE__));

include_once 'common/CloudApiConst.php';
include_once 'common/iiacEnqueueResources.php';

include_once 'helpers/CloudApiHelper.php';
include_once 'helpers/WPDatabaseHelper.php';
include_once 'services/CloudApiService.php';
include_once 'services/AdminService.php';

if (is_admin()) {
    add_action("init", array(iiacEnqueueResources::getInstance(), "loadStandardJavaScript"));
    add_action("init", array(iiacEnqueueResources::getInstance(), "loadJavascripts"));
    
}

/**
 * On activation, set a time, frequency and name of an action hook to be scheduled.
 */
function iiac_activations() {
        WPDatabaseHelper::getInstance()->CreateMLSListingTables();
	wp_schedule_event( time(), 'daily', 'iiac_daily_event_hook' );
}

function iiac_deactivations() {
        WPDatabaseHelper::getInstance()-> DropMlsListingTables();
	wp_clear_scheduled_hook( 'iiac_daily_event_hook' );
}
//add_filter('the_content', array(CloudApiService::getInstance(), "loadListingsPhotos"));

add_action('admin_menu', array(AdminService::getInstance(),"createMenu"));
add_action( 'iiac_daily_event_hook', array(CloudApiService::getInstance(), "loadMLSIDxData") );
//add_filter('the_content', array(WPDatabaseHelper::getInstence(), "CreateMLSListingTable"));
register_activation_hook(__FILE__, "iiac_activations");
register_deactivation_hook(__FILE__, "iiac_deactivations");

//Ajax hooks
add_action("wp_ajax_iiac_import_data", array(CloudApiService::getInstance(), "LoadDataViaAjax"));







