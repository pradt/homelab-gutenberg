<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Create new service page callback
function homelab_create_service_page() {
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
    <th><label for="service_type">Service/Application</label></th>
    <td>
        <select name="service_type" id="service_type">
            <option value="">Select a service</option>
            <option value="sonarr">Sonarr</option>
            <option value="radarr">Radarr</option>
            <option value="prowlarr">Prowlarr</option>
            <option value="lidarr">Lidarr</option>
            <option value="readarr">Readarr</option>
            <option value="tdarr">Tdarr</option>
            <option value="overseer">Overseer</option>
            <option value="other">Other</option>
        </select>
    </td>
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
                <tr class="get-data-fields" style="display: none;">
    <th><label for="get_data">Get Data</label></th>
    <td><input type="checkbox" name="get_data" id="get_data" value="1"></td>
</tr>
<tr class="api-fields" style="display: none;">
    <th><label for="api_url">API URL</label></th>
    <td><input type="text" name="api_url" id="api_url" class="regular-text"></td>
</tr>
<tr class="api-fields" style="display: none;">
    <th><label for="api_key">API Key</label></th>
    <td><input type="text" name="api_key" id="api_key" class="regular-text"></td>
</tr>
<tr class="api-fields" style="display: none;">
    <th><label for="username">Username</label></th>
    <td><input type="text" name="username" id="username" class="regular-text"></td>
</tr>
<tr class="api-fields" style="display: none;">
    <th><label for="password">Password</label></th>
    <td><input type="password" name="password" id="password" class="regular-text"></td>
</tr>
<tr class="api-fields" style="display: none;">
    <th><label for="fetch_interval">Fetch Every (seconds)</label></th>
    <td><input type="number" name="fetch_interval" id="fetch_interval" class="regular-text" min="5" value="600"></td>
</tr>
<tr class="api-fields" style="display: none;">
    <th>&nbsp;</th>
    <td>
        <button type="button" class="button" id="test-connection">Test Connection</button>
        <span class="spinner" id="test-connection-spinner" style="float: none; visibility: hidden;"></span>
        <div id="test-connection-result" style="margin-top: 5px;"></div>
    </td>
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
}

// Save service details
function homelab_save_service() {
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
    $service_type = sanitize_text_field($_POST['service_type']);
    $get_data = isset($_POST['get_data']) ? 1 : 0;
    $api_url = sanitize_text_field($_POST['api_url']);
    $api_key = sanitize_text_field($_POST['api_key']);
    $username = sanitize_text_field($_POST['username']);
    $password = sanitize_text_field($_POST['password']);
    $fetch_interval = intval($_POST['fetch_interval']);

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
            'service_type' => $service_type,
            'get_data' => $get_data,
            'api_url' => $api_url,
            'api_key' => $api_key,
            'username' => $username,
            'password' => $password,
            'fetch_interval' => $fetch_interval,
        )
    );

    $service_id = $wpdb->insert_id;

    if ($status_check) {
        homelab_add_service_check_schedule($service_id, $polling_interval);
    }

    if ($get_data) {
        homelab_schedule_data_fetch($service_id, $fetch_interval);
    }

    
}

function homelab_schedule_data_fetch($service_id, $fetch_interval) {
    // Fetch data from the service and store it in the database
    $service = homelab_get_service($service_id);
    $data = homelab_fetch_service_data($service);
    homelab_save_service_data($service_id, $data);

    // Schedule the next data fetch
    wp_schedule_single_event(time() + $fetch_interval, 'homelab_fetch_service_data', array($service_id));
}

function homelab_fetch_service_data($service) {
    $service_type = $service['service_type'];
    $api_url = $service['api_url'];
    $api_key = $service['api_key'];
    $username = $service['username'];
    $password = $service['password'];

    // Fetch data from the service based on the service type
    switch ($service_type) {
        case 'sonarr':
            return homelab_fetch_sonarr_data($api_url, $api_key);
        case 'radarr':
            return homelab_fetch_radarr_data($api_url, $api_key);
        case 'prowlarr':
            return homelab_fetch_prowlarr_data($api_url, $api_key);
        case 'lidarr':
            return homelab_fetch_lidarr_data($api_url, $api_key);
        case 'readarr':
            return homelab_fetch_readarr_data($api_url, $api_key);
        case 'tdarr':
            return homelab_fetch_tdarr_data($api_url, $api_key);
        case 'overseer':
            return homelab_fetch_overseer_data($api_url, $api_key);
        default:
            return array();
    }
}

function homelab_save_service_data($service_id, $data, $error_message = null, $error_timestamp = null) {
    global $wpdb;
    $table_name_service_data = $wpdb->prefix . 'homelab_service_data';

    $wpdb->insert(
        $table_name_service_data,
        array(
            'service_id' => $service_id,
            'fetched_at' => current_time('mysql'),
            'data' => json_encode($data),
            'is_scheduled' => 1,
            'error_message' => $error_message,
            'error_timestamp' => $error_timestamp,
        )
    );
}

/***
 * Fetch data functions
 * 
 */


 /***
  * Sonarr
  * --------
  * TODO: Fetch the upcoming episodes, missing episodes on demand through the block 
  * Currently this function retrieves the count to limit the data.
  */
  
  /* 
 function homelab_fetch_sonarr_data($api_url, $api_key, $service_id) {
    $url = rtrim($api_url, '/') . '/api/v3/queue?apikey=' . $api_key;
    $response = wp_remote_get($url);

    $error_message = null;
    $error_timestamp = null;

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        $error_timestamp = current_time('mysql');
        return array();
    }

    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        $error_message = "API request failed with status code: $response_code";
        $error_timestamp = current_time('mysql');
        return array();
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);

    $wanted = count($data['records']);
    $queued = count(array_filter($data['records'], function ($item) {
        return $item['status'] === 'queued';
    }));

    $series_url = rtrim($api_url, '/') . '/api/v3/series?apikey=' . $api_key;
    $series_response = wp_remote_get($series_url);
    $series_count = 0;
    $total_episodes = 0;

    if (!is_wp_error($series_response)) {
        $series_data = json_decode(wp_remote_retrieve_body($series_response), true);
        $series_count = count($series_data);

        foreach ($series_data as $series) {
            $total_episodes += $series['statistics']['episodeCount'];
        }
    }

    $upcoming_url = rtrim($api_url, '/') . '/api/v3/calendar?start=0&end=0&apikey=' . $api_key;
    $upcoming_response = wp_remote_get($upcoming_url);
    $upcoming_episodes_count = 0;

    if (!is_wp_error($upcoming_response)) {
        $upcoming_data = json_decode(wp_remote_retrieve_body($upcoming_response), true);
        $upcoming_episodes_count = count($upcoming_data);
    }

    $missing_url = rtrim($api_url, '/') . '/api/v3/wanted/missing?apikey=' . $api_key;
    $missing_response = wp_remote_get($missing_url);
    $missing_episodes_count = 0;

    if (!is_wp_error($missing_response)) {
        $missing_data = json_decode(wp_remote_retrieve_body($missing_response), true);
        $missing_episodes_count = $missing_data['totalRecords'];
    }

    $diskspace_url = rtrim($api_url, '/') . '/api/v3/diskspace?apikey=' . $api_key;
    $diskspace_response = wp_remote_get($diskspace_url);
    $diskspace_usage = 0;

    if (!is_wp_error($diskspace_response)) {
        $diskspace_data = json_decode(wp_remote_retrieve_body($diskspace_response), true);
        foreach ($diskspace_data as $disk) {
            $diskspace_usage += $disk['totalSpace'];
        }
    }

    $fetched_data = array(
        'wanted' => $wanted,
        'queued' => $queued,
        'series' => $series_count,
        'total_episodes' => $total_episodes,
        'upcoming_episodes_count' => $upcoming_episodes_count,
        'missing_episodes_count' => $missing_episodes_count,
        'diskspace_usage' => $diskspace_usage,
    );

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */

/**
 * Radarr
 * ------
 * TODO: Get the upcoming movies, missing movies, on demand via the Block. 
 * Only counts are being fetched to limit the amount of data. 
 */
/* 
function homelab_fetch_radarr_data($api_url, $api_key, $service_id) {
    $url = rtrim($api_url, '/') . '/api/v3/queue?apikey=' . $api_key;
    $response = wp_remote_get($url);

    $error_message = null;
    $error_timestamp = null;

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        $error_timestamp = current_time('mysql');
        return array();
    }

    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        $error_message = "API request failed with status code: $response_code";
        $error_timestamp = current_time('mysql');
        return array();
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);

    $wanted = count($data['records']);
    $queued = count(array_filter($data['records'], function ($item) {
        return $item['status'] === 'queued';
    }));

    $movies_url = rtrim($api_url, '/') . '/api/v3/movie?apikey=' . $api_key;
    $movies_response = wp_remote_get($movies_url);
    $movies_count = 0;

    if (is_wp_error($movies_response)) {
        $error_message = $movies_response->get_error_message();
        $error_timestamp = current_time('mysql');
    } else {
        $movies_data = json_decode(wp_remote_retrieve_body($movies_response), true);
        $movies_count = count($movies_data);
    }

    $fetched_data = array(
        'wanted' => $wanted,
        'queued' => $queued,
        'movies' => $movies_count,
    );

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */

/***
 * Prowlarr
 */
/* function homelab_fetch_prowlarr_data($api_url, $api_key, $service_id) {
    $url = rtrim($api_url, '/') . '/api/v1/indexerstats?apikey=' . $api_key;
    $response = wp_remote_get($url);

    $error_message = null;
    $error_timestamp = null;

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        $error_timestamp = current_time('mysql');
        return array();
    }

    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        $error_message = "API request failed with status code: $response_code";
        $error_timestamp = current_time('mysql');
        return array();
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);

    $grabs_count = $data['numberOfGrabs'];
    $queries_count = $data['numberOfQueries'];
    $fail_grabs_count = $data['numberOfFailedGrabs'];
    $fail_queries_count = $data['numberOfFailedQueries'];

    $indexers_url = rtrim($api_url, '/') . '/api/v1/indexer?apikey=' . $api_key;
    $indexers_response = wp_remote_get($indexers_url);
    $indexer_statuses = array();
    $total_indexers = 0;

    if (!is_wp_error($indexers_response)) {
        $indexers_data = json_decode(wp_remote_retrieve_body($indexers_response), true);
        foreach ($indexers_data as $indexer) {
            $indexer_statuses[] = array(
                'name' => $indexer['name'],
                'enabled' => $indexer['enable'],
                'status' => $indexer['status'],
            );
        }
        $total_indexers = count($indexers_data);
    }

    $priorities_url = rtrim($api_url, '/') . '/api/v1/indexer/priority?apikey=' . $api_key;
    $priorities_response = wp_remote_get($priorities_url);
    $indexer_priority = array();

    if (!is_wp_error($priorities_response)) {
        $priorities_data = json_decode(wp_remote_retrieve_body($priorities_response), true);
        $indexer_priority = $priorities_data;
    }

    $fetched_data = array(
        'grabs' => $grabs_count,
        'queries' => $queries_count,
        'fail_grabs' => $fail_grabs_count,
        'fail_queries' => $fail_queries_count,
        'indexer_statuses' => $indexer_statuses,
        'total_indexers' => $total_indexers,
        'indexer_priority' => $indexer_priority,
    );

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */

/***
 * Lidarr
 * ------
 * TODO: Upcoming albums, Missing albums are only counts, Implement the list through the block so that it is fetched on demand
 * to limit the amount of data.
 * Users can add the block of they want this to be displayed.
 * 
 */

 /* 
function homelab_fetch_lidarr_data($api_url, $api_key, $service_id) {
    $url = rtrim($api_url, '/') . '/api/v1/queue?apikey=' . $api_key;
    $response = wp_remote_get($url);

    $error_message = null;
    $error_timestamp = null;

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        $error_timestamp = current_time('mysql');
        return array();
    }

    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        $error_message = "API request failed with status code: $response_code";
        $error_timestamp = current_time('mysql');
        return array();
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);

    $wanted = count($data['records']);
    $queued = count(array_filter($data['records'], function ($item) {
        return $item['status'] === 'queued';
    }));

    $artists_url = rtrim($api_url, '/') . '/api/v1/artist?apikey=' . $api_key;
    $artists_response = wp_remote_get($artists_url);
    $artists_count = 0;

    if (!is_wp_error($artists_response)) {
        $artists_data = json_decode(wp_remote_retrieve_body($artists_response), true);
        $artists_count = count($artists_data);
    }

    $upcoming_url = rtrim($api_url, '/') . '/api/v1/album/upcoming?start=0&end=0&includeAllArtists=true&apikey=' . $api_key;
    $upcoming_response = wp_remote_get($upcoming_url);
    $upcoming_albums_count = 0;

    if (!is_wp_error($upcoming_response)) {
        $upcoming_data = json_decode(wp_remote_retrieve_body($upcoming_response), true);
        $upcoming_albums_count = count($upcoming_data);
    }

    $missing_url = rtrim($api_url, '/') . '/api/v1/wanted/missing?apikey=' . $api_key;
    $missing_response = wp_remote_get($missing_url);
    $missing_albums_count = 0;

    if (!is_wp_error($missing_response)) {
        $missing_data = json_decode(wp_remote_retrieve_body($missing_response), true);
        $missing_albums_count = $missing_data['totalRecords'];
    }

    $tracks_url = rtrim($api_url, '/') . '/api/v1/track?apikey=' . $api_key;
    $tracks_response = wp_remote_get($tracks_url);
    $total_tracks = 0;

    if (!is_wp_error($tracks_response)) {
        $tracks_data = json_decode(wp_remote_retrieve_body($tracks_response), true);
        $total_tracks = count($tracks_data);
    }

    $diskspace_url = rtrim($api_url, '/') . '/api/v1/diskspace?apikey=' . $api_key;
    $diskspace_response = wp_remote_get($diskspace_url);
    $diskspace_usage = 0;

    if (!is_wp_error($diskspace_response)) {
        $diskspace_data = json_decode(wp_remote_retrieve_body($diskspace_response), true);
        foreach ($diskspace_data as $disk) {
            $diskspace_usage += $disk['totalSpace'];
        }
    }

    $fetched_data = array(
        'wanted' => $wanted,
        'queued' => $queued,
        'artists' => $artists_count,
        'upcoming_albums_count' => $upcoming_albums_count,
        'missing_albums_count' => $missing_albums_count,
        'total_tracks' => $total_tracks,
        'diskspace_usage' => $diskspace_usage,
    );

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */
/***
 * TODO: Missing books count is being retrieved, a list can be implemented through the book for ondemand 
 * 
 * 
 */
