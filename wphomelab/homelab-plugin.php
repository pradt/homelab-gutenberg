<?php
/**
 * Plugin Name: Homelab Plugin
 * Plugin URI: 
 * Description: A plugin to manage homelab assets within WordPress.
 * Version: 0.01
 * Author: Pratheepan Thevadasan
 * Author URI: 
 */

 function homelab_admin_scripts() {
    wp_enqueue_script('jquery');
}
add_action('admin_enqueue_scripts', 'homelab_admin_scripts');
require_once plugin_dir_path(__FILE__) . 'menupageregistration.php';
require_once plugin_dir_path(__FILE__) . 'includes/helperfunctions/generalhelpers.php';
require_once plugin_dir_path(__FILE__) . 'includes/GutenbergBlocks/wphomelab-blocks.php';
require_once plugin_dir_path(__FILE__) . 'includes/schedule-data-fetch.php';
require_once plugin_dir_path(__FILE__) . 'includes/servicecheck.php';
require_once plugin_dir_path(__FILE__) . 'includes/schedule-service-check.php';
require_once plugin_dir_path(__FILE__) . 'includes/dataretentioncron.php';
require_once plugin_dir_path(__FILE__) . 'includes/pages/settingspage.php';
require_once plugin_dir_path(__FILE__) . 'includes/GutenbergBlocks/homelab-block.php';
require_once plugin_dir_path(__FILE__) . 'includes/integerations/integerationincludes.php';
require_once plugin_dir_path(__FILE__) . 'includes/datafetch.php';
require_once plugin_dir_path(__FILE__) . 'includes/addnote.php';

//Pages
require_once plugin_dir_path(__FILE__) . 'includes/pages/createservice.php';
require_once plugin_dir_path(__FILE__) . 'includes/pages/editservice.php';
require_once plugin_dir_path(__FILE__) . 'includes/pages/listservices.php';
require_once plugin_dir_path(__FILE__) . 'includes/pages/viewservice.php';
require_once plugin_dir_path(__FILE__) . 'includes/pages/test.php';
require_once plugin_dir_path(__FILE__) . 'includes/pages/visualiseblock.php';
require_once plugin_dir_path(__FILE__) . 'includes/pages/debugpages/troubleshooting.php';
require_once plugin_dir_path(__FILE__) . 'includes/pages/debugpages/tableviewer.php';
require_once plugin_dir_path(__FILE__) . 'includes/pages/debugpages/viewschedulefetchs.php';
//API
require_once plugin_dir_path(__FILE__) . 'includes/Api/getservices-api.php';


// Create necessary tables on plugin activation
function homelab_plugin_activate() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    $table_name_services = $wpdb->prefix . 'homelab_services';
    $table_name_images = $wpdb->prefix . 'homelab_service_images';
    $table_name_status_logs = $wpdb->prefix . 'homelab_service_status_logs';
    $table_name_service_data = $wpdb->prefix . 'homelab_service_data';

    $sql_services = "CREATE TABLE $table_name_services (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        description text,
        icon varchar(255),
        image_id mediumint(9),
        tags varchar(255),
        category_id mediumint(9),
        color varchar(7),
        parent_id mediumint(9),
        service_url varchar(255),
        alt_page_url varchar(255),
        status_check tinyint(1) NOT NULL DEFAULT 0,
        status_check_url varchar(255),
        disable_ssl tinyint(1) NOT NULL DEFAULT 0,
        accepted_response varchar(255),
        polling_interval int(11) NOT NULL DEFAULT 300,
        notify_email varchar(255),
        tunnel_id varchar(255),
        account_id varchar(255),
        enable_now_playing tinyint(1) NOT NULL DEFAULT 0,
        enable_blocks tinyint(1) NOT NULL DEFAULT 0,
        check_uuid varchar(255),
        client_id varchar(255),
        toots_limit int(11) NOT NULL DEFAULT 1,
        server_port int(11) NOT NULL DEFAULT 1,
        volume varchar(255),
        service_type varchar(255),
        get_data tinyint(1),
        api_url varchar(255),
        api_key varchar(255),
        username varchar(255),
        password varchar(255),
        fetch_interval int(11),
        PRIMARY KEY (id)
    ) $charset_collate;";

    $sql_images = "CREATE TABLE $table_name_images (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        url varchar(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $sql_status_logs = "CREATE TABLE $table_name_status_logs (
        id CHAR(36) NOT NULL,
        service_id mediumint(9) NOT NULL,
        check_datetime datetime NOT NULL,
        url varchar(255) NOT NULL,
        response_code smallint(6) NOT NULL,
        response_message varchar(255) NOT NULL,
        status varchar(10) NOT NULL,
        PRIMARY KEY (id),
        KEY service_id (service_id)
    ) $charset_collate;";

    $table_name_notes = $wpdb->prefix . 'homelab_service_notes';

    $sql_notes = "CREATE TABLE $table_name_notes (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        service_id mediumint(9) NOT NULL,
        user_id bigint(20) UNSIGNED NOT NULL,
        note text NOT NULL,
        created_at datetime NOT NULL,
        PRIMARY KEY (id),
        KEY service_id (service_id),
        KEY user_id (user_id)
    ) $charset_collate;";

$sql_service_data = "CREATE TABLE $table_name_service_data (
    id CHAR(36) NOT NULL,
    service_id mediumint(9) NOT NULL,
    fetched_at datetime NOT NULL,
    data longtext,
    is_scheduled tinyint(1) NOT NULL DEFAULT 1,
    error_message text,
    error_timestamp datetime,
    PRIMARY KEY (id),
    FOREIGN KEY (service_id) REFERENCES $table_name_services (id)
) $charset_collate;";

