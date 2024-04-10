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

require_once plugin_dir_path(__FILE__) . 'includes/GutenbergBlocks/homelab-block.php';
require_once plugin_dir_path(__FILE__) . 'includes/addnote.php';
require_once plugin_dir_path(__FILE__) . 'includes/createservice.php';
require_once plugin_dir_path(__FILE__) . 'includes/editservice.php';
require_once plugin_dir_path(__FILE__) . 'includes/listservices.php';
require_once plugin_dir_path(__FILE__) . 'includes/viewservice.php';
require_once plugin_dir_path(__FILE__) . 'includes/test.php';


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
        PRIMARY KEY (id)
    ) $charset_collate;";

    $sql_images = "CREATE TABLE $table_name_images (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        url varchar(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $sql_status_logs = "CREATE TABLE $table_name_status_logs (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
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
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    service_id mediumint(9) NOT NULL,
    fetched_at datetime NOT NULL,
    data longtext,
    is_scheduled tinyint(1) NOT NULL DEFAULT 1,
    error_message text,
    error_timestamp datetime,
    PRIMARY KEY (id),
    FOREIGN KEY (service_id) REFERENCES $table_name_services (id)
) $charset_collate;";


    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_services);
    dbDelta($sql_images);
    dbDelta($sql_status_logs);
    dbDelta($sql_notes);
    dbDelta($sql_service_data);

}
register_activation_hook(__FILE__, 'homelab_plugin_activate');

// Register the REST API endpoint for retrieving services
//Endpoint is used in the Gutenberg Block
function homelab_register_services_endpoint() {
    register_rest_route('homelab/v1', '/services', array(
        'methods' => 'GET',
        'callback' => 'homelab_get_services',
        'permission_callback' => '__return_true',
    ));
}
add_action('rest_api_init', 'homelab_register_services_endpoint');





// Callback function for the services endpoint
function homelab_get_services() {
    global $wpdb;

    $table_name_services = $wpdb->prefix . 'homelab_services';
    $services = $wpdb->get_results("SELECT * FROM $table_name_services");

    $formatted_services = array();
    foreach ($services as $service) {
        $formatted_service = array(
            'id' => $service->id,
            'name' => $service->name,
            'icon' => $service->icon,
            'image_url' => wp_get_attachment_url($service->image_id),
            'status_check' => (bool) $service->status_check,
            'status_class' => homelab_get_service_status_class($service->id),
            'description' => $service->description,
            'service_url' => $service->service_url,
            'tags' => $service->tags,
            'category_id' => $service->category_id,
        );
        $formatted_services[] = $formatted_service;
    }

    return $formatted_services;
}


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

  
// Add menu items
function homelab_plugin_menu() {
    add_menu_page(
        'Homelab',
        'Homelab',
        'manage_options',
        'homelab',
        'homelab_setup_page',
        'dashicons-admin-generic',
        30
    );
    add_submenu_page(
        'homelab',
        'Setup',
        'Setup',
        'manage_options',
        'homelab-setup',
        'homelab_setup_page'
    );
    add_submenu_page(
        'homelab',
        'Create New Service',
        'Create New Service',
        'manage_options',
        'homelab-create-service',
        'homelab_create_service_page'
    );
    add_submenu_page(
        'homelab',
        'List Services',
        'List Services',
        'manage_options',
        'homelab-list-services',
        'homelab_list_services_page'
    );
    add_submenu_page(
        'homelab',
        'Status Check Settings',
        'Status Check Settings',
        'manage_options',
        'homelab-status-check-settings',
        'homelab_status_check_settings_page'
    );

     add_submenu_page(
        'homelab', // Assuming this is a registered parent page
        'Edit Service', // Page title
        'Edit Service', // Menu title
        'edit_posts', // Capability
        'homelab-edit-service', // Menu slug
        'homelab_edit_service_page' // Function to display the page
    );
    
    

    add_submenu_page(
        'homelab', // Assuming this is a registered parent page
        'View Service', // Page title
        'View Service', // Menu title
        'edit_posts', // Capability
        'homelab-view-service', // Menu slug
        'homelab_view_service_page' // Function to display the page
    );
    
    //Test
    add_submenu_page(
        'homelab', // Assuming this is a registered parent page
        'View Test List', // Page title
        'View Test List', // Menu title
        'edit_posts', // Capability
        'homelab_test_list', // Menu slug
        'homelab_test_list_page' // Function to display the page
    ); 

    // Now remove the submenu page
    //remove_submenu_page('homelab', 'homelab-edit-service');
}
add_action('admin_menu', 'homelab_plugin_menu');






