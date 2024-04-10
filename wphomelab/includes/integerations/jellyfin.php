<?php
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
function homelab_fetch_jellyfin_data($api_url, $api_key, $service_id) {
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
}