// Create settings table
$table_name_settings = $wpdb->prefix . 'homelab_settings';
$sql_settings = "CREATE TABLE $table_name_settings (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    option_name varchar(255) NOT NULL,
    option_value longtext,
    PRIMARY KEY (id),
    UNIQUE KEY option_name (option_name)
) $charset_collate;";


    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_services);
    dbDelta($sql_images);
    dbDelta($sql_status_logs);
    dbDelta($sql_notes);
    dbDelta($sql_service_data);
    dbDelta($sql_settings);

    // Add default settings if not exists
    $default_settings = [
        'api_access' => 'disabled',
        'api_key' => homelab_generate_api_key(),
        'data_retention_days' => 60,
        'status_log_retention_days' => 60,
        'service_data_cron_active' => 1,
        'status_log_cron_active' => 1,
    ];
    add_option('homelab_settings', $default_settings);

    if (has_action('homelab_delete_old_service_data', 'homelab_cron_delete_old_service_data')) {
        error_log('The homelab_delete_old_service_data action is registered with the homelab_cron_delete_old_service_data callback.');
    } else {
        error_log('The homelab_delete_old_service_data action is not registered with the homelab_cron_delete_old_service_data callback.');
    }
    
    // Schedule cron jobs
    if (!wp_next_scheduled('homelab_delete_old_service_data')) {
        wp_schedule_event(time(), 'daily', 'homelab_delete_old_service_data');
    }
    if (!wp_next_scheduled('homelab_delete_old_status_logs')) {
        wp_schedule_event(time(), 'daily', 'homelab_delete_old_status_logs');
    }

}
register_activation_hook(__FILE__, 'homelab_plugin_activate');

//Activates scheduled events
function homelab_activate_plugin() {
    global $wpdb;

    // Log the start of the activation process
    error_log("homelab_activate_plugin: Starting plugin activation");

    // Get all services from the database
    $table_name_services = $wpdb->prefix . 'homelab_services';
    $services = $wpdb->get_results("SELECT * FROM $table_name_services");

    // Log the number of services found
    error_log("homelab_activate_plugin: Found " . count($services) . " services");

    // Iterate through each service and start the data fetch
    foreach ($services as $service) {
        // Log the service ID and fetch interval being processed
        error_log("homelab_activate_plugin: Starting data fetch for service ID " . $service->id . " with fetch interval " . $service->fetch_interval);

        homelab_schedule_data_fetch($service->id, $service->fetch_interval);
    }

    // Log the completion of the activation process
    error_log("homelab_activate_plugin: Plugin activation completed");
}
register_activation_hook(__FILE__, 'homelab_activate_plugin');




function homelab_enqueue_block_scripts() {
    // Enqueue JavaScript for block editor
    wp_enqueue_script(
        'homelab-block',
        plugins_url('assets/js/homelab-block.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-block-editor', 'wp-components', 'wp-i18n'),
        filemtime(plugin_dir_path(__FILE__) . 'assets/js/homelab-block.js')
    );

    // Enqueue CSS for block editor
    wp_enqueue_style(
        'homelab-block-editor-styles',
        plugins_url('assets/css/styles.css', __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'assets/css/styles.css')
    );
  }
  add_action('enqueue_block_editor_assets', 'homelab_enqueue_block_scripts');

  function homelab_enqueue_front_styles() {
    wp_enqueue_style(
        'homelab-block-styles',
        plugins_url('assets/css/styles.css', __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'assets/css/styles.css')
    );
}
add_action('wp_enqueue_scripts', 'homelab_enqueue_front_styles');

  

function homelab_register_service_block() {
    register_block_type('homelab/service-block', array(
      'render_callback' => 'homelab_render_service_block',
    ));
  }
  add_action('init', 'homelab_register_service_block');

  function register_layout_category( $categories ) {
	
	$categories[] = array(
		'slug'  => 'WPHomeLab',
		'title' => 'WPHomeLab'
	);

	return $categories;
}

if ( version_compare( get_bloginfo( 'version' ), '5.8', '>=' ) ) {
	add_filter( 'block_categories_all', 'register_layout_category' );
} else {
	add_filter( 'block_categories', 'register_layout_category' );
}











function homelab_deactivate_plugin() {
    global $wpdb;

    // Log the start of the deactivation process
    error_log("homelab_deactivate_plugin: Starting plugin deactivation");

    // Get all services from the database
    $table_name_services = $wpdb->prefix . 'homelab_services';
    $services = $wpdb->get_results("SELECT * FROM $table_name_services");

    // Log the number of services found
    error_log("homelab_deactivate_plugin: Found " . count($services) . " services");

    // Iterate through each service and stop the data fetch
    foreach ($services as $service) {
        // Log the service ID being processed
        error_log("homelab_deactivate_plugin: Stopping data fetch for service ID " . $service->id);

        homelab_stop_data_fetch($service->id);
    }

    wp_clear_scheduled_hook('homelab_delete_old_service_data');
    wp_clear_scheduled_hook('homelab_delete_old_status_logs');

    // Log the completion of the deactivation process
    error_log("homelab_deactivate_plugin: Plugin deactivation completed");
}
register_deactivation_hook(__FILE__, 'homelab_deactivate_plugin');