/* function homelab_admin_menu() {
    $hook_suffix = add_submenu_page(
        'homelab-list-services', // Assuming this is a registered parent page
        'Edit Service', // Page title
        'Edit Service', // Menu title
        'manage_options', // Capability
        'homelab-edit-service', // Menu slug
        'homelab_edit_service_page' // Function to display the page
    );

    // Now remove the submenu page
    remove_submenu_page('homelab-list-services', 'homelab-edit-service');
}
add_action('admin_menu', 'homelab_admin_menu'); */

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
  /*function homelab_render_service_block($attributes) {
    $service_id = $attributes['serviceId'];
    $show_status = $attributes['showStatus'];
    $show_description = $attributes['showDescription'];
    $show_icon = $attributes['showIcon'];
    $show_image = $attributes['showImage'];
    $show_launch_button = $attributes['showLaunchButton'];
    $show_tags_button = $attributes['showTagsButton'];
    $show_category_button = $attributes['showCategoryButton'];
    $show_view_button = $attributes['showViewButton'];

    // Retrieve the service data based on the service ID
    global $wpdb;
    $table_name_services = $wpdb->prefix . 'homelab_services';
    $service = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name_services WHERE id = %d", $service_id));

    if (!$service) {
        return '';
    }

    $image_url = '';
    if ($service->image_id) {
        $image_url = wp_get_attachment_url($service->image_id);
    }

    $status_class = '';
    $status_color = '';
    if ($service->status_check) {
        $status = homelab_get_latest_service_status($service->id);
        $status_class = strtolower($status);
        switch ($status) {
            case 'GREEN':
                $status_color = 'green';
                break;
            case 'AMBER':
                $status_color = 'orange';
                break;
            case 'RED':
                $status_color = 'red';
                break;
            default:
                $status_color = 'gray';
                break;
        }
    }

    $description = wp_trim_words($service->description, 20, '...');

    ob_start();
    ?>
    <div class="homelab-service-block">
        <div class="homelab-service-header">
            <?php if ($show_icon && $service->icon) : ?>
                <div class="homelab-service-icon">
                    <i class="<?php echo esc_attr($service->icon); ?>"></i>
                </div>
            <?php endif; ?>
            <?php if ($show_image && $image_url) : ?>
                <div class="homelab-service-image">
                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($service->name); ?>">
                </div>
            <?php endif; ?>
            <h3 class="homelab-service-title"><?php echo esc_html($service->name); ?></h3>
            <?php if ($show_status && $service->status_check) : ?>
                <div class="homelab-service-status">
                    <span class="status-indicator <?php echo esc_attr($status_class); ?>" style="background-color: <?php echo esc_attr($status_color); ?>;"></span>
                </div>
            <?php endif; ?>
        </div>
        <?php if ($show_description) : ?>
            <div class="homelab-service-body">
                <p class="homelab-service-description"><?php echo esc_html($description); ?> <a href="#" class="read-more">Read More</a></p>
            </div>
        <?php endif; ?>
        <div class="homelab-service-footer">
            <?php if ($show_launch_button && $service->service_url) : ?>
                <a href="<?php echo esc_url($service->service_url); ?>" target="_blank" class="homelab-service-launch-button">Launch</a>
            <?php endif; ?>
            <?php if ($show_tags_button && $service->tags) : ?>
    <?php
    $tags = explode(',', $service->tags);
    foreach ($tags as $tag) {
        $tag = trim($tag);
        $tag_link = get_tag_link(get_term_by('name', $tag, 'post_tag')->term_id);
        echo '<a href="' . esc_url($tag_link) . '" class="homelab-service-tags-button">' . esc_html($tag) . '</a> ';
    }
    ?>
<?php endif; ?>
<?php if ($show_category_button && $service->category_id) : ?>
    <a href="<?php echo esc_url(get_category_link($service->category_id)); ?>" class="homelab-service-category-button">Posts by Category</a>
<?php endif; ?>
            <?php if ($show_view_button) : ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=homelab-view-service&service_id=' . $service->id)); ?>" class="homelab-service-view-button">View Service</a>
            <?php endif; ?>
        </div>
    </div>
    <style>
        .homelab-service-block {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 1px 1px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            padding: 15px;
        }
        .homelab-service-header {
            align-items: center;
            display: flex;
            margin-bottom: 10px;
        }
        .homelab-service-icon {
            font-size: 24px;
            margin-right: 10px;
        }
        .homelab-service-image {
            margin-right: 10px;
        }
        .homelab-service-image img {
            max-width: 100%;
            height: auto;
        }
        .homelab-service-title {
            margin: 0;
        }
        .homelab-service-status {
            margin-left: auto;
        }
        .status-indicator {
            border-radius: 50%;
            display: inline-block;
            height: 10px;
            width: 10px;
        }
        .homelab-service-body {
            margin-bottom: 10px;
        }
        .homelab-service-description {
            margin: 0;
        }
        .read-more {
            color: #007bff;
            text-decoration: none;
        }
        .read-more:hover {
            text-decoration: underline;
        }
        .homelab-service-footer {
            text-align: right;
        }
        .homelab-service-footer a {
            background-color: #007bff;
            border-color: #007bff;
            border-radius: 4px;
            color: #fff;
            display: inline-block;
            margin-left: 5px;
            padding: 6px 12px;
            text-align: center;
            text-decoration: none;
            vertical-align: middle;
        }
        .homelab-service-footer a:hover {
            background-color: #0069d9;
            border-color: #0062cc;
        }
    </style>
    <?php
    return ob_get_clean();
}*/

