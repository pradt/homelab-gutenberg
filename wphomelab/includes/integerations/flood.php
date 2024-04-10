<?php
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

 function homelab_fetch_flood_data($api_url, $username = '', $password = '', $service_id) {
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
}