/* function homelab_fetch_readarr_data($api_url, $api_key, $service_id) {
    $url = rtrim($api_url, '/') . '/api/v1/queue?apikey=' . $api_key;
    $response = wp_remote_get($url);

    $error_message = null;
    $error_timestamp = null;

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        $error_timestamp = current_time('mysql');
        return array();
    }

    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        $error_message = "API request failed with status code: $response_code";
        $error_timestamp = current_time('mysql');
        return array();
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);

    $wanted = count($data['records']);
    $queued = count(array_filter($data['records'], function ($item) {
        return $item['status'] === 'queued';
    }));

    $books_url = rtrim($api_url, '/') . '/api/v1/book?apikey=' . $api_key;
    $books_response = wp_remote_get($books_url);
    $books_count = 0;

    if (!is_wp_error($books_response)) {
        $books_data = json_decode(wp_remote_retrieve_body($books_response), true);
        $books_count = count($books_data);
    }

    $upcoming_url = rtrim($api_url, '/') . '/api/v1/book/upcoming?apikey=' . $api_key;
    $upcoming_response = wp_remote_get($upcoming_url);
    $upcoming_books_count = 0;

    if (!is_wp_error($upcoming_response)) {
        $upcoming_data = json_decode(wp_remote_retrieve_body($upcoming_response), true);
        $upcoming_books_count = count($upcoming_data);
    }

    $missing_url = rtrim($api_url, '/') . '/api/v1/wanted/missing?apikey=' . $api_key;
    $missing_response = wp_remote_get($missing_url);
    $missing_books_count = 0;

    if (!is_wp_error($missing_response)) {
        $missing_data = json_decode(wp_remote_retrieve_body($missing_response), true);
        $missing_books_count = $missing_data['totalRecords'];
    }

    $authors_url = rtrim($api_url, '/') . '/api/v1/author?apikey=' . $api_key;
    $authors_response = wp_remote_get($authors_url);
    $total_authors = 0;

    if (!is_wp_error($authors_response)) {
        $authors_data = json_decode(wp_remote_retrieve_body($authors_response), true);
        $total_authors = count($authors_data);
    }

    $diskspace_url = rtrim($api_url, '/') . '/api/v1/diskspace?apikey=' . $api_key;
    $diskspace_response = wp_remote_get($diskspace_url);
    $diskspace_usage = 0;

    if (!is_wp_error($diskspace_response)) {
        $diskspace_data = json_decode(wp_remote_retrieve_body($diskspace_response), true);
        foreach ($diskspace_data as $disk) {
            $diskspace_usage += $disk['totalSpace'];
        }
    }

    $fetched_data = array(
        'wanted' => $wanted,
        'queued' => $queued,
        'books' => $books_count,
        'upcoming_books_count' => $upcoming_books_count,
        'missing_books_count' => $missing_books_count,
        'total_authors' => $total_authors,
        'diskspace_usage' => $diskspace_usage,
    );

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */

/***
 * Tdarr
 * -----
 * TODO: 
 * 
 */
/* function homelab_fetch_tdarr_data($api_url, $api_key, $service_id) {
    $url = rtrim($api_url, '/') . '/api/v2/status?apikey=' . $api_key;
    $response = wp_remote_get($url);

    $error_message = null;
    $error_timestamp = null;

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        $error_timestamp = current_time('mysql');
        return array();
    }

    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        $error_message = "API request failed with status code: $response_code";
        $error_timestamp = current_time('mysql');
        return array();
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);

    $queue = $data['queue'];
    $processed = $data['processed'];
    $errored = $data['errored'];
    $saved = $data['saved'];

    $libraries_url = rtrim($api_url, '/') . '/api/v2/libraries?apikey=' . $api_key;
    $libraries_response = wp_remote_get($libraries_url);
    $total_libraries = 0;

    if (!is_wp_error($libraries_response)) {
        $libraries_data = json_decode(wp_remote_retrieve_body($libraries_response), true);
        $total_libraries = count($libraries_data);
    }

    $nodes_url = rtrim($api_url, '/') . '/api/v2/nodes?apikey=' . $api_key;
    $nodes_response = wp_remote_get($nodes_url);
    $node_statuses = array();

    if (!is_wp_error($nodes_response)) {
        $nodes_data = json_decode(wp_remote_retrieve_body($nodes_response), true);
        foreach ($nodes_data as $node) {
            $node_statuses[] = array(
                'name' => $node['name'],
                'status' => $node['status'],
            );
        }
    }

    $transcode_url = rtrim($api_url, '/') . '/api/v2/transcode-cache?apikey=' . $api_key;
    $transcode_response = wp_remote_get($transcode_url);
    $current_transcode = null;

    if (!is_wp_error($transcode_response)) {
        $transcode_data = json_decode(wp_remote_retrieve_body($transcode_response), true);
        if (!empty($transcode_data)) {
            $current_transcode = $transcode_data[0]['file'];
        }
    }

    $history_url = rtrim($api_url, '/') . '/api/v2/history?take=5&apikey=' . $api_key;
    $history_response = wp_remote_get($history_url);
    $transcode_history = array();

    if (!is_wp_error($history_response)) {
        $history_data = json_decode(wp_remote_retrieve_body($history_response), true);
        $transcode_history = $history_data;
    }

    $fetched_data = array(
        'queue' => $queue,
        'processed' => $processed,
        'errored' => $errored,
        'saved' => $saved,
        'total_libraries' => $total_libraries,
        'node_statuses' => $node_statuses,
        'current_transcode' => $current_transcode,
        'transcode_history' => $transcode_history,
    );

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */

/***
 * Overseer
 * --------
 */

/* function homelab_fetch_overseerr_data($api_url, $api_key, $service_id) {
    $url = rtrim($api_url, '/') . '/api/v1/request?take=0&apikey=' . $api_key;
    $response = wp_remote_get($url);

    $error_message = null;
    $error_timestamp = null;

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        $error_timestamp = current_time('mysql');
        return array();
    }

    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        $error_message = "API request failed with status code: $response_code";
        $error_timestamp = current_time('mysql');
        return array();
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);

    $pending = $data['pageInfo']['results'];
    $processing = 0;
    $approved = 0;
    $available = 0;

    foreach ($data['results'] as $request) {
        switch ($request['status']) {
            case 1:
                $processing++;
                break;
            case 2:
                $approved++;
                break;
            case 5:
                $available++;
                break;
        }
    }

    $users_url = rtrim($api_url, '/') . '/api/v1/user?take=0&apikey=' . $api_key;
    $users_response = wp_remote_get($users_url);
    $total_users = 0;

    if (!is_wp_error($users_response)) {
        $users_data = json_decode(wp_remote_retrieve_body($users_response), true);
        $total_users = $users_data['pageInfo']['results'];
    }

    $pending_requests_url = rtrim($api_url, '/') . '/api/v1/request?take=0&filter=pending&apikey=' . $api_key;
    $pending_requests_response = wp_remote_get($pending_requests_url);
    $pending_requests_count = 0;

    if (!is_wp_error($pending_requests_response)) {
        $pending_requests_data = json_decode(wp_remote_retrieve_body($pending_requests_response), true);
        $pending_requests_count = $pending_requests_data['pageInfo']['results'];
    }

    $recently_added_url = rtrim($api_url, '/') . '/api/v1/media?take=5&sort=added&apikey=' . $api_key;
    $recently_added_response = wp_remote_get($recently_added_url);
    $recently_added_media = array();

    if (!is_wp_error($recently_added_response)) {
        $recently_added_data = json_decode(wp_remote_retrieve_body($recently_added_response), true);
        $recently_added_media = $recently_added_data['results'];
    }

    $user_stats_url = rtrim($api_url, '/') . '/api/v1/request/stats?apikey=' . $api_key;
    $user_stats_response = wp_remote_get($user_stats_url);
    $user_request_stats = array();

    if (!is_wp_error($user_stats_response)) {
        $user_stats_data = json_decode(wp_remote_retrieve_body($user_stats_response), true);
        $user_request_stats = $user_stats_data;
    }

    $fetched_data = array(
        'pending' => $pending,
        'approved' => $approved,
        'available' => $available,
        'processing' => $processing,
        'total_users' => $total_users,
        'pending_requests_count' => $pending_requests_count,
        'recently_added_media' => $recently_added_media,
        'user_request_stats' => $user_request_stats,
    );

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */

/***
 * Deluge
 * ------
 * The data points that can be displayed on the dashboard for Deluge include:
 *
 * Download rate: The current download speed.
 * Upload rate: The current upload speed.
 * Total download: The total amount of data downloaded.
 * Total upload: The total amount of data uploaded.
 * Leech count: The number of torrents currently leeching (downloading).
 * Seed count: The number of torrents currently seeding (uploading).
 * Paused count: The number of paused torrents.
 */


 /* function homelab_fetch_deluge_data($api_url, $api_key, $service_id) {
    $url = rtrim($api_url, '/') . '/json';
    $body = json_encode(array(
        'method' => 'web.update_ui',
        'params' => array(
            array(
                'stats',
                'torrents',
            ),
        ),
        'id' => 1,
    ));

    $response = wp_remote_post($url, array(
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
        'body' => $body,
    ));

    $error_message = null;
    $error_timestamp = null;

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        $error_timestamp = current_time('mysql');
        return array();
    }

    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        $error_message = "API request failed with status code: $response_code";
        $error_timestamp = current_time('mysql');
        return array();
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);

    $stats = $data['result']['stats'];
    $torrents = $data['result']['torrents'];

    $download_rate = $stats['download_rate'];
    $upload_rate = $stats['upload_rate'];
    $total_download = $stats['total_download'];
    $total_upload = $stats['total_upload'];

    $leech_count = 0;
    $seed_count = 0;
    $paused_count = 0;

    foreach ($torrents as $torrent) {
        if ($torrent['state'] === 'Seeding') {
            $seed_count++;
        } elseif ($torrent['state'] === 'Downloading') {
            $leech_count++;
        } elseif ($torrent['state'] === 'Paused') {
            $paused_count++;
        }
    }

    $fetched_data = array(
        'download_rate' => $download_rate,
        'upload_rate' => $upload_rate,
        'total_download' => $total_download,
        'total_upload' => $total_upload,
        'leech_count' => $leech_count,
        'seed_count' => $seed_count,
        'paused_count' => $paused_count,
    );

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */

/***
 * SabNZBD
 */

 function homelab_fetch_sabnzbd_data($api_url, $api_key, $service_id) {
    $url = rtrim($api_url, '/') . '/api?mode=queue&output=json&apikey=' . $api_key;
    $response = wp_remote_get($url);

    $error_message = null;
    $error_timestamp = null;

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        $error_timestamp = current_time('mysql');
        return array();
    }

    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        $error_message = "API request failed with status code: $response_code";
        $error_timestamp = current_time('mysql');
        return array();
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);

    $speed = $data['queue']['speed'];
    $size_left = $data['queue']['mbleft'];
    $time_left = $data['queue']['timeleft'];
    $queue_size = $data['queue']['noofslots_total'];

    $speed_formatted = homelab_format_speed($speed);
    $size_left_formatted = homelab_format_size($size_left);
    $time_left_formatted = homelab_format_time($time_left);

    $fetched_data = array(
        'speed' => $speed_formatted,
        'size_left' => $size_left_formatted,
        'time_left' => $time_left_formatted,
        'queue_size' => $queue_size,
    );

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}

function homelab_format_speed($speed) {
    $units = array('B/s', 'KB/s', 'MB/s', 'GB/s');
    $unit = 0;

    while ($speed >= 1024 && $unit < count($units) - 1) {
        $speed /= 1024;
        $unit++;
    }

    return round($speed, 2) . ' ' . $units[$unit];
}

function homelab_format_size($size) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $unit = 0;

    while ($size >= 1024 && $unit < count($units) - 1) {
        $size /= 1024;
        $unit++;
    }

    return round($size, 2) . ' ' . $units[$unit];
}

function homelab_format_time($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $seconds = $seconds % 60;

    return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
}

/***
 * PiHole
 * ------
 * The data points that can be displayed on the dashboard for Pi-hole include:
 *
 * DNS Queries Today: The total number of DNS queries made today.
 * Ads Blocked Today: The number of ads blocked today.
 * Ads Percentage Today: The percentage of ads blocked today.
 * Domains Being Blocked: The number of domains being blocked by Pi-hole.
 * Queries Forwarded: The number of queries forwarded to upstream DNS servers.
 * Queries Cached: The number of queries served from Pi-hole's cache.
 * Clients Ever Seen: The total number of clients that have been seen by Pi-hole.
 * Unique Clients: The number of unique clients that have made queries to Pi-hole.
 * DNS Queries (All Types): The breakdown of DNS query types.
 * Recently Blocked Domains: The list of recently blocked domains.
 */
function homelab_fetch_pihole_data($api_url, $api_key, $service_id) {
    $url = rtrim($api_url, '/') . '/admin/api.php?summary&auth=' . $api_key;
    $response = wp_remote_get($url);

    $error_message = null;
    $error_timestamp = null;

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        $error_timestamp = current_time('mysql');
        return array();
    }

    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        $error_message = "API request failed with status code: $response_code";
        $error_timestamp = current_time('mysql');
        return array();
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);

    $dns_queries_today = $data['dns_queries_today'];
    $ads_blocked_today = $data['ads_blocked_today'];
    $ads_percentage_today = $data['ads_percentage_today'];
    $domains_being_blocked = $data['domains_being_blocked'];
    $queries_forwarded = $data['queries_forwarded'];
    $queries_cached = $data['queries_cached'];
    $clients_ever_seen = $data['clients_ever_seen'];
    $unique_clients = $data['unique_clients'];
    $dns_queries_all_types = $data['dns_queries_all_types'];
    $reply_NODATA = $data['reply_NODATA'];
    $reply_NXDOMAIN = $data['reply_NXDOMAIN'];
    $reply_CNAME = $data['reply_CNAME'];
    $reply_IP = $data['reply_IP'];
    $privacy_level = $data['privacy_level'];
    $status = $data['status'];

    $gravity_url = rtrim($api_url, '/') . '/admin/api.php?recentBlocked=100&auth=' . $api_key;
    $gravity_response = wp_remote_get($gravity_url);

    if (!is_wp_error($gravity_response) && wp_remote_retrieve_response_code($gravity_response) === 200) {
        $gravity_data = json_decode(wp_remote_retrieve_body($gravity_response), true);
        $recently_blocked = $gravity_data['data'];
    } else {
        $recently_blocked = array();
    }

    $fetched_data = array(
        'dns_queries_today' => $dns_queries_today,
        'ads_blocked_today' => $ads_blocked_today,
        'ads_percentage_today' => $ads_percentage_today,
        'domains_being_blocked' => $domains_being_blocked,
        'queries_forwarded' => $queries_forwarded,
        'queries_cached' => $queries_cached,
        'clients_ever_seen' => $clients_ever_seen,
        'unique_clients' => $unique_clients,
        'dns_queries_all_types' => $dns_queries_all_types,
        'reply_NODATA' => $reply_NODATA,
        'reply_NXDOMAIN' => $reply_NXDOMAIN,
        'reply_CNAME' => $reply_CNAME,
        'reply_IP' => $reply_IP,
        'privacy_level' => $privacy_level,
        'status' => $status,
        'recently_blocked' => $recently_blocked,
    );

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}