// Setup page callback
function homelab_setup_page() {
    echo '<h1>Homelab Setup</h1>';
    // Add setup page content here
}

// Create new service page callback
/* function homelab_create_service_page() {
    ob_start();
    // Handle form submission
    if (isset($_POST['submit'])) {
        homelab_save_service();
        wp_redirect(admin_url('admin.php?page=homelab-list-services'));
        exit;
    }

    // Render the create service form
    ?>
    <div class="wrap">
        <h1>Create New Service</h1>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th><label for="name">Name</label></th>
                    <td><input type="text" name="name" id="name" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="description">Description</label></th>
                    <td><textarea name="description" id="description" rows="4" cols="50"></textarea></td>
                </tr>
                <tr>
                    <th><label for="icon">Icon</label></th>
                    <td>
                        <select name="icon" id="icon">
                            <option value="">Select an icon</option>
                            <!-- Add Font Awesome icon options here -->
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="image">Service Image</label></th>
                    <td>
                        <select name="image" id="image">
                            <option value="">Select an image</option>
                            <?php
                            global $wpdb;
                            $table_name_images = $wpdb->prefix . 'homelab_service_images';
                            $images = $wpdb->get_results("SELECT * FROM $table_name_images");
                            foreach ($images as $image) {
                                echo '<option value="' . $image->id . '">' . $image->name . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="tags">Tags</label></th>
                    <td><input type="text" name="tags" id="tags" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="category">Category</label></th>
                    <td>
                        <?php
                        wp_dropdown_categories(array(
                            'name' => 'category',
                            'id' => 'category',
                            'taxonomy' => 'category',
                            'hide_empty' => 0,
                            'show_option_none' => 'Select a category',
                        ));
                        ?>
                    </td>
                </tr>
                <tr>
                    <th><label for="color">Color</label></th>
                    <td><input type="color" name="color" id="color"></td>
                </tr>
                <tr>
                    <th><label for="parent">Parent</label></th>
                    <td>
                        <select name="parent" id="parent">
                            <option value="">Select a parent service</option>
                            <?php
                            global $wpdb;
                            $table_name_services = $wpdb->prefix . 'homelab_services';
                            $services = $wpdb->get_results("SELECT * FROM $table_name_services");
                            foreach ($services as $service) {
                                echo '<option value="' . $service->id . '">' . $service->name . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="service_url">Service URL</label></th>
                    <td><input type="text" name="service_url" id="service_url" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="alt_page_url">Alternative Page URL</label></th>
                    <td><input type="text" name="alt_page_url" id="alt_page_url" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="status_check">Status Check</label></th>
                    <td><input type="checkbox" name="status_check" id="status_check" value="1"></td>
                </tr>
                <tr class="status-check-fields" style="display: none;">
                    <th><label for="status_check_url">Status Check URL</label></th>
                    <td><input type="text" name="status_check_url" id="status_check_url" class="regular-text"></td>
                </tr>
                <tr class="status-check-fields" style="display: none;">
                    <th><label for="disable_ssl">Disable SSL</label></th>
                    <td><input type="checkbox" name="disable_ssl" id="disable_ssl" value="1"></td>
                </tr>
                <tr class="status-check-fields" style="display: none;">
                    <th><label for="accepted_response">Accepted HTTP Response</label></th>
                    <td><input type="text" name="accepted_response" id="accepted_response" class="regular-text"></td>
                </tr>
                <tr class="status-check-fields" style="display: none;">
                    <th><label for="polling_interval">Polling Interval (seconds)</label></th>
                    <td><input type="number" name="polling_interval" id="polling_interval" class="regular-text" min="5"></td>
                </tr>
                <tr class="status-check-fields" style="display: none;">
                    <th><label for="notify_email">Notify Email</label></th>
                    <td><input type="email" name="notify_email" id="notify_email" class="regular-text"></td>
                </tr>
            </table>
            <?php submit_button('Create Service'); ?>
        </form>
    </div>
    <script>
    jQuery(document).ready(function($) {
        $('#status_check').change(function() {
            $('.status-check-fields').toggle(this.checked);
        });
    });
    </script>
    <?php
    ob_end_flush();
} */

