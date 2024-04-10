<?php
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

 function homelab_fetch_cytube_data($api_url, $api_key, $service_id, $enable_now_playing = false, $enable_blocks = false) {
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
}