/***
 * Portainer 
 * ---------
 * The data points collected in this implementation are:
 * 
 * Running Containers: The number of containers currently running.
 * Stopped Containers: The number of containers that are stopped.
 * Total Containers: The total number of containers, both running and stopped.
 * Total Images: The total number of Docker images.
 * Total Volumes: The total number of Docker volumes.
 * Total Networks: The total number of Docker networks.
 */

 function homelab_fetch_portainer_data($api_url, $api_key, $env_id, $service_id) {
    $endpoints_url = rtrim($api_url, '/') . '/api/endpoints/' . $env_id . '/docker/containers/json';
    $response = wp_remote_get($endpoints_url, array(
        'headers' => array(
            'X-API-Key' => $api_key,
        ),
    ));

    $error_message = null;
    $error_timestamp = null;

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        $error_timestamp = current_time('mysql');
        return array();
    }

    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        $error_message = "API request failed with status code: $response_code";
        $error_timestamp = current_time('mysql');
        return array();
    }

    $containers_data = json_decode(wp_remote_retrieve_body($response), true);

    $running_containers = 0;
    $stopped_containers = 0;
    $total_containers = count($containers_data);

    foreach ($containers_data as $container) {
        if ($container['State'] === 'running') {
            $running_containers++;
        } else {
            $stopped_containers++;
        }
    }

    $stats_url = rtrim($api_url, '/') . '/api/endpoints/' . $env_id . '/docker/info';
    $stats_response = wp_remote_get($stats_url, array(
        'headers' => array(
            'X-API-Key' => $api_key,
        ),
    ));

    if (!is_wp_error($stats_response) && wp_remote_retrieve_response_code($stats_response) === 200) {
        $stats_data = json_decode(wp_remote_retrieve_body($stats_response), true);
        $total_images = $stats_data['Images'];
        $total_volumes = $stats_data['Volumes'];
        $total_networks = $stats_data['Networks'];
    } else {
        $total_images = 0;
        $total_volumes = 0;
        $total_networks = 0;
    }

    $fetched_data = array(
        'running_containers' => $running_containers,
        'stopped_containers' => $stopped_containers,
        'total_containers' => $total_containers,
        'total_images' => $total_images,
        'total_volumes' => $total_volumes,
        'total_networks' => $total_networks,
    );

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}

/***
 * Synology Disk Station
 * ---------------------
 * Total Shares: The total number of shares on the Synology DiskStation.
Total Size: The total storage capacity of the specified volume (if provided).
Used Size: The used storage space of the specified volume (if provided).
Free Size: The free storage space of the specified volume (if provided).
Used Percentage: The percentage of used storage space of the specified volume (if provided).
 * Uptime: The uptime of the Synology DiskStation in seconds.
Volume Count: The total number of available volumes on the Synology DiskStation.
CPU Usage: The current CPU usage percentage.
Memory Usage: The current memory usage percentage.
 */

 /* function homelab_fetch_synology_data($api_url, $username, $password, $volume, $service_id) {
    $auth_url = rtrim($api_url, '/') . '/webapi/auth.cgi?api=SYNO.API.Auth&version=3&method=login&account=' . $username . '&passwd=' . $password . '&session=homelab_session';
    $auth_response = wp_remote_get($auth_url);

    $error_message = null;
    $error_timestamp = null;

    if (is_wp_error($auth_response)) {
        $error_message = $auth_response->get_error_message();
        $error_timestamp = current_time('mysql');
        return array();
    }

    $auth_response_code = wp_remote_retrieve_response_code($auth_response);
    if ($auth_response_code !== 200) {
        $error_message = "Authentication failed with status code: $auth_response_code";
        $error_timestamp = current_time('mysql');
        return array();
    }

    $auth_data = json_decode(wp_remote_retrieve_body($auth_response), true);
    $sid = $auth_data['data']['sid'];

    $stats_url = rtrim($api_url, '/') . '/webapi/entry.cgi?api=SYNO.FileStation.List&version=2&method=list_share&additional=size,owner,time,perm,mount_point_type,volume_status&sid=' . $sid;
    $stats_response = wp_remote_get($stats_url);

    if (is_wp_error($stats_response)) {
        $error_message = $stats_response->get_error_message();
        $error_timestamp = current_time('mysql');
        return array();
    }

    $stats_response_code = wp_remote_retrieve_response_code($stats_response);
    if ($stats_response_code !== 200) {
        $error_message = "API request failed with status code: $stats_response_code";
        $error_timestamp = current_time('mysql');
        return array();
    }

    $stats_data = json_decode(wp_remote_retrieve_body($stats_response), true);
    $total_shares = count($stats_data['data']['shares']);

    $system_url = rtrim($api_url, '/') . '/webapi/entry.cgi?api=SYNO.Core.System&version=1&method=info&sid=' . $sid;
    $system_response = wp_remote_get($system_url);

    if (!is_wp_error($system_response) && wp_remote_retrieve_response_code($system_response) === 200) {
        $system_data = json_decode(wp_remote_retrieve_body($system_response), true);
        $uptime = $system_data['data']['uptime'];
    } else {
        $uptime = 0;
    }

    $volume_url = rtrim($api_url, '/') . '/webapi/entry.cgi?api=SYNO.Storage.Volume&version=1&method=list&sid=' . $sid;
    $volume_response = wp_remote_get($volume_url);

    if (!is_wp_error($volume_response) && wp_remote_retrieve_response_code($volume_response) === 200) {
        $volume_data = json_decode(wp_remote_retrieve_body($volume_response), true);
        $volumes = $volume_data['data']['volumes'];
        $volume_count = count($volumes);
    } else {
        $volume_count = 0;
    }

    $resource_url = rtrim($api_url, '/') . '/webapi/entry.cgi?api=SYNO.Core.System.Utilization&version=1&method=get&type=current&resource=cpu,memory&sid=' . $sid;
    $resource_response = wp_remote_get($resource_url);

    if (!is_wp_error($resource_response) && wp_remote_retrieve_response_code($resource_response) === 200) {
        $resource_data = json_decode(wp_remote_retrieve_body($resource_response), true);
        $cpu_usage = $resource_data['data']['cpu']['user_load'];
        $memory_usage = $resource_data['data']['memory']['real_usage'];
    } else {
        $cpu_usage = 0;
        $memory_usage = 0;
    }

    $volume_url = rtrim($api_url, '/') . '/webapi/entry.cgi?api=SYNO.FileStation.VolumeStatus&version=1&method=list&additional=size_total,size_used,size_free,percentage_used&volume_path=' . $volume . '&sid=' . $sid;
    $volume_response = wp_remote_get($volume_url);

    if (!is_wp_error($volume_response) && wp_remote_retrieve_response_code($volume_response) === 200) {
        $volume_data = json_decode(wp_remote_retrieve_body($volume_response), true);
        $volume_info = $volume_data['data']['volumes'][0];
        $total_size = $volume_info['size_total'];
        $used_size = $volume_info['size_used'];
        $free_size = $volume_info['size_free'];
        $used_percentage = $volume_info['percentage_used'];
    } else {
        $total_size = 0;
        $used_size = 0;
        $free_size = 0;
        $used_percentage = 0;
    }

    $fetched_data = array(
        'total_shares' => $total_shares,
        'total_size' => $total_size,
        'used_size' => $used_size,
        'free_size' => $free_size,
        'used_percentage' => $used_percentage,
        'uptime' => $uptime,
        'volume_count' => $volume_count,
        'cpu_usage' => $cpu_usage,
        'memory_usage' => $memory_usage,
    );

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */

/***
 * Synology Download Station
 * -------------------------
 * 
 */

 /* function homelab_fetch_synology_download_station_data($api_url, $username, $password, $service_id) {
    
    $auth_url = rtrim($api_url, '/') . '/webapi/auth.cgi?api=SYNO.API.Auth&version=3&method=login&account=' . $username . '&passwd=' . $password . '&session=homelab_session';
    $auth_response = wp_remote_get($auth_url);

    $error_message = null;
    $error_timestamp = null;

    if (is_wp_error($auth_response)) {
        $error_message = $auth_response->get_error_message();
        $error_timestamp = current_time('mysql');
        return array();
    }

    $auth_response_code = wp_remote_retrieve_response_code($auth_response);
    if ($auth_response_code !== 200) {
        $error_message = "Authentication failed with status code: $auth_response_code";
        $error_timestamp = current_time('mysql');
        return array();
    }

    $auth_data = json_decode(wp_remote_retrieve_body($auth_response), true);
    $sid = $auth_data['data']['sid'];

    $task_url = rtrim($api_url, '/') . '/webapi/entry.cgi?api=SYNO.DownloadStation.Task&version=1&method=list&additional=detail,transfer,tracker&sid=' . $sid;
    $task_response = wp_remote_get($task_url);

    $error_message = null;
    $error_timestamp = null;

    if (is_wp_error($task_response)) {
        $error_message = $task_response->get_error_message();
        $error_timestamp = current_time('mysql');
        return array();
    }

    $task_response_code = wp_remote_retrieve_response_code($task_response);
    if ($task_response_code !== 200) {
        $error_message = "API request failed with status code: $task_response_code";
        $error_timestamp = current_time('mysql');
        return array();
    }

    $task_data = json_decode(wp_remote_retrieve_body($task_response), true);
    $tasks = $task_data['data']['tasks'];

    $active_downloads = 0;
    $waiting_downloads = 0;
    $completed_downloads = 0;
    $total_downloads = count($tasks);

    $total_download_speed = 0;
    $total_upload_speed = 0;

    foreach ($tasks as $task) {
        if ($task['status'] === 'downloading') {
            $active_downloads++;
            $total_download_speed += $task['additional']['transfer']['speed_download'];
        } elseif ($task['status'] === 'waiting') {
            $waiting_downloads++;
        } elseif ($task['status'] === 'finished') {
            $completed_downloads++;
        }

        $total_upload_speed += $task['additional']['transfer']['speed_upload'];
    }

    $fetched_data = array(
        'active_downloads' => $active_downloads,
        'waiting_downloads' => $waiting_downloads,
        'completed_downloads' => $completed_downloads,
        'total_downloads' => $total_downloads,
        'total_download_speed' => $total_download_speed,
        'total_upload_speed' => $total_upload_speed,
    );

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */

/**********
 * AdGuard Home Data Collection
 * ----------------------------
 * This function collects data from the AdGuard Home API for dashboard display, incorporating authentication with a username and password.
 * It fetches statistics on the total number of DNS queries, the number of blocked queries, the percentage of queries blocked, the number
 * of active DNS filters, and the number of URL rewrite rules.
 * 
 * Data not collected but available for extension:
 * - Detailed info on blocked services
 * - Top queried domains
 * - Top blocked domains
 * - Information on clients making the most queries
 * - System status and resource usage (CPU, RAM, etc.)
 * 
 * Authentication:
 * The function uses basic HTTP authentication to access the AdGuard Home API, requiring a username and password.
 ***********/

/*  function fetch_adguard_home_data($api_url, $username, $password, $service_id) {
    // Encode the username and password for basic auth
    $auth = base64_encode("$username:$password");

    // Construct the URL for the statistics endpoint
    $stats_url = rtrim($api_url, '/') . '/control/stats';

    // Make the request to the AdGuard Home API with the Authorization header
    $response = wp_remote_get($stats_url, array(
        'headers' => array(
            'Authorization' => 'Basic ' . $auth,
        ),
    ));

    // Initialize error handling variables
    $error_message = null;
    $error_timestamp = null;

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        $error_timestamp = current_time('mysql');
    } else {
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            $error_message = "API request failed with status code: $response_code";
            $error_timestamp = current_time('mysql');
        }
    }

    // Initialize the array to hold the fetched data
    $adguard_data = array(
        'total_queries' => 0,
        'blocked_queries' => 0,
        'percentage_blocked' => 0,
        'active_dns_filters' => 0,
        'rewrite_rules' => 0,
    );

    if (empty($error_message)) {
        // Decode the response body into an associative array
        $stats_data = json_decode(wp_remote_retrieve_body($response), true);

        // Extract required data
        $total_queries = $stats_data['num_dns_queries'];
        $blocked_queries = $stats_data['num_blocked_filtering'];
        $percentage_blocked = round(($blocked_queries / $total_queries) * 100, 2);

        // Assuming another endpoint for filter status: /control/filtering/status
        $filter_status_url = rtrim($api_url, '/') . '/control/filtering/status';
        $filter_response = wp_remote_get($filter_status_url, array(
            'headers' => array(
                'Authorization' => 'Basic ' . $auth,
            ),
        ));

        if (!is_wp_error($filter_response) && wp_remote_retrieve_response_code($filter_response) === 200) {
            $filter_data = json_decode(wp_remote_retrieve_body($filter_response), true);
            $active_dns_filters = count($filter_data['filters']);
            $rewrite_rules = $filter_data['num_rewrites'];
        }

        // Prepare the data for saving or displaying
        $adguard_data = array(
            'total_queries' => $total_queries,
            'blocked_queries' => $blocked_queries,
            'percentage_blocked' => $percentage_blocked,
            'active_dns_filters' => $active_dns_filters,
            'rewrite_rules' => $rewrite_rules,
        );
    }

    // Save the fetched data along with any error messages or timestamps
    homelab_save_service_data($service_id, $adguard_data, $error_message, $error_timestamp);

    return $adguard_data;
} */

/**********
 * Audiobookshelf Data Collection
 * -------------------------------
 * This function collects statistics from the Audiobookshelf API for dashboard display, using an API Key for authentication.
 * It fetches counts and total durations for both podcasts and books.
 * 
 * Collected Data:
 * - Number of podcasts
 * - Total duration of all podcasts
 * - Number of books
 * - Total duration of all books
 * 
 * Data not collected but available for extension:
 * - Number of users and their activity
 * - Detailed genre or category statistics for books and podcasts
 * - Recent additions to the library
 * - Data on most listened books or podcasts
 * - Storage usage statistics
 * 
 * Authentication:
 * The function uses an API Key passed in the header for authentication.
 * 
 * Error Handling:
 * - Captures any errors encountered during the API request process.
 * - Stores error messages and timestamps for troubleshooting.
 ***********/
/* 
 function fetch_audiobookshelf_stats($api_url, $api_key, $service_id) {
    $headers = array(
        'headers' => array(
            'X-API-KEY' => $api_key,
        ),
    );

    $error_message = null;
    $error_timestamp = null;

    $stats = [
        'podcasts_count' => 0,
        'podcasts_duration' => 0,
        'books_count' => 0,
        'books_duration' => 0,
    ];

    // Fetch podcasts statistics
    $podcasts_response = wp_remote_get(rtrim($api_url, '/') . '/api/podcasts', $headers);
    if (is_wp_error($podcasts_response)) {
        $error_message = $podcasts_response->get_error_message();
        $error_timestamp = current_time('mysql');
    } elseif (wp_remote_retrieve_response_code($podcasts_response) !== 200) {
        $error_message = "API request for podcasts failed with status code: " . wp_remote_retrieve_response_code($podcasts_response);
        $error_timestamp = current_time('mysql');
    } else {
        $podcasts_data = json_decode(wp_remote_retrieve_body($podcasts_response), true);
        $stats['podcasts_count'] = count($podcasts_data);
        foreach ($podcasts_data as $podcast) {
            $stats['podcasts_duration'] += $podcast['duration'];
        }
    }

    // If there was an error with podcasts, don't proceed to fetch books
    if ($error_message === null) {
        // Fetch books statistics
        $books_response = wp_remote_get(rtrim($api_url, '/') . '/api/books', $headers);
        if (is_wp_error($books_response)) {
            $error_message = $books_response->get_error_message();
            $error_timestamp = current_time('mysql');
        } elseif (wp_remote_retrieve_response_code($books_response) !== 200) {
            $error_message = "API request for books failed with status code: " . wp_remote_retrieve_response_code($books_response);
            $error_timestamp = current_time('mysql');
        } else {
            $books_data = json_decode(wp_remote_retrieve_body($books_response), true);
            $stats['books_count'] = count($books_data);
            foreach ($books_data as $book) {
                $stats['books_duration'] += $book['duration'];
            }
        }
    }

    // Save the collected data, along with any error message or timestamp
    homelab_save_service_data($service_id, $stats, $error_message, $error_timestamp);

    return $stats;
} */