// Save service details
/* function homelab_save_service() {
    ob_start();
    global $wpdb;

    $table_name_services = $wpdb->prefix . 'homelab_services';

    $name = sanitize_text_field($_POST['name']);
    $description = sanitize_textarea_field($_POST['description']);
    $icon = sanitize_text_field($_POST['icon']);
    $image_id = intval($_POST['image']);
    $tags = sanitize_text_field($_POST['tags']);
    $category_id = intval($_POST['category']);
    $color = sanitize_hex_color($_POST['color']);
    $parent_id = intval($_POST['parent']);
    $service_url = sanitize_text_field($_POST['service_url']);
    $alt_page_url = sanitize_text_field($_POST['alt_page_url']);
    $status_check = isset($_POST['status_check']) ? 1 : 0;
    $status_check_url = sanitize_text_field($_POST['status_check_url']);
    $disable_ssl = isset($_POST['disable_ssl']) ? 1 : 0;
    $accepted_response = sanitize_text_field($_POST['accepted_response']);
    $polling_interval = intval($_POST['polling_interval']);
    $notify_email = sanitize_email($_POST['notify_email']);

    $wpdb->insert(
        $table_name_services,
        array(
            'name' => $name,
            'description' => $description,
            'icon' => $icon,
            'image_id' => $image_id,
            'tags' => $tags,
            'category_id' => $category_id,
            'color' => $color,
            'parent_id' => $parent_id,
            'service_url' => $service_url,
            'alt_page_url' => $alt_page_url,
            'status_check' => $status_check,
            'status_check_url' => $status_check_url,
            'disable_ssl' => $disable_ssl,
            'accepted_response' => $accepted_response,
            'polling_interval' => $polling_interval,
            'notify_email' => $notify_email,
        )
    );

    $service_id = $wpdb->insert_id;

    if ($status_check) {
        homelab_add_service_check_schedule($service_id, $polling_interval);
    }

    // Redirect to the list services page after saving
    //wp_redirect(admin_url('admin.php?page=homelab-list-services'));
    //ob_end_flush(); // Send output buffer and turn off buffering
    //exit;
} */















