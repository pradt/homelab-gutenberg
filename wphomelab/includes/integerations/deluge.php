<?php
/******************
* Deluge Data Collection
* ----------------------
* This function collects data from Deluge, a BitTorrent client, for dashboard display.
* It fetches information about the torrents, including their status, download progress, upload and download speeds, and other relevant metrics.
*
* Collected Data:
* - Total number of torrents
* - Number of torrents by state (downloading, seeding, paused, error)
* - Total upload and download speeds
* - Total uploaded and downloaded data
* - Total size of all torrents
*
* Data not collected but available for extension:
* - Detailed torrent information (name, size, hash, tracker)
* - Torrent-specific progress, upload and download speeds, and ratios
* - Torrent labels and categories
* - Torrent peers and seeds information
* - Deluge configuration and settings
*
* Opportunities for additional data:
* - Historical data on torrent activity (added, completed, removed)
* - Bandwidth usage statistics over time
* - Disk space utilization for torrents
* - Deluge plugins and their status
*
* Requirements:
* - Deluge API should be accessible via the provided API URL.
* - API authentication (username and password) is required to access the Deluge API.
*
* Parameters:
* - $api_url: The base URL of the Deluge API.
* - $username: The username for API authentication.
* - $password: The password for API authentication.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*
* Example of fetched_data structure:
* {
*   "total_torrents": 50,
*   "torrents_downloading": 10,
*   "torrents_seeding": 35,
*   "torrents_paused": 4,
*   "torrents_error": 1,
*   "total_upload_speed": 1500,
*   "total_download_speed": 3000,
*   "total_uploaded": 1000000000,
*   "total_downloaded": 5000000000,
*   "total_size": 10000000000
* }
*******************/
function homelab_fetch_deluge_data($api_url, $username, $password, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'torrents' => '/json',
    );
    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    // Authenticate and retrieve the session ID
    $auth_data = array(
        'method' => 'auth.login',
        'params' => array($password),
        'id' => 1,
    );
    $auth_response = wp_remote_post($api_url, array(
        'headers' => array('Content-Type' => 'application/json'),
        'body' => json_encode($auth_data),
    ));
    if (is_wp_error($auth_response)) {
        $error_message = "Authentication failed: " . $auth_response->get_error_message();
        $error_timestamp = current_time('mysql');
        homelab_save_service_data($service_id, array(), $error_message, $error_timestamp);
        return array();
    }
    $auth_result = json_decode(wp_remote_retrieve_body($auth_response), true);
    $session_id = $auth_result['result'];

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $data = array(
            'method' => $key . '.get',
            'params' => array($session_id),
            'id' => 1,
        );
        $response = wp_remote_post($url, array(
            'headers' => array('Content-Type' => 'application/json'),
            'body' => json_encode($data),
        ));
        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }
        $result = json_decode(wp_remote_retrieve_body($response), true);
        if ($key === 'torrents') {
            $fetched_data['total_torrents'] = count($result['result']);
            $state_counts = array(
                'downloading' => 0,
                'seeding' => 0,
                'paused' => 0,
                'error' => 0,
            );
            $total_upload_speed = 0;
            $total_download_speed = 0;
            $total_uploaded = 0;
            $total_downloaded = 0;
            $total_size = 0;
            foreach ($result['result'] as $torrent) {
                $state = strtolower($torrent['state']);
                if (isset($state_counts[$state])) {
                    $state_counts[$state]++;
                }
                $total_upload_speed += $torrent['upload_payload_rate'];
                $total_download_speed += $torrent['download_payload_rate'];
                $total_uploaded += $torrent['total_uploaded'];
                $total_downloaded += $torrent['total_done'];
                $total_size += $torrent['total_size'];
            }
            $fetched_data['torrents_downloading'] = $state_counts['downloading'];
            $fetched_data['torrents_seeding'] = $state_counts['seeding'];
            $fetched_data['torrents_paused'] = $state_counts['paused'];
            $fetched_data['torrents_error'] = $state_counts['error'];
            $fetched_data['total_upload_speed'] = $total_upload_speed;
            $fetched_data['total_download_speed'] = $total_download_speed;
            $fetched_data['total_uploaded'] = $total_uploaded;
            $fetched_data['total_downloaded'] = $total_downloaded;
            $fetched_data['total_size'] = $total_size;
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}