/******************
 * Authentik Data Collection
 * -------------------------------
 * This function collects statistics from the Authentik API for dashboard display, using an API Key for authentication.
 * It fetches user counts, login counts, and failed login counts for the last 24 hours.
 *
 * Collected Data:
 * - Total number of users
 * - Number of logins in the last 24 hours
 * - Number of failed logins in the last 24 hours
 *
 * Data not collected but available for extension:
 * - User activity and session details
 * - Application and provider specific login statistics
 * - Detailed user profile information
 * - Authentication flow and policy data
 * - Audit logs and event timestamps
 *
 * Authentication:
 * The function uses an API Key passed in the header for authentication.
 *
 * Error Handling:
 * - Captures any errors encountered during the API request process.
 * - Stores error messages and timestamps for troubleshooting.
 *******************/

 /* function homelab_fetch_authentik_data($api_url, $api_key, $service_id) {
    $users_url = rtrim($api_url, '/') . '/api/v3/core/users/';
    $logins_url = rtrim($api_url, '/') . '/api/v3/core/events/logins/?ordering=-timestamp&limit=1';
    $failed_logins_url = rtrim($api_url, '/') . '/api/v3/core/events/failed_logins/?ordering=-timestamp&limit=1';

    $headers = array(
        'Authorization' => 'Bearer ' . $api_key,
        'Accept' => 'application/json',
    );

    $users_response = wp_remote_get($users_url, array('headers' => $headers));
    $logins_response = wp_remote_get($logins_url, array('headers' => $headers));
    $failed_logins_response = wp_remote_get($failed_logins_url, array('headers' => $headers));

    $error_message = null;
    $error_timestamp = null;

    if (is_wp_error($users_response) || is_wp_error($logins_response) || is_wp_error($failed_logins_response)) {
        $error_message = "API request failed: " . $users_response->get_error_message() . ", " . $logins_response->get_error_message() . ", " . $failed_logins_response->get_error_message();
        $error_timestamp = current_time('mysql');
        return array();
    }

    $users_data = json_decode(wp_remote_retrieve_body($users_response), true);
    $logins_data = json_decode(wp_remote_retrieve_body($logins_response), true);
    $failed_logins_data = json_decode(wp_remote_retrieve_body($failed_logins_response), true);

    $total_users = $users_data['count'];
    $logins_last_24h = $logins_data['count'];
    $failed_logins_last_24h = $failed_logins_data['count'];

    $fetched_data = array(
        'total_users' => $total_users,
        'logins_last_24h' => $logins_last_24h,
        'failed_logins_last_24h' => $failed_logins_last_24h,
    );

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */

/******************
 * AutoBrr Data Collection
 * -------------------------------
 * This function collects statistics from the AutoBrr API for dashboard display, using an API Key for authentication.
 * It fetches counts for approved pushes, rejected pushes, filters, and indexers.
 *
 * Collected Data:
 * - Number of approved pushes
 * - Number of rejected pushes
 * - Number of filters
 * - Number of indexers
 *
 * Data not collected but available for extension:
 * - Detailed push history and status
 * - Filter and indexer configurations
 * - User and application settings
 * - Performance metrics and logs
 * - Storage and bandwidth usage statistics
 *
 * Authentication:
 * The function uses an API Key passed in the header for authentication.
 *
 * Error Handling:
 * - Captures any errors encountered during the API request process.
 * - Stores error messages and timestamps for troubleshooting.
 *******************/

 function homelab_fetch_autobrr_data($api_url, $api_key, $service_id) {
    $stats_url = rtrim($api_url, '/') . '/api/v1/stats';

    $headers = array(
        'Authorization' => 'Bearer ' . $api_key,
        'Accept' => 'application/json',
    );

    $response = wp_remote_get($stats_url, array('headers' => $headers));

    $error_message = null;
    $error_timestamp = null;

    if (is_wp_error($response)) {
        $error_message = "API request failed: " . $response->get_error_message();
        $error_timestamp = current_time('mysql');
        return array();
    }

    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        $error_message = "API request failed with status code: $response_code";
        $error_timestamp = current_time('mysql');
        return array();
    }

    $stats_data = json_decode(wp_remote_retrieve_body($response), true);

    $approved_pushes = $stats_data['approvedPushes'];
    $rejected_pushes = $stats_data['rejectedPushes'];
    $filters_count = $stats_data['filters'];
    $indexers_count = $stats_data['indexers'];

    $fetched_data = array(
        'approved_pushes' => $approved_pushes,
        'rejected_pushes' => $rejected_pushes,
        'filters_count' => $filters_count,
        'indexers_count' => $indexers_count,
    );

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}

/******************
 * Calibre-web Data Collection
 * -------------------------------
 * This function collects statistics from the Calibre-web API for dashboard display, using a username and password for authentication.
 * It fetches counts for books, authors, categories, and series.
 *
 * Collected Data:
 * - Number of books
 * - Number of authors
 * - Number of categories
 * - Number of series
 *
 * Data not collected but available for extension:
 * - Detailed book information (title, description, publication date, etc.)
 * - Author details and biographies
 * - Category hierarchies and descriptions
 * - Series metadata and book associations
 * - User activity and reading progress
 * - Library settings and configurations
 *
 * Authentication:
 * The function uses a username and password passed in the request body for authentication.
 *
 * Error Handling:
 * - Captures any errors encountered during the API request process.
 * - Stores error messages and timestamps for troubleshooting.
 *******************/

 /* function homelab_fetch_calibre_web_data($api_url, $username, $password, $service_id) {
    $login_url = rtrim($api_url, '/') . '/login';
    $books_url = rtrim($api_url, '/') . '/api/v1/books';
    $authors_url = rtrim($api_url, '/') . '/api/v1/authors';
    $categories_url = rtrim($api_url, '/') . '/api/v1/categories';
    $series_url = rtrim($api_url, '/') . '/api/v1/series';

    $login_data = array(
        'username' => $username,
        'password' => $password,
    );

    $login_response = wp_remote_post($login_url, array(
        'body' => $login_data,
    ));

    if (is_wp_error($login_response)) {
        $error_message = "Login failed: " . $login_response->get_error_message();
        $error_timestamp = current_time('mysql');
        return array();
    }

    $cookies = wp_remote_retrieve_cookies($login_response);

    $books_response = wp_remote_get($books_url, array('cookies' => $cookies));
    $authors_response = wp_remote_get($authors_url, array('cookies' => $cookies));
    $categories_response = wp_remote_get($categories_url, array('cookies' => $cookies));
    $series_response = wp_remote_get($series_url, array('cookies' => $cookies));

    $error_message = null;
    $error_timestamp = null;

    if (is_wp_error($books_response) || is_wp_error($authors_response) || is_wp_error($categories_response) || is_wp_error($series_response)) {
        $error_message = "API request failed: " . $books_response->get_error_message() . ", " . $authors_response->get_error_message() . ", " . $categories_response->get_error_message() . ", " . $series_response->get_error_message();
        $error_timestamp = current_time('mysql');
        return array();
    }

    $books_data = json_decode(wp_remote_retrieve_body($books_response), true);
    $authors_data = json_decode(wp_remote_retrieve_body($authors_response), true);
    $categories_data = json_decode(wp_remote_retrieve_body($categories_response), true);
    $series_data = json_decode(wp_remote_retrieve_body($series_response), true);

    $books_count = count($books_data['books']);
    $authors_count = count($authors_data['authors']);
    $categories_count = count($categories_data['categories']);
    $series_count = count($series_data['series']);

    $fetched_data = array(
        'books_count' => $books_count,
        'authors_count' => $authors_count,
        'categories_count' => $categories_count,
        'series_count' => $series_count,
    );

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */

/******************
 * ChangeDetection.io Self-Hosted Data Collection
 * ----------------------------------------------
 * This function collects statistics from the ChangeDetection.io self-hosted API for dashboard display, using an API key for authentication.
 * It fetches counts for watched URLs, triggered URLs, and the last time data was fetched.
 *
 * Collected Data:
 * - Number of watched URLs
 * - Number of triggered URLs
 * - Timestamp of the last data fetch
 *
 * Data not collected but available for extension:
 * - Detailed information about each watched URL
 * - Change history and diff data for triggered URLs
 * - Notification settings and alert configurations
 * - User and account management data
 * - System performance metrics and logs
 *
 * Authentication:
 * The function uses an API key passed in the header for authentication.
 *
 * Error Handling:
 * - Captures any errors encountered during the API request process.
 * - Stores error messages and timestamps for troubleshooting.
 *******************/

 /* function homelab_fetch_changedetection_data($api_url, $api_key, $service_id) {
    $watched_urls_url = rtrim($api_url, '/') . '/api/v1/urls/watched';
    $triggered_urls_url = rtrim($api_url, '/') . '/api/v1/urls/triggered';
    $last_fetch_url = rtrim($api_url, '/') . '/api/v1/fetch/last';

    $headers = array(
        'Authorization' => 'Bearer ' . $api_key,
        'Accept' => 'application/json',
    );

    $watched_urls_response = wp_remote_get($watched_urls_url, array('headers' => $headers));
    $triggered_urls_response = wp_remote_get($triggered_urls_url, array('headers' => $headers));
    $last_fetch_response = wp_remote_get($last_fetch_url, array('headers' => $headers));

    $error_message = null;
    $error_timestamp = null;

    if (is_wp_error($watched_urls_response) || is_wp_error($triggered_urls_response) || is_wp_error($last_fetch_response)) {
        $error_message = "API request failed: " . $watched_urls_response->get_error_message() . ", " . $triggered_urls_response->get_error_message() . ", " . $last_fetch_response->get_error_message();
        $error_timestamp = current_time('mysql');
        return array();
    }

    $watched_urls_data = json_decode(wp_remote_retrieve_body($watched_urls_response), true);
    $triggered_urls_data = json_decode(wp_remote_retrieve_body($triggered_urls_response), true);
    $last_fetch_data = json_decode(wp_remote_retrieve_body($last_fetch_response), true);

    $watched_urls_count = count($watched_urls_data);
    $triggered_urls_count = count($triggered_urls_data);
    $last_fetch_timestamp = $last_fetch_data['timestamp'];

    $fetched_data = array(
        'watched_urls_count' => $watched_urls_count,
        'triggered_urls_count' => $triggered_urls_count,
        'last_fetch_timestamp' => $last_fetch_timestamp,
    );

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */

/******************
 * Channels DVR Server Data Collection
 * ------------------------------------
 * This function collects statistics from the Channels DVR server API for dashboard display.
 * It fetches counts for active recordings, upcoming recordings, and total recordings.
 *
 * Collected Data:
 * - Number of active recordings
 * - Number of upcoming recordings
 * - Total number of recordings
 *
 * Data not collected but available for extension:
 * - Detailed information about each recording (title, channel, duration, etc.)
 * - Live TV guide data and channel information
 * - DVR settings and configuration
 * - Storage usage and disk space metrics
 * - User and device management data
 *
 * Authentication:
 * The Channels DVR server API does not require authentication for basic statistics.
 *
 * Error Handling:
 * - Captures any errors encountered during the API request process.
 * - Stores error messages and timestamps for troubleshooting.
 *******************/

 /* function homelab_fetch_channels_dvr_data($api_url, $service_id) {
    $active_recordings_url = rtrim($api_url, '/') . '/api/dvr/activeRecordings';
    $upcoming_recordings_url = rtrim($api_url, '/') . '/api/dvr/upcomingRecordings';
    $recordings_url = rtrim($api_url, '/') . '/api/dvr/recordings';

    $active_recordings_response = wp_remote_get($active_recordings_url);
    $upcoming_recordings_response = wp_remote_get($upcoming_recordings_url);
    $recordings_response = wp_remote_get($recordings_url);

    $error_message = null;
    $error_timestamp = null;

    if (is_wp_error($active_recordings_response) || is_wp_error($upcoming_recordings_response) || is_wp_error($recordings_response)) {
        $error_message = "API request failed: " . $active_recordings_response->get_error_message() . ", " . $upcoming_recordings_response->get_error_message() . ", " . $recordings_response->get_error_message();
        $error_timestamp = current_time('mysql');
        return array();
    }

    $active_recordings_data = json_decode(wp_remote_retrieve_body($active_recordings_response), true);
    $upcoming_recordings_data = json_decode(wp_remote_retrieve_body($upcoming_recordings_response), true);
    $recordings_data = json_decode(wp_remote_retrieve_body($recordings_response), true);

    $active_recordings_count = count($active_recordings_data);
    $upcoming_recordings_count = count($upcoming_recordings_data);
    $total_recordings_count = count($recordings_data);

    $fetched_data = array(
        'active_recordings_count' => $active_recordings_count,
        'upcoming_recordings_count' => $upcoming_recordings_count,
        'total_recordings_count' => $total_recordings_count,
    );

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */

/******************
 * Cloudflare Tunnels Data Collection
 * -----------------------------------
 * This function collects statistics from the Cloudflare Tunnels API for dashboard display, using an API key, tunnel ID, and account ID for authentication.
 * It fetches information about the tunnel, including its status, connected connections, and traffic data.
 *
 * Collected Data:
 * - Tunnel status (active or inactive)
 * - Number of active connections
 * - Total number of connections
 * - Ingress and egress traffic data
 *
 * Data not collected but available for extension:
 * - Detailed connection information (source IP, user agent, etc.)
 * - Historical traffic data and metrics
 * - Tunnel configuration settings
 * - Cloudflare account and user details
 * - Logs and error messages
 *
 * Authentication:
 * The function uses an API key passed in the header for authentication, along with the tunnel ID and account ID.
 *
 * Error Handling:
 * - Captures any errors encountered during the API request process.
 * - Stores error messages and timestamps for troubleshooting.
 *******************/

 /* function homelab_fetch_cloudflare_tunnels_data($api_url, $api_key, $tunnel_id, $account_id, $service_id) {
    $tunnel_url = rtrim($api_url, '/') . '/accounts/' . $account_id . '/tunnels/' . $tunnel_id;

    $headers = array(
        'Authorization' => 'Bearer ' . $api_key,
        'Content-Type' => 'application/json',
    );

    $tunnel_response = wp_remote_get($tunnel_url, array('headers' => $headers));

    $error_message = null;
    $error_timestamp = null;

    if (is_wp_error($tunnel_response)) {
        $error_message = "API request failed: " . $tunnel_response->get_error_message();
        $error_timestamp = current_time('mysql');
        return array();
    }

    $tunnel_data = json_decode(wp_remote_retrieve_body($tunnel_response), true);

    $tunnel_status = $tunnel_data['tunnel']['status'];
    $active_connections = $tunnel_data['tunnel']['connections']['active'];
    $total_connections = $tunnel_data['tunnel']['connections']['total'];
    $ingress_traffic = $tunnel_data['tunnel']['traffic']['ingress'];
    $egress_traffic = $tunnel_data['tunnel']['traffic']['egress'];

    $fetched_data = array(
        'tunnel_status' => $tunnel_status,
        'active_connections' => $active_connections,
        'total_connections' => $total_connections,
        'ingress_traffic' => $ingress_traffic,
        'egress_traffic' => $egress_traffic,
    );

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */

