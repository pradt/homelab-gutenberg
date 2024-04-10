<?php
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

 function homelab_fetch_emby_data($api_url, $api_key, $service_id, $enable_now_playing = false, $enable_recently_added = false) {
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
}