// Get latest service status
function homelab_get_latest_service_status($service_id) {
    global $wpdb;

    $table_name_status_logs = $wpdb->prefix . 'homelab_service_status_logs';
    $status = $wpdb->get_var($wpdb->prepare("SELECT status FROM $table_name_status_logs WHERE service_id = %d ORDER BY check_datetime DESC LIMIT 1", $service_id));

    return $status ? $status : 'UNKNOWN';
}

// Check service status
function homelab_check_service_status($service_id) {
    global $wpdb;

    $table_name_services = $wpdb->prefix . 'homelab_services';
    $table_name_status_logs = $wpdb->prefix . 'homelab_service_status_logs';

    $service = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name_services WHERE id = %d", $service_id));

    if ($service->status_check) {
        $url = $service->status_check_url ? $service->status_check_url : $service->service_url;
        $disable_ssl = $service->disable_ssl;

        $args = array(
            'timeout' => 10,
            'sslverify' => !$disable_ssl,
        );

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            $status = 'RED';
            $response_code = 0;
            $response_message = $response->get_error_message();
        } else {
            $response_code = wp_remote_retrieve_response_code($response);
            $response_message = wp_remote_retrieve_response_message($response);

            if ($response_code >= 200 && $response_code <= 299) {
                $status = 'GREEN';
            } elseif ($response_code >= 100 && $response_code <= 199 || $response_code >= 300 && $response_code <= 399) {
                $status = 'AMBER';
            } else {
                $status = 'RED';
            }

            if ($status === 'RED') {
                $notify_email = $service->notify_email;
                if ($notify_email) {
                    $subject = 'Service Down: ' . $service->name;
                    $message = "The service '{$service->name}' is currently down. Please check the status.";
                    wp_mail($notify_email, $subject, $message);
                }
            }
        }

        $wpdb->insert(
            $table_name_status_logs,
            array(
                'service_id' => $service_id,
                'check_datetime' => current_time('mysql'),
                'url' => $url,
                'response_code' => $response_code,
                'response_message' => $response_message,
                'status' => $status,
            )
        );
    }
}

// Schedule service checks
function homelab_schedule_service_checks() {
    global $wpdb;

    $table_name_services = $wpdb->prefix . 'homelab_services';
    $services = $wpdb->get_results("SELECT * FROM $table_name_services WHERE status_check = 1");

    foreach ($services as $service) {
        if (!wp_next_scheduled('homelab_check_service_status_' . $service->id)) {
            wp_schedule_event(time(), 'homelab_service_check_interval_' . $service->id, 'homelab_check_service_status_' . $service->id);
        }
    }
}
add_action('init', 'homelab_schedule_service_checks');

// Add service check schedule
function homelab_add_service_check_schedule($service_id, $polling_interval) {
    $hook = 'homelab_check_service_status_' . $service_id;
    $recurrence = 'homelab_service_check_interval_' . $service_id;

    if (!wp_next_scheduled($hook)) {
        wp_schedule_event(time(), $recurrence, $hook);
    }

    add_filter('cron_schedules', function ($schedules) use ($recurrence, $polling_interval) {
        $schedules[$recurrence] = array(
            'interval' => $polling_interval,
            'display' => 'Every ' . $polling_interval . ' seconds',
        );
        return $schedules;
    });

    add_action($hook, function () use ($service_id) {
        homelab_check_service_status($service_id);
    });
}



// Helper function to get the service status class
function homelab_get_service_status_class($service_id) {
    $status = homelab_get_latest_service_status($service_id);
    switch ($status) {
        case 'GREEN':
            return 'status-green';
        case 'AMBER':
            return 'status-amber';
        case 'RED':
            return 'status-red';
        default:
            return '';
    }
}




?>