/******************
 * Coin Market Cap Data Collection
 * --------------------------------
 * This function collects cryptocurrency data from the Coin Market Cap API for dashboard display, using an API key for authentication.
 * It fetches the latest price, market cap, 24h volume, and 24h percent change for the specified cryptocurrencies.
 *
 * Collected Data:
 * - Latest price of each specified cryptocurrency
 * - Market cap of each specified cryptocurrency
 * - 24-hour trading volume of each specified cryptocurrency
 * - 24-hour percent change in price of each specified cryptocurrency
 *
 * Data not collected but available for extension:
 * - Historical price and market data
 * - Detailed cryptocurrency information (circulating supply, total supply, max supply, etc.)
 * - Global cryptocurrency market data
 * - Exchange and trading pair data
 * - News and social media sentiment data
 *
 * Authentication:
 * The function uses an API key passed in the header for authentication.
 *
 * Parameters:
 * - $api_url: The base URL of the Coin Market Cap API.
 * - $api_key: The API key for authentication.
 * - $service_id: The ID of the service being monitored.
 * - $currency: (Optional) The target currency for price conversion (default: USD).
 * - $symbols: (Optional) An array of cryptocurrency symbols (e.g., BTC, ETH, LTC) to fetch data for. If not provided, data will be fetched for all cryptocurrencies.
 * - $slug_symbols: (Optional) An array of cryptocurrency slug symbols (e.g., bitcoin, ethereum, litecoin) to fetch data for. If not provided, data will be fetched for all cryptocurrencies.
 *
 * Error Handling:
 * - Captures any errors encountered during the API request process.
 * - Stores error messages and timestamps for troubleshooting.
 *******************/

/*  function homelab_fetch_coinmarketcap_data($api_url, $api_key, $service_id, $currency = 'USD', $symbols = array(), $slug_symbols = array()) {
    $api_url = rtrim($api_url, '/');

    $headers = array(
        'Accepts' => 'application/json',
        'X-CMC_PRO_API_KEY' => $api_key,
    );

    $params = array(
        'convert' => $currency,
    );

    if (!empty($symbols)) {
        $params['symbol'] = implode(',', $symbols);
    }

    if (!empty($slug_symbols)) {
        $params['slug'] = implode(',', $slug_symbols);
    }

    $url = $api_url . '/v1/cryptocurrency/quotes/latest?' . http_build_query($params);

    $response = wp_remote_get($url, array('headers' => $headers));

    $error_message = null;
    $error_timestamp = null;

    if (is_wp_error($response)) {
        $error_message = "API request failed: " . $response->get_error_message();
        $error_timestamp = current_time('mysql');
        return array();
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($data['status']['error_code']) && $data['status']['error_code'] !== 0) {
        $error_message = "API request failed with error code: " . $data['status']['error_code'] . ", error message: " . $data['status']['error_message'];
        $error_timestamp = current_time('mysql');
        return array();
    }

    $fetched_data = array();

    foreach ($data['data'] as $cryptocurrency) {
        $symbol = $cryptocurrency['symbol'];
        $fetched_data[$symbol] = array(
            'price' => $cryptocurrency['quote'][$currency]['price'],
            'market_cap' => $cryptocurrency['quote'][$currency]['market_cap'],
            'volume_24h' => $cryptocurrency['quote'][$currency]['volume_24h'],
            'percent_change_24h' => $cryptocurrency['quote'][$currency]['percent_change_24h'],
        );
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */

/******************
 * CyTube Data Collection
 * ----------------------
 * This function collects data from the CyTube API for dashboard display, using an API key for authentication.
 * It fetches the number of users, the number of rooms, the currently playing item, and the items in the block queue.
 *
 * Collected Data:
 * - Total number of users
 * - Total number of rooms
 * - Currently playing item (if enabled)
 * - Items in the block queue (if enabled)
 *
 * Data not collected but available for extension:
 * - Detailed user information (username, profile, etc.)
 * - Detailed room information (name, description, user count, etc.)
 * - Chat messages and user interactions
 * - Media playback statistics and metrics
 * - User-generated content (playlists, favorites, etc.)
 *
 * Authentication:
 * The function uses an API key passed in the header for authentication.
 *
 * Parameters:
 * - $api_url: The base URL of the CyTube API.
 * - $api_key: The API key for authentication.
 * - $service_id: The ID of the service being monitored.
 * - $enable_now_playing: (Optional) Flag to enable fetching the currently playing item (default: false).
 * - $enable_blocks: (Optional) Flag to enable fetching the items in the block queue (default: false).
 *
 * Error Handling:
 * - Captures any errors encountered during the API request process.
 * - Stores error messages and timestamps for troubleshooting.
 *******************/

 /* function homelab_fetch_cytube_data($api_url, $api_key, $service_id, $enable_now_playing = false, $enable_blocks = false) {
    $api_url = rtrim($api_url, '/');

    $headers = array(
        'Accept' => 'application/json',
        'Authorization' => 'Bearer ' . $api_key,
    );

    $endpoints = array(
        'users' => '/api/users',
        'rooms' => '/api/rooms',
    );

    if ($enable_now_playing) {
        $endpoints['now_playing'] = '/api/now-playing';
    }

    if ($enable_blocks) {
        $endpoints['blocks'] = '/api/blocks';
    }

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $response = wp_remote_get($url, array('headers' => $headers));

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'users') {
            $fetched_data['total_users'] = count($data);
        } elseif ($key === 'rooms') {
            $fetched_data['total_rooms'] = count($data);
        } elseif ($key === 'now_playing' && isset($data['item'])) {
            $fetched_data['now_playing'] = $data['item'];
        } elseif ($key === 'blocks') {
            $fetched_data['block_queue'] = $data;
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */

/******************
 * Emby Data Collection
 * --------------------
 * This function collects data from the Emby API for dashboard display, using an API key for authentication.
 * It fetches the number of users, the number of media items, the currently playing item, and the recently added items.
 *
 * Collected Data:
 * - Total number of users
 * - Total number of media items
 * - Currently playing item (if enabled)
 * - Recently added items (if enabled)
 *
 * Data not collected but available for extension:
 * - Detailed user information (username, profile, watch history, etc.)
 * - Detailed media item information (title, description, genre, etc.)
 * - Media playback statistics and metrics
 * - Server status and performance data
 * - User-generated content (playlists, favorites, ratings, etc.)
 *
 * Authentication:
 * The function uses an API key passed in the header for authentication.
 *
 * Parameters:
 * - $api_url: The base URL of the Emby API.
 * - $api_key: The API key for authentication.
 * - $service_id: The ID of the service being monitored.
 * - $enable_now_playing: (Optional) Flag to enable fetching the currently playing item (default: false).
 * - $enable_recently_added: (Optional) Flag to enable fetching the recently added items (default: false).
 *
 * Error Handling:
 * - Captures any errors encountered during the API request process.
 * - Stores error messages and timestamps for troubleshooting.
 *******************/

 /* function homelab_fetch_emby_data($api_url, $api_key, $service_id, $enable_now_playing = false, $enable_recently_added = false) {
    $api_url = rtrim($api_url, '/');

    $headers = array(
        'Accept' => 'application/json',
        'X-MediaBrowser-Token' => $api_key,
    );

    $endpoints = array(
        'users' => '/Users',
        'items' => '/Items',
    );

    if ($enable_now_playing) {
        $endpoints['now_playing'] = '/Sessions';
    }

    if ($enable_recently_added) {
        $endpoints['recently_added'] = '/Users/{UserId}/Items/Latest';
    }

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $response = wp_remote_get($url, array('headers' => $headers));

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'users') {
            $fetched_data['total_users'] = count($data);
        } elseif ($key === 'items') {
            $fetched_data['total_items'] = $data['TotalRecordCount'];
        } elseif ($key === 'now_playing' && !empty($data)) {
            $fetched_data['now_playing'] = $data[0]['NowPlayingItem'];
        } elseif ($key === 'recently_added') {
            $fetched_data['recently_added'] = $data;
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */

/******************
 * ESPHome Data Collection
 * --------------------
 * This function collects data from the ESPHome API for dashboard display, without requiring authentication.
 * It fetches the count of devices by state and other relevant information.
 *
 * Collected Data:
 * - Total number of devices
 * - Number of online devices
 * - Number of offline devices
 * - Number of devices with unknown state
 * - ESPHome server version
 * - Number of enabled devices
 * - Number of disabled devices
 *
 * Data not collected but available for extension:
 * - Detailed device information (name, MAC address, IP address, etc.)
 * - Device configuration and settings
 * - Sensor data and device state
 * - Automation and script execution status
 * - System resource usage and performance data
 *
 * Authentication:
 * This function does not require authentication to access ESPHome API information.
 *
 * Parameters:
 * - $api_url: The base URL of the ESPHome API.
 * - $service_id: The ID of the service being monitored.
 *
 * Error Handling:
 * - Captures any errors encountered during the API request process.
 * - Stores error messages and timestamps for troubleshooting.
 *******************/

 /* function homelab_fetch_esphome_data($api_url, $service_id) {
    $api_url = rtrim($api_url, '/');

    $endpoints = array(
        'devices' => '/devices',
        'info' => '/info',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'devices') {
            $total_devices = count($data);
            $online_devices = 0;
            $offline_devices = 0;
            $unknown_devices = 0;
            $enabled_devices = 0;
            $disabled_devices = 0;

            foreach ($data as $device) {
                switch ($device['state']) {
                    case 'online':
                        $online_devices++;
                        break;
                    case 'offline':
                        $offline_devices++;
                        break;
                    default:
                        $unknown_devices++;
                        break;
                }

                if ($device['enabled']) {
                    $enabled_devices++;
                } else {
                    $disabled_devices++;
                }
            }

            $fetched_data['total_devices_count'] = $total_devices;
            $fetched_data['online_devices_count'] = $online_devices;
            $fetched_data['offline_devices_count'] = $offline_devices;
            $fetched_data['unknown_devices_count'] = $unknown_devices;
            $fetched_data['enabled_devices_count'] = $enabled_devices;
            $fetched_data['disabled_devices_count'] = $disabled_devices;
        } elseif ($key === 'info') {
            $fetched_data['esphome_version'] = $data['version'];
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */

/******************
 * EVCC Data Collection
 * --------------------
 * This function collects data from the EVCC API for dashboard display, without requiring authentication.
 * It fetches the current charging session information, total energy charged, and other relevant data.
 *
 * Collected Data:
 * - Current charging status (charging, connected, disconnected)
 * - Current charging power (in watts)
 * - Total energy charged in the current session (in kWh)
 * - Duration of the current charging session
 * - Vehicle ID of the currently connected vehicle
 * - Total number of charging sessions
 *
 * Data not collected but available for extension:
 * - Detailed charging session history
 * - Vehicle information (make, model, battery capacity, etc.)
 * - Charger information (type, maximum power, etc.)
 * - Energy tariff and cost data
 * - Charging schedules and automation
 *
 * Authentication:
 * This function does not require authentication to access the EVCC API.
 *
 * Parameters:
 * - $api_url: The base URL of the EVCC API.
 * - $service_id: The ID of the service being monitored.
 *
 * Error Handling:
 * - Captures any errors encountered during the API request process.
 * - Stores error messages and timestamps for troubleshooting.
 *******************/

 /* function homelab_fetch_evcc_data($api_url, $service_id) {
    $api_url = rtrim($api_url, '/');

    $endpoints = array(
        'status' => '/api/status',
        'session' => '/api/session',
        'sessions' => '/api/sessions',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'status') {
            $fetched_data['charging_status'] = $data['charging'] ? 'charging' : ($data['connected'] ? 'connected' : 'disconnected');
            $fetched_data['charging_power'] = $data['chargePower'];
        } elseif ($key === 'session') {
            $fetched_data['total_energy_charged'] = $data['chargedEnergy'];
            $fetched_data['session_duration'] = $data['duration'];
            $fetched_data['vehicle_id'] = $data['vehicleID'];
        } elseif ($key === 'sessions') {
            $fetched_data['total_sessions_count'] = count($data);
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */

/******************
 * FileFlows Data Collection
 * -------------------------
 * This function collects data from the FileFlows API for dashboard display, without requiring authentication.
 * It fetches the status of the FileFlows server, the number of active flows, and other relevant information.
 *
 * Collected Data:
 * - FileFlows server status (running or stopped)
 * - Number of active flows
 * - Total number of flows
 * - Total number of processed files
 * - Total number of processed bytes
 * - FileFlows server version
 *
 * Data not collected but available for extension:
 * - Detailed flow information (name, description, status, etc.)
 * - Flow execution history and statistics
 * - Node and worker information
 * - Error logs and diagnostics
 * - User and authentication data
 *
 * Authentication:
 * This function does not require authentication to access the FileFlows API.
 *
 * Parameters:
 * - $api_url: The base URL of the FileFlows API.
 * - $service_id: The ID of the service being monitored.
 *
 * Error Handling:
 * - Captures any errors encountered during the API request process.
 * - Stores error messages and timestamps for troubleshooting.
 *******************/

 /* function homelab_fetch_fileflows_data($api_url, $service_id) {
    $api_url = rtrim($api_url, '/');

    $endpoints = array(
        'status' => '/api/status',
        'flows' => '/api/flows',
        'statistics' => '/api/statistics',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'status') {
            $fetched_data['server_status'] = $data['status'] === 'running' ? 'running' : 'stopped';
            $fetched_data['server_version'] = $data['version'];
        } elseif ($key === 'flows') {
            $active_flows = 0;
            foreach ($data as $flow) {
                if ($flow['status'] === 'active') {
                    $active_flows++;
                }
            }
            $fetched_data['active_flows_count'] = $active_flows;
            $fetched_data['total_flows_count'] = count($data);
        } elseif ($key === 'statistics') {
            $fetched_data['processed_files_count'] = $data['processedFiles'];
            $fetched_data['processed_bytes_count'] = $data['processedBytes'];
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */

/******************
 * Flood Data Collection
 * ---------------------
 * This function collects data from the Flood API for dashboard display, supporting authentication using a username and password.
 * It fetches information about the torrents, including their status, download and upload speeds, and counts.
 *
 * Collected Data:
 * - Total number of torrents
 * - Number of downloading torrents
 * - Number of seeding torrents
 * - Number of stopped torrents
 * - Number of active torrents (downloading or seeding)
 * - Total download speed (in bytes per second)
 * - Total upload speed (in bytes per second)
 *
 * Data not collected but available for extension:
 * - Detailed torrent information (name, size, progress, ratio, etc.)
 * - Torrent tracker and peer details
 * - Transfer history and statistics
 * - Torrent client settings and configuration
 * - User and authentication data
 *
 * Authentication:
 * This function supports authentication using a username and password.
 * If the username and password are provided, they will be used for authentication.
 * If the username and password are not provided or are empty, authentication will be skipped.
 *
 * Parameters:
 * - $api_url: The base URL of the Flood API.
 * - $username: (Optional) The username for authentication.
 * - $password: (Optional) The password for authentication.
 * - $service_id: The ID of the service being monitored.
 *
 * Error Handling:
 * - Captures any errors encountered during the API request process.
 * - Stores error messages and timestamps for troubleshooting.
 *******************/

 /* function homelab_fetch_flood_data($api_url, $username = '', $password = '', $service_id) {
    $api_url = rtrim($api_url, '/');

    $endpoints = array(
        'auth' => '/api/auth/authenticate',
        'torrents' => '/api/torrents',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    // Authentication
    $auth_token = '';
    if (!empty($username) && !empty($password)) {
        $auth_data = array(
            'username' => $username,
            'password' => $password,
        );
        $auth_response = wp_remote_post($api_url . $endpoints['auth'], array(
            'body' => json_encode($auth_data),
            'headers' => array('Content-Type' => 'application/json'),
        ));

        if (is_wp_error($auth_response)) {
            $error_message = "Authentication failed: " . $auth_response->get_error_message();
            $error_timestamp = current_time('mysql');
            homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
            return $fetched_data;
        }

        $auth_body = json_decode(wp_remote_retrieve_body($auth_response), true);
        if (isset($auth_body['token'])) {
            $auth_token = $auth_body['token'];
        }
    }

    // Fetch data
    foreach ($endpoints as $key => $endpoint) {
        if ($key === 'auth') {
            continue;
        }

        $url = $api_url . $endpoint;
        $args = array(
            'headers' => array('Content-Type' => 'application/json'),
        );
        if (!empty($auth_token)) {
            $args['headers']['Authorization'] = 'Bearer ' . $auth_token;
        }
        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'torrents') {
            $total_torrents = count($data);
            $downloading_torrents = 0;
            $seeding_torrents = 0;
            $stopped_torrents = 0;
            $active_torrents = 0;
            $total_download_speed = 0;
            $total_upload_speed = 0;

            foreach ($data as $torrent) {
                $status = $torrent['status'];
                if ($status === 'downloading') {
                    $downloading_torrents++;
                    $active_torrents++;
                } elseif ($status === 'seeding') {
                    $seeding_torrents++;
                    $active_torrents++;
                } elseif ($status === 'stopped') {
                    $stopped_torrents++;
                }
                $total_download_speed += $torrent['downRate'];
                $total_upload_speed += $torrent['upRate'];
            }

            $fetched_data['total_torrents'] = $total_torrents;
            $fetched_data['downloading_torrents'] = $downloading_torrents;
            $fetched_data['seeding_torrents'] = $seeding_torrents;
            $fetched_data['stopped_torrents'] = $stopped_torrents;
            $fetched_data['active_torrents'] = $active_torrents;
            $fetched_data['total_download_speed'] = $total_download_speed;
            $fetched_data['total_upload_speed'] = $total_upload_speed;
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */

/******************
 * FreshRSS Data Collection
 * -------------------------
 * This function collects data from the FreshRSS API for dashboard display, supporting authentication using a username and password.
 * It fetches information about the user's feeds, categories, and articles.
 *
 * Collected Data:
 * - Total number of feeds
 * - Number of categories
 * - Number of unread articles
 * - Number of read articles
 * - Number of starred articles
 *
 * Data not collected but available for extension:
 * - Detailed feed information (title, URL, description, etc.)
 * - Detailed category information (name, parent category, etc.)
 * - Detailed article information (title, URL, content, author, etc.)
 * - User preferences and settings
 * - Feed synchronization status and logs
 *
 * Authentication:
 * This function requires authentication using a username and password.
 * The username and password should be provided as parameters to the function.
 *
 * Parameters:
 * - $api_url: The base URL of the FreshRSS API.
 * - $username: The username for authentication.
 * - $password: The password for authentication.
 * - $service_id: The ID of the service being monitored.
 *
 * Error Handling:
 * - Captures any errors encountered during the API request process.
 * - Stores error messages and timestamps for troubleshooting.
 *******************/

 /* function homelab_fetch_freshrss_data($api_url, $username, $password, $service_id) {
    $api_url = rtrim($api_url, '/');

    $endpoints = array(
        'feeds' => '/api/feeds.php',
        'categories' => '/api/categories.php',
        'entries' => '/api/entries.php',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    // Authentication
    $auth_string = base64_encode($username . ':' . $password);

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $args = array(
            'headers' => array(
                'Authorization' => 'Basic ' . $auth_string,
                'Content-Type' => 'application/json',
            ),
        );
        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'feeds') {
            $fetched_data['total_feeds'] = count($data);
        } elseif ($key === 'categories') {
            $fetched_data['total_categories'] = count($data);
        } elseif ($key === 'entries') {
            $unread_articles = 0;
            $read_articles = 0;
            $starred_articles = 0;

            foreach ($data['entries'] as $entry) {
                if ($entry['is_read']) {
                    $read_articles++;
                } else {
                    $unread_articles++;
                }
                if ($entry['is_favorite']) {
                    $starred_articles++;
                }
            }

            $fetched_data['unread_articles'] = $unread_articles;
            $fetched_data['read_articles'] = $read_articles;
            $fetched_data['starred_articles'] = $starred_articles;
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */

/******************
 * FRITZ!Box Data Collection
 * --------------------------
 * This function collects data from the FRITZ!Box API for dashboard display, supporting authentication using the FRITZ!Box login credentials.
 * It fetches information about the device status, connected devices, and various statistics.
 *
 * Collected Data:
 * - Device model and firmware version
 * - Uptime of the device
 * - Number of connected devices
 * - Upstream and downstream speeds
 * - Total data transferred (sent and received)
 * - WAN connection status
 *
 * Data not collected but available for extension:
 * - Detailed information about connected devices (IP address, MAC address, hostname, etc.)
 * - WLAN configuration and status
 * - DECT device information and status
 * - Phone call logs and statistics
 * - Smart home device status and control
 *
 * Authentication:
 * This function requires authentication using the FRITZ!Box login credentials (username and password).
 * The username and password should be provided as parameters to the function.
 *
 * Parameters:
 * - $api_url: The base URL of the FRITZ!Box API.
 * - $username: The username for authentication (default: "admin").
 * - $password: The password for authentication.
 * - $service_id: The ID of the service being monitored.
 *
 * Error Handling:
 * - Captures any errors encountered during the API request process.
 * - Stores error messages and timestamps for troubleshooting.
 *******************/

 /* function homelab_fetch_fritzbox_data($api_url, $username = 'admin', $password, $service_id) {
    $api_url = rtrim($api_url, '/');

    $endpoints = array(
        'deviceinfo' => '/data.lua',
        'connectivity' => '/internet/inetstat_monitor.lua',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    // Authentication
    $auth_string = base64_encode($username . ':' . $password);

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $args = array(
            'headers' => array(
                'Authorization' => 'Basic ' . $auth_string,
                'Content-Type' => 'application/json',
            ),
        );
        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'deviceinfo') {
            $fetched_data['device_model'] = $data['model'];
            $fetched_data['firmware_version'] = $data['fw_version'];
            $fetched_data['uptime'] = $data['uptime'];
            $fetched_data['connected_devices'] = $data['active_hosts'];
        } elseif ($key === 'connectivity') {
            $fetched_data['upstream_speed'] = $data['upstream'];
            $fetched_data['downstream_speed'] = $data['downstream'];
            $fetched_data['data_sent'] = $data['bytes_sent'];
            $fetched_data['data_received'] = $data['bytes_received'];
            $fetched_data['wan_connection_status'] = $data['connection_status'];
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */

/******************
 * GameDig Data Collection
 * -----------------------
 * This function collects data from a game server using the GameDig library for dashboard display.
 * It fetches information about the server status, player count, map, and other server-specific data.
 *
 * Collected Data:
 * - Server status (online/offline)
 * - Number of players online
 * - Maximum number of players allowed
 * - Current map name
 * - Server name
 * - Game type
 *
 * Data not collected but available for extension:
 * - Detailed player information (names, scores, duration, etc.)
 * - Server configuration and settings
 * - Server mods and plugins
 * - Server latency and performance metrics
 *
 * Requirements:
 * - The GameDig library must be installed and loaded.
 *
 * Parameters:
 * - $server_type: The type of the game server (e.g., "minecraft", "csgo", "tf2").
 * - $server_host: The hostname or IP address of the game server.
 * - $server_port: The port number of the game server.
 * - $service_id: The ID of the service being monitored.
 *
 * Error Handling:
 * - Captures any errors encountered during the server query process.
 * - Stores error messages and timestamps for troubleshooting.
 *******************/

 /* function homelab_fetch_gamedig_data($server_type, $server_host, $server_port, $service_id) {
    require_once 'path/to/GameDig/Browser.php';

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    try {
        $gamedig = new \GameQ\GameQ();
        $gamedig->addServer([
            'type' => $server_type,
            'host' => $server_host,
            'port' => $server_port,
        ]);

        $gamedig->setOption('timeout', 5);
        $results = $gamedig->process();

        if (isset($results[$server_host . ':' . $server_port])) {
            $server_data = $results[$server_host . ':' . $server_port];

            $fetched_data['server_status'] = $server_data['gq_online'] ? 'online' : 'offline';
            $fetched_data['num_players'] = $server_data['gq_numplayers'];
            $fetched_data['max_players'] = $server_data['gq_maxplayers'];
            $fetched_data['map_name'] = $server_data['gq_mapname'];
            $fetched_data['server_name'] = $server_data['gq_hostname'];
            $fetched_data['game_type'] = $server_data['gq_gametype'];
        } else {
            $error_message = "Server data not found for {$server_host}:{$server_port}";
            $error_timestamp = current_time('mysql');
        }
    } catch (Exception $e) {
        $error_message = "Error querying server: " . $e->getMessage();
        $error_timestamp = current_time('mysql');
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */

/******************
 * Gatus Data Collection
 * ---------------------
 * This function collects data from Gatus, a service monitoring tool, for dashboard display.
 * It fetches information about the services monitored by Gatus, including their status and other relevant data.
 *
 * Collected Data:
 * - Total number of services
 * - Number of healthy services
 * - Number of degraded services
 * - Number of failed services
 * - Status of each service (name, status, uptime, response time)
 *
 * Data not collected but available for extension:
 * - Detailed service information (description, group, conditions)
 * - Service metrics and historical data
 * - Alerting and notification settings
 * - Gatus configuration and settings
 *
 * Requirements:
 * - Gatus should be accessible via the provided API URL.
 *
 * Parameters:
 * - $api_url: The base URL of the Gatus API.
 * - $service_id: The ID of the service being monitored.
 *
 * Error Handling:
 * - Captures any errors encountered during the API request process.
 * - Stores error messages and timestamps for troubleshooting.
 *******************/

 /* function homelab_fetch_gatus_data($api_url, $service_id) {
    $api_url = rtrim($api_url, '/');

    $endpoints = array(
        'services' => '/api/v1/services',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'services') {
            $fetched_data['total_services'] = count($data);

            $healthy_services = 0;
            $degraded_services = 0;
            $failed_services = 0;
            $service_statuses = array();

            foreach ($data as $service) {
                $status = $service['status'];
                $service_statuses[] = array(
                    'name' => $service['name'],
                    'status' => $status,
                    'uptime' => $service['uptime'],
                    'response_time' => $service['response_time'],
                );

                if ($status === 'healthy') {
                    $healthy_services++;
                } elseif ($status === 'degraded') {
                    $degraded_services++;
                } elseif ($status === 'failed') {
                    $failed_services++;
                }
            }

            $fetched_data['healthy_services'] = $healthy_services;
            $fetched_data['degraded_services'] = $degraded_services;
            $fetched_data['failed_services'] = $failed_services;
            $fetched_data['service_statuses'] = $service_statuses;
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */

/******************
 * Gitea Data Collection
 * ---------------------
 * This function collects data from Gitea, a self-hosted Git service, for dashboard display.
 * It fetches information about the user's repositories, organizations, and activity statistics.
 *
 * Collected Data:
 * - Total number of repositories owned by the user
 * - Number of private repositories
 * - Number of public repositories
 * - Number of organizations the user belongs to
 * - Number of pull requests created by the user
 * - Number of issues created by the user
 *
 * Data not collected but available for extension:
 * - Detailed repository information (name, description, URL, stars, forks)
 * - Detailed organization information (name, description, URL, members)
 * - User profile information (name, email, avatar, bio)
 * - Detailed activity statistics (commits, issues, pull requests)
 * - Repository collaboration and access control settings
 *
 * Requirements:
 * - Gitea API should be accessible via the provided API URL.
 * - API authentication token (API key) is required.
 *
 * Parameters:
 * - $api_url: The base URL of the Gitea API.
 * - $api_key: The API key for authentication.
 * - $service_id: The ID of the service being monitored.
 *
 * Error Handling:
 * - Captures any errors encountered during the API request process.
 * - Stores error messages and timestamps for troubleshooting.
 *******************/

 /* function homelab_fetch_gitea_data($api_url, $api_key, $service_id) {
    $api_url = rtrim($api_url, '/');

    $endpoints = array(
        'repos' => '/api/v1/user/repos',
        'orgs' => '/api/v1/user/orgs',
        'activity' => '/api/v1/user/stats',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $args = array(
            'headers' => array(
                'Authorization' => 'token ' . $api_key,
                'Content-Type' => 'application/json',
            ),
        );
        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'repos') {
            $fetched_data['total_repos'] = count($data);

            $private_repos = 0;
            $public_repos = 0;
            foreach ($data as $repo) {
                if ($repo['private']) {
                    $private_repos++;
                } else {
                    $public_repos++;
                }
            }
            $fetched_data['private_repos'] = $private_repos;
            $fetched_data['public_repos'] = $public_repos;
        } elseif ($key === 'orgs') {
            $fetched_data['num_orgs'] = count($data);
        } elseif ($key === 'activity') {
            $fetched_data['pull_requests_created'] = $data['pull_requests_created'];
            $fetched_data['issues_created'] = $data['issues_created'];
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */

/******************
 * Glances Data Collection
 * -----------------------
 * This function collects data from Glances, a cross-platform system monitoring tool, for dashboard display.
 * It fetches information about the system's CPU, memory, disk usage, network traffic, and running processes.
 *
 * Collected Data:
 * - CPU usage percentage
 * - Memory usage percentage
 * - Disk usage percentage
 * - Network traffic (download and upload speeds)
 * - Number of running processes
 *
 * Data not collected but available for extension:
 * - Detailed CPU information (per-core usage, load average)
 * - Detailed memory information (used, free, cached, buffers)
 * - Detailed disk information (per-partition usage, I/O stats)
 * - Detailed network information (per-interface traffic, connections)
 * - Process details (CPU usage, memory usage, status, user)
 * - System information (hostname, OS, uptime)
 * - Sensor data (temperatures, fan speeds, battery)
 *
 * Requirements:
 * - Glances API should be accessible via the provided API URL.
 * - Glances should be configured to allow remote access (if accessing remotely).
 *
 * Parameters:
 * - $api_url: The URL of the Glances API.
 * - $service_id: The ID of the service being monitored.
 *
 * Error Handling:
 * - Captures any errors encountered during the API request process.
 * - Stores error messages and timestamps for troubleshooting.
 *******************/

/*  function homelab_fetch_glances_data($api_url, $service_id) {
    $api_url = rtrim($api_url, '/');

    $endpoints = array(
        'all' => '/',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'all') {
            $cpu_percent = $data['cpu']['total'];
            $memory_percent = $data['mem']['percent'];
            $disk_percent = $data['fs'][0]['percent'];
            $network_rx = $data['network']['rx'];
            $network_tx = $data['network']['tx'];
            $num_processes = $data['processcount']['total'];

            $fetched_data['cpu_usage'] = $cpu_percent;
            $fetched_data['memory_usage'] = $memory_percent;
            $fetched_data['disk_usage'] = $disk_percent;
            $fetched_data['network_download'] = $network_rx;
            $fetched_data['network_upload'] = $network_tx;
            $fetched_data['num_processes'] = $num_processes;
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */

/******************
 * Gluten Data Collection
 * ----------------------
 * This function collects data from Gluten, a service monitoring tool, for dashboard display.
 * It fetches information about the monitored services, including their status, response times, and uptime percentages.
 *
 * Collected Data:
 * - Total number of monitored services
 * - Number of services by status (up, down, degraded, unknown)
 * - Average response time across all services
 * - Average uptime percentage across all services
 *
 * Data not collected but available for extension:
 * - Detailed service information (name, URL, port, protocol)
 * - Service-specific response times and uptime percentages
 * - Service health check details (last check time, error messages)
 * - Alert and notification settings
 * - Historical uptime and response time data
 * - Gluten configuration and settings
 *
 * Requirements:
 * - Gluten API should be accessible via the provided API URL.
 * - API authentication token (API key) may be required depending on Gluten configuration.
 *
 * Parameters:
 * - $api_url: The base URL of the Gluten API.
 * - $api_key: The API key for authentication (if required).
 * - $service_id: The ID of the service being monitored.
 *
 * Error Handling:
 * - Captures any errors encountered during the API request process.
 * - Stores error messages and timestamps for troubleshooting.
 *******************/

 /* function homelab_fetch_gluten_data($api_url, $api_key = '', $service_id) {
    $api_url = rtrim($api_url, '/');

    $endpoints = array(
        'services' => '/api/v1/services',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
        );

        if (!empty($api_key)) {
            $args['headers']['Authorization'] = 'Bearer ' . $api_key;
        }

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'services') {
            $fetched_data['total_services'] = count($data);

            $status_counts = array(
                'up' => 0,
                'down' => 0,
                'degraded' => 0,
                'unknown' => 0,
            );

            $total_response_time = 0;
            $total_uptime_percentage = 0;

            foreach ($data as $service) {
                $status = strtolower($service['status']);
                if (isset($status_counts[$status])) {
                    $status_counts[$status]++;
                } else {
                    $status_counts['unknown']++;
                }

                $total_response_time += $service['response_time'];
                $total_uptime_percentage += $service['uptime_percentage'];
            }

            $fetched_data['services_up'] = $status_counts['up'];
            $fetched_data['services_down'] = $status_counts['down'];
            $fetched_data['services_degraded'] = $status_counts['degraded'];
            $fetched_data['services_unknown'] = $status_counts['unknown'];
            $fetched_data['avg_response_time'] = $fetched_data['total_services'] > 0 ? $total_response_time / $fetched_data['total_services'] : 0;
            $fetched_data['avg_uptime_percentage'] = $fetched_data['total_services'] > 0 ? $total_uptime_percentage / $fetched_data['total_services'] : 0;
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */

   /**********************
* Gotify Data Collection
* ----------------------
* This function collects data from Gotify, a self-hosted notification service, for dashboard display.
* It fetches information about the applications, messages, and overall usage statistics.
*
* Collected Data:
* - Total number of applications
* - Number of messages by priority (low, moderate, high)
* - Total number of messages
* - Average message count per application
*
* Data not collected but available for extension:
* - Detailed application information (name, description, token)
* - Message details (title, content, date, priority)
* - Client and user management data
* - Plugin and integration settings
* - Notification and delivery statistics
* - Message deletion and acknowledgment status
*
* Opportunities for additional data collection:
* - Application-specific message statistics
* - User engagement and interaction metrics
* - Notification delivery success rates
* - Plugin usage and performance data
* - System health and resource utilization
*
* Requirements:
* - Gotify API should be accessible via the provided API URL.
* - API authentication token (API key or client token) is required for accessing the Gotify API.
*
* Parameters:
* - $api_url: The base URL of the Gotify API.
* - $api_key: The API key or client token for authentication.
* - $service_id: The ID of the Gotify service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
***********************/
/* function homelab_fetch_gotify_data($api_url, $api_key, $service_id) {
    $api_url = rtrim($api_url, '/');

    $endpoints = array(
        'applications' => '/application',
        'messages' => '/message',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;

        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-Gotify-Key' => $api_key,
            ),
        );

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'applications') {
            $fetched_data['total_applications'] = count($data);
        } elseif ($key === 'messages') {
            $priority_counts = array(
                'low' => 0,
                'moderate' => 0,
                'high' => 0,
            );

            foreach ($data as $message) {
                $priority = strtolower($message['priority']);
                if (isset($priority_counts[$priority])) {
                    $priority_counts[$priority]++;
                }
            }

            $fetched_data['messages_low'] = $priority_counts['low'];
            $fetched_data['messages_moderate'] = $priority_counts['moderate'];
            $fetched_data['messages_high'] = $priority_counts['high'];
            $fetched_data['total_messages'] = count($data);
            $fetched_data['avg_messages_per_app'] = $fetched_data['total_applications'] > 0 ? $fetched_data['total_messages'] / $fetched_data['total_applications'] : 0;
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */

/**********************
* Grafana Data Collection
* ----------------------
* This function collects data from Grafana, a monitoring and observability platform, for dashboard display.
* It fetches information about the dashboards, panels, users, and overall usage statistics.
*
* Collected Data:
* - Total number of dashboards
* - Total number of panels
* - Number of dashboards by tag
* - Number of active users
* - Average dashboard count per user
*
* Data not collected but available for extension:
* - Detailed dashboard information (name, description, tags, URL)
* - Panel details (title, type, query, visualization)
* - User details (name, email, role, last active)
* - Organization and folder structure
* - Data source configuration and status
* - Alert and notification settings
* - Plugin and integration data
*
* Opportunities for additional data collection:
* - Dashboard usage and popularity metrics
* - Panel performance and query execution times
* - User activity and interaction patterns
* - Data source health and connectivity status
* - Alert frequency and resolution times
* - Plugin usage and effectiveness
*
* Requirements:
* - Grafana API should be accessible via the provided API URL.
* - API authentication requires a username and password.
*
* Parameters:
* - $api_url: The base URL of the Grafana API.
* - $username: The username for authentication.
* - $password: The password for authentication.
* - $service_id: The ID of the Grafana service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
***********************/
/* function homelab_fetch_grafana_data($api_url, $username, $password, $service_id) {
    $api_url = rtrim($api_url, '/');

    $endpoints = array(
        'dashboards' => '/api/search',
        'users' => '/api/users',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;

        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode(array(
                'username' => $username,
                'password' => $password,
            )),
        );

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'dashboards') {
            $fetched_data['total_dashboards'] = count($data);

            $tag_counts = array();
            $total_panels = 0;

            foreach ($data as $dashboard) {
                $tags = $dashboard['tags'];
                foreach ($tags as $tag) {
                    if (isset($tag_counts[$tag])) {
                        $tag_counts[$tag]++;
                    } else {
                        $tag_counts[$tag] = 1;
                    }
                }
                $total_panels += $dashboard['panels'];
            }

            $fetched_data['dashboards_by_tag'] = $tag_counts;
            $fetched_data['total_panels'] = $total_panels;
        } elseif ($key === 'users') {
            $fetched_data['active_users'] = count($data);
            $fetched_data['avg_dashboards_per_user'] = $fetched_data['total_dashboards'] > 0 ? $fetched_data['total_dashboards'] / $fetched_data['active_users'] : 0;
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */

/**********************
* Health Checks Data Collection
* ----------------------
* This function collects data from Health Checks, a cron job monitoring service, for dashboard display.
* It fetches information about the checks, their status, and overall usage statistics.
*
* Collected Data:
* - Total number of checks
* - Number of checks by status (up, down, paused)
* - Number of checks by schedule (daily, weekly, monthly)
* - Average ping frequency per check
* - Total number of pings received
*
* Data not collected but available for extension:
* - Detailed check information (name, description, tags, last ping)
* - Check configuration (schedule, grace period, timeout)
* - Ping history and response times
* - Alert channels and notification settings
* - Team and project management data
* - API usage and limits
*
* Opportunities for additional data collection:
* - Check uptime and reliability metrics
* - Ping latency and network performance
* - Alert frequency and resolution times
* - Integration and webhook usage
* - Account activity and user engagement
*
* Requirements:
* - Health Checks API should be accessible via the provided API URL.
* - API authentication requires an API key.
* - Specific check data requires the check's UUID.
*
* Parameters:
* - $api_url: The base URL of the Health Checks API.
* - $api_key: The API key for authentication.
* - $check_uuid: The UUID of the specific check (optional).
* - $service_id: The ID of the Health Checks service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
***********************/
/* function homelab_fetch_healthchecks_data($api_url, $api_key, $check_uuid = '', $service_id) {
    $api_url = rtrim($api_url, '/');

    $endpoints = array(
        'checks' => '/api/v1/checks/',
        'pings' => '/api/v1/pings/',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;

        if (!empty($check_uuid)) {
            $url .= $check_uuid . '/';
        }

        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-Api-Key' => $api_key,
            ),
        );

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'checks') {
            $fetched_data['total_checks'] = count($data['checks']);

            $status_counts = array(
                'up' => 0,
                'down' => 0,
                'paused' => 0,
            );

            $schedule_counts = array(
                'daily' => 0,
                'weekly' => 0,
                'monthly' => 0,
            );

            $total_ping_frequency = 0;

            foreach ($data['checks'] as $check) {
                $status = strtolower($check['status']);
                if (isset($status_counts[$status])) {
                    $status_counts[$status]++;
                }

                $schedule = strtolower($check['schedule']);
                if (isset($schedule_counts[$schedule])) {
                    $schedule_counts[$schedule]++;
                }

                $total_ping_frequency += $check['ping_frequency'];
            }

            $fetched_data['checks_up'] = $status_counts['up'];
            $fetched_data['checks_down'] = $status_counts['down'];
            $fetched_data['checks_paused'] = $status_counts['paused'];

            $fetched_data['checks_daily'] = $schedule_counts['daily'];
            $fetched_data['checks_weekly'] = $schedule_counts['weekly'];
            $fetched_data['checks_monthly'] = $schedule_counts['monthly'];

            $fetched_data['avg_ping_frequency'] = $fetched_data['total_checks'] > 0 ? $total_ping_frequency / $fetched_data['total_checks'] : 0;
        } elseif ($key === 'pings') {
            $fetched_data['total_pings'] = count($data['pings']);
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */

/**********************
* Home Assistant Data Collection
* ----------------------
* This function collects data from Home Assistant, a home automation platform, for dashboard display.
* It fetches information about the devices, entities, and overall system status.
*
* Collected Data:
* - Total number of devices
* - Number of devices by category (light, switch, sensor, etc.)
* - Number of entities by domain (light, switch, sensor, etc.)
* - Average state changes per entity
* - System health and runtime information
*
* Data not collected but available for extension:
* - Detailed device information (name, model, manufacturer)
* - Entity details (state, attributes, last changed)
* - Automation and script configurations
* - Scene and group definitions
* - Notification and alert settings
* - User and permission management
*
* Opportunities for additional data collection:
* - Device and entity usage patterns
* - Automation and script execution history
* - Energy consumption and efficiency metrics
* - Sensor data trends and anomaly detection
* - Integration and third-party service usage
*
* Requirements:
* - Home Assistant API should be accessible via the provided API URL.
* - API authentication requires an API key.
*
* Parameters:
* - $api_url: The base URL of the Home Assistant API.
* - $api_key: The API key for authentication.
* - $service_id: The ID of the Home Assistant service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
***********************/
/* function homelab_fetch_homeassistant_data($api_url, $api_key, $service_id) {
    $api_url = rtrim($api_url, '/');

    $endpoints = array(
        'devices' => '/api/states',
        'entities' => '/api/states',
        'system' => '/api/config',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;

        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key,
            ),
        );

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'devices' || $key === 'entities') {
            $device_counts = array();
            $entity_counts = array();
            $total_state_changes = 0;

            foreach ($data as $entity) {
                $device_class = $entity['attributes']['device_class'] ?? 'unknown';
                if (isset($device_counts[$device_class])) {
                    $device_counts[$device_class]++;
                } else {
                    $device_counts[$device_class] = 1;
                }

                $domain = explode('.', $entity['entity_id'])[0];
                if (isset($entity_counts[$domain])) {
                    $entity_counts[$domain]++;
                } else {
                    $entity_counts[$domain] = 1;
                }

                $total_state_changes += $entity['attributes']['state_changes'] ?? 0;
            }

            $fetched_data['total_devices'] = count($data);
            $fetched_data['devices_by_category'] = $device_counts;
            $fetched_data['entities_by_domain'] = $entity_counts;
            $fetched_data['avg_state_changes'] = $fetched_data['total_devices'] > 0 ? $total_state_changes / $fetched_data['total_devices'] : 0;
        } elseif ($key === 'system') {
            $fetched_data['system_health'] = $data['state'];
            $fetched_data['system_runtime'] = $data['last_boot'];
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */

/**********************
* Homebox Data Collection
* ----------------------
* This function collects data from Homebox, a home server management platform, for dashboard display.
* It fetches information about the server, services, and overall system status.
*
* Collected Data:
* - Server hostname and operating system
* - Total number of services
* - Number of services by status (running, stopped, error)
* - Average uptime per service
* - System resource utilization (CPU, memory, disk)
*
* Data not collected but available for extension:
* - Detailed service information (name, description, configuration)
* - Service logs and error messages
* - Network and port configuration
* - User and permission management
* - Backup and restore settings
* - Plugin and extension data
*
* Opportunities for additional data collection:
* - Service performance and response times
* - Resource usage trends and anomaly detection
* - Network traffic and bandwidth analysis
* - Security and access control metrics
* - User activity and audit logging
*
* Requirements:
* - Homebox API should be accessible via the provided API URL.
* - API authentication requires a username and password.
*
* Parameters:
* - $api_url: The base URL of the Homebox API.
* - $username: The username for authentication.
* - $password: The password for authentication.
* - $service_id: The ID of the Homebox service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
***********************/
/* function homelab_fetch_homebox_data($api_url, $username, $password, $service_id) {
    $api_url = rtrim($api_url, '/');

    $endpoints = array(
        'server' => '/api/server',
        'services' => '/api/services',
        'system' => '/api/system',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;

        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode(array(
                'username' => $username,
                'password' => $password,
            )),
        );

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'server') {
            $fetched_data['hostname'] = $data['hostname'];
            $fetched_data['operating_system'] = $data['os'];
        } elseif ($key === 'services') {
            $fetched_data['total_services'] = count($data);

            $status_counts = array(
                'running' => 0,
                'stopped' => 0,
                'error' => 0,
            );

            $total_uptime = 0;

            foreach ($data as $service) {
                $status = strtolower($service['status']);
                if (isset($status_counts[$status])) {
                    $status_counts[$status]++;
                }

                $total_uptime += $service['uptime'];
            }

            $fetched_data['services_running'] = $status_counts['running'];
            $fetched_data['services_stopped'] = $status_counts['stopped'];
            $fetched_data['services_error'] = $status_counts['error'];
            $fetched_data['avg_uptime'] = $fetched_data['total_services'] > 0 ? $total_uptime / $fetched_data['total_services'] : 0;
        } elseif ($key === 'system') {
            $fetched_data['cpu_usage'] = $data['cpu_usage'];
            $fetched_data['memory_usage'] = $data['memory_usage'];
            $fetched_data['disk_usage'] = $data['disk_usage'];
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */

/**********************
* Immich Data Collection
* ----------------------
* This function collects data from Immich, a self-hosted photo and video backup solution, for dashboard display.
* It fetches information about the user, albums, assets, and overall system status.
*
* Collected Data:
* - Total number of users
* - Number of albums per user
* - Total number of assets (photos and videos)
* - Average asset size
* - System storage utilization
*
* Data not collected but available for extension:
* - Detailed user information (name, email, registration date)
* - Album details (name, description, creation date)
* - Asset metadata (filename, timestamp, location, tags)
* - Sharing and collaboration settings
* - Backup and synchronization status
* - Server configuration and performance metrics
*
* Opportunities for additional data collection:
* - User activity and engagement metrics
* - Album access and view counts
* - Asset popularity and sharing statistics
* - Storage trends and growth projections
* - System resource utilization and optimization
*
* Requirements:
* - Immich API should be accessible via the provided API URL.
* - API authentication requires an API key.
*
* Parameters:
* - $api_url: The base URL of the Immich API.
* - $api_key: The API key for authentication.
* - $service_id: The ID of the Immich service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
***********************/
/* function homelab_fetch_immich_data($api_url, $api_key, $service_id) {
    $api_url = rtrim($api_url, '/');

    $endpoints = array(
        'users' => '/api/user',
        'albums' => '/api/album',
        'assets' => '/api/asset',
        'system' => '/api/server/info',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;

        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key,
            ),
        );

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'users') {
            $fetched_data['total_users'] = count($data);
        } elseif ($key === 'albums') {
            $user_album_counts = array();

            foreach ($data as $album) {
                $user_id = $album['ownerId'];
                if (isset($user_album_counts[$user_id])) {
                    $user_album_counts[$user_id]++;
                } else {
                    $user_album_counts[$user_id] = 1;
                }
            }

            $fetched_data['albums_per_user'] = $user_album_counts;
        } elseif ($key === 'assets') {
            $fetched_data['total_assets'] = count($data);

            $total_size = 0;
            foreach ($data as $asset) {
                $total_size += $asset['fileSize'];
            }

            $fetched_data['avg_asset_size'] = $fetched_data['total_assets'] > 0 ? $total_size / $fetched_data['total_assets'] : 0;
        } elseif ($key === 'system') {
            $fetched_data['storage_usage'] = $data['storageUsage'];
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */

/**********************
* JDownloader Data Collection
* ----------------------
* This function collects data from JDownloader, a download management tool, for dashboard display.
* It fetches information about the downloads, download status, and overall system status.
*
* Collected Data:
* - Total number of downloads
* - Number of downloads by status (running, finished, failed)
* - Average download speed
* - Total downloaded data
* - System resource utilization (CPU, memory)
*
* Data not collected but available for extension:
* - Detailed download information (filename, URL, size, timestamp)
* - Download source and category
* - Package and file structure
* - Captcha solving and account management
* - Bandwidth and traffic limits
* - Plugin and extension data
*
* Opportunities for additional data collection:
* - Download success rate and error analysis
* - Peak download times and activity patterns
* - File type and category distribution
* - Network and server performance metrics
* - User behavior and preferences
*
* Requirements:
* - JDownloader API should be accessible via the provided API URL.
* - API authentication requires a username and password.
* - Client identification may be required for API access.
*
* Parameters:
* - $api_url: The base URL of the JDownloader API.
* - $username: The username for authentication.
* - $password: The password for authentication.
* - $client_id: The client ID for API access (optional).
* - $service_id: The ID of the JDownloader service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
***********************/
/* function homelab_fetch_jdownloader_data($api_url, $username, $password, $client_id = '', $service_id) {
    $api_url = rtrim($api_url, '/');

    $endpoints = array(
        'downloads' => '/downloads',
        'system' => '/system',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;

        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode(array(
                'username' => $username,
                'password' => $password,
                'client' => $client_id,
            )),
        );

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'downloads') {
            $fetched_data['total_downloads'] = count($data);

            $status_counts = array(
                'running' => 0,
                'finished' => 0,
                'failed' => 0,
            );

            $total_speed = 0;
            $total_size = 0;

            foreach ($data as $download) {
                $status = strtolower($download['status']);
                if (isset($status_counts[$status])) {
                    $status_counts[$status]++;
                }

                $total_speed += $download['speed'];
                $total_size += $download['bytesLoaded'];
            }

            $fetched_data['downloads_running'] = $status_counts['running'];
            $fetched_data['downloads_finished'] = $status_counts['finished'];
            $fetched_data['downloads_failed'] = $status_counts['failed'];
            $fetched_data['avg_download_speed'] = $fetched_data['total_downloads'] > 0 ? $total_speed / $fetched_data['total_downloads'] : 0;
            $fetched_data['total_downloaded_data'] = $total_size;
        } elseif ($key === 'system') {
            $fetched_data['cpu_usage'] = $data['cpu'];
            $fetched_data['memory_usage'] = $data['memory'];
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */

/**********************
* Jellyfin Data Collection
* ----------------------
* This function collects data from Jellyfin, a media streaming server, for dashboard display.
* It fetches information about the media library, users, and overall system status.
*
* Collected Data:
* - Total number of media items
* - Number of media items by type (movies, TV shows, music)
* - Total number of users
* - Average play count per user
* - System storage utilization
*
* Data not collected but available for extension:
* - Detailed media information (title, year, genre, duration)
* - User details (username, last login, playback progress)
* - Playback statistics and history
* - Playlist and collection management
* - Transcoding and streaming settings
* - Plugin and extension data
*
* Opportunities for additional data collection:
* - Media popularity and watch time analytics
* - User engagement and retention metrics
* - Content discovery and recommendation insights
* - Server performance and scalability metrics
* - Bandwidth and network utilization
*
* Requirements:
* - Jellyfin API should be accessible via the provided API URL.
* - API authentication requires an API key.
*
* Parameters:
* - $api_url: The base URL of the Jellyfin API.
* - $api_key: The API key for authentication.
* - $service_id: The ID of the Jellyfin service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
***********************/
/* function homelab_fetch_jellyfin_data($api_url, $api_key, $service_id) {
    $api_url = rtrim($api_url, '/');

    $endpoints = array(
        'items' => '/Items',
        'users' => '/Users',
        'system' => '/System/Info',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;

        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-Emby-Token' => $api_key,
            ),
        );

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'items') {
            $fetched_data['total_items'] = count($data['Items']);

            $type_counts = array(
                'movies' => 0,
                'tvshows' => 0,
                'music' => 0,
            );

            foreach ($data['Items'] as $item) {
                $type = strtolower($item['Type']);
                if (isset($type_counts[$type])) {
                    $type_counts[$type]++;
                }
            }

            $fetched_data['movies_count'] = $type_counts['movies'];
            $fetched_data['tvshows_count'] = $type_counts['tvshows'];
            $fetched_data['music_count'] = $type_counts['music'];
        } elseif ($key === 'users') {
            $fetched_data['total_users'] = count($data);

            $total_play_count = 0;

            foreach ($data as $user) {
                $total_play_count += $user['PlayCount'];
            }

            $fetched_data['avg_play_count'] = $fetched_data['total_users'] > 0 ? $total_play_count / $fetched_data['total_users'] : 0;
        } elseif ($key === 'system') {
            $fetched_data['storage_usage'] = $data['StorageUsage'];
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */


/**********************
* Jellyseerr Data Collection
* ----------------------
* This function collects data from Jellyseerr, a request management system for Jellyfin, for dashboard display.
* It fetches information about the requests, users, and overall system status.
*
* Collected Data:
* - Total number of requests
* - Number of requests by status (pending, approved, available)
* - Total number of users
* - Average requests per user
* - System resource utilization (CPU, memory)
*
* Data not collected but available for extension:
* - Detailed request information (title, year, requester, date)
* - User details (username, email, role)
* - Request history and trends
* - Media type and category distribution
* - Notification and email settings
* - Integration and API usage
*
* Opportunities for additional data collection:
* - Request fulfillment time and efficiency
* - User engagement and satisfaction metrics
* - Content popularity and demand analysis
* - Server performance and scalability metrics
* - Third-party service integration and automation
*
* Requirements:
* - Jellyseerr API should be accessible via the provided API URL.
* - API authentication requires an API key.
*
* Parameters:
* - $api_url: The base URL of the Jellyseerr API.
* - $api_key: The API key for authentication.
* - $service_id: The ID of the Jellyseerr service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
***********************/
/* function homelab_fetch_jellyseerr_data($api_url, $api_key, $service_id) {
    $api_url = rtrim($api_url, '/');

    $endpoints = array(
        'requests' => '/request',
        'users' => '/user',
        'system' => '/status',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;

        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-Api-Key' => $api_key,
            ),
        );

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'requests') {
            $fetched_data['total_requests'] = count($data);

            $status_counts = array(
                'pending' => 0,
                'approved' => 0,
                'available' => 0,
            );

            foreach ($data as $request) {
                $status = strtolower($request['status']);
                if (isset($status_counts[$status])) {
                    $status_counts[$status]++;
                }
            }

            $fetched_data['requests_pending'] = $status_counts['pending'];
            $fetched_data['requests_approved'] = $status_counts['approved'];
            $fetched_data['requests_available'] = $status_counts['available'];
        } elseif ($key === 'users') {
            $fetched_data['total_users'] = count($data);

            $total_requests = $fetched_data['total_requests'] ?? 0;
            $fetched_data['avg_requests_per_user'] = $fetched_data['total_users'] > 0 ? $total_requests / $fetched_data['total_users'] : 0;
        } elseif ($key === 'system') {
            $fetched_data['cpu_usage'] = $data['cpu'];
            $fetched_data['memory_usage'] = $data['memory'];
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */


/**********************
* Kavita Data Collection
* ----------------------
* This function collects data from Kavita, a self-hosted digital library management system, for dashboard display.
* It fetches information about the libraries, series, users, and overall system status.
*
* Collected Data:
* - Total number of libraries
* - Number of series per library
* - Total number of users
* - Average series per user
* - System storage utilization
*
* Data not collected but available for extension:
* - Detailed library information (name, description, type)
* - Series details (title, author, genre, release year)
* - User details (username, email, role, last active)
* - Reading progress and history
* - Bookmark and favorite management
* - Plugin and extension data
*
* Opportunities for additional data collection:
* - Library growth and activity trends
* - Series popularity and read count analytics
* - User engagement and retention metrics
* - Content discovery and recommendation insights
* - Server performance and scalability metrics
*
* Requirements:
* - Kavita API should be accessible via the provided API URL.
* - API authentication requires a username and password.
*
* Parameters:
* - $api_url: The base URL of the Kavita API.
* - $username: The username for authentication.
* - $password: The password for authentication.
* - $service_id: The ID of the Kavita service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
***********************/
/* function homelab_fetch_kavita_data($api_url, $username, $password, $service_id) {
    $api_url = rtrim($api_url, '/');

    $endpoints = array(
        'libraries' => '/api/Library',
        'series' => '/api/Series',
        'users' => '/api/User',
        'system' => '/api/System/Info',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;

        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode(array(
                'username' => $username,
                'password' => $password,
            )),
        );

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'libraries') {
            $fetched_data['total_libraries'] = count($data);

            $series_counts = array();

            foreach ($data as $library) {
                $library_id = $library['id'];
                $series_counts[$library_id] = $library['seriesCount'];
            }

            $fetched_data['series_per_library'] = $series_counts;
        } elseif ($key === 'series') {
            $fetched_data['total_series'] = count($data);
        } elseif ($key === 'users') {
            $fetched_data['total_users'] = count($data);

            $total_series = $fetched_data['total_series'] ?? 0;
            $fetched_data['avg_series_per_user'] = $fetched_data['total_users'] > 0 ? $total_series / $fetched_data['total_users'] : 0;
        } elseif ($key === 'system') {
            $fetched_data['storage_usage'] = $data['storageUsage'];
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */

/**********************
* Komga Data Collection
* ----------------------
* This function collects data from Komga, a self-hosted comics and manga server, for dashboard display.
* It fetches information about the libraries, series, users, and overall system status.
*
* Collected Data:
* - Total number of libraries
* - Number of series per library
* - Total number of users
* - Average series per user
* - System storage utilization
*
* Data not collected but available for extension:
* - Detailed library information (name, description, type)
* - Series details (title, author, genre, release year)
* - User details (username, email, role, last active)
* - Reading progress and history
* - Bookmark and collection management
* - Plugin and extension data
*
* Opportunities for additional data collection:
* - Library growth and activity trends
* - Series popularity and read count analytics
* - User engagement and retention metrics
* - Content discovery and recommendation insights
* - Server performance and scalability metrics
*
* Requirements:
* - Komga API should be accessible via the provided API URL.
* - API authentication requires a username and password.
*
* Parameters:
* - $api_url: The base URL of the Komga API.
* - $username: The username for authentication.
* - $password: The password for authentication.
* - $service_id: The ID of the Komga service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
***********************/
/* function homelab_fetch_komga_data($api_url, $username, $password, $service_id) {
    $api_url = rtrim($api_url, '/');

    $endpoints = array(
        'libraries' => '/api/v1/libraries',
        'series' => '/api/v1/series',
        'users' => '/api/v1/users',
        'system' => '/api/v1/system/info',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;

        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($username . ':' . $password),
            ),
        );

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'libraries') {
            $fetched_data['total_libraries'] = count($data['content']);

            $series_counts = array();

            foreach ($data['content'] as $library) {
                $library_id = $library['id'];
                $series_counts[$library_id] = $library['seriesCount'];
            }

            $fetched_data['series_per_library'] = $series_counts;
        } elseif ($key === 'series') {
            $fetched_data['total_series'] = $data['numberOfElements'];
        } elseif ($key === 'users') {
            $fetched_data['total_users'] = count($data['content']);

            $total_series = $fetched_data['total_series'] ?? 0;
            $fetched_data['avg_series_per_user'] = $fetched_data['total_users'] > 0 ? $total_series / $fetched_data['total_users'] : 0;
        } elseif ($key === 'system') {
            $fetched_data['storage_usage'] = $data['storageUsage'];
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
} */




















