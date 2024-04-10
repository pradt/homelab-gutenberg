<?php
/******************
* qBittorrent Data Collection
* ----------------------
* This function collects data from qBittorrent, a BitTorrent client, for dashboard display.
* It fetches information about the active torrents, including their status, progress, download and upload speeds, and more.
*
* Collected Data:
* - Total number of active torrents
* - Number of torrents by state (downloading, seeding, completed, paused, error)
* - Total download and upload speeds
* - Total size of torrents (completed and remaining)
* - Overall progress percentage
*
* Data not collected but available for extension:
* - Detailed torrent information (name, hash, category, tags)
* - Torrent-specific download and upload speeds
* - Torrent health and availability
* - Torrent activity history
* - qBittorrent settings and preferences
*
* Requirements:
* - qBittorrent Web API should be accessible via the provided API URL.
* - API authentication (username and password) is required to access the qBittorrent API.
*
* Parameters:
* - $api_url: The base URL of the qBittorrent Web API.
* - $username: The username for API authentication.
* - $password: The password for API authentication.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*
* Example of $fetched_data structure:
* {
*   "total_torrents": 10,
*   "downloading_torrents": 3,
*   "seeding_torrents": 5,
*   "completed_torrents": 2,
*   "paused_torrents": 0,
*   "error_torrents": 0,
*   "total_download_speed": 1024,
*   "total_upload_speed": 512,
*   "total_size": 50000000000,
*   "total_size_remaining": 10000000000,
*   "overall_progress": 80
* }
*******************/
function homelab_fetch_qbittorrent_data($api_url, $username, $password, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'torrents' => '/api/v2/torrents/info',
        'transfer' => '/api/v2/transfer/info',
    );
    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    $args = array(
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode(array(
            'username' => $username,
            'password' => $password,
        )),
    );

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'torrents') {
            $fetched_data['total_torrents'] = count($data);
            $state_counts = array(
                'downloading' => 0,
                'seeding' => 0,
                'completed' => 0,
                'paused' => 0,
                'error' => 0,
            );
            $total_size = 0;
            $total_size_remaining = 0;

            foreach ($data as $torrent) {
                $state = strtolower($torrent['state']);
                if (isset($state_counts[$state])) {
                    $state_counts[$state]++;
                }
                $total_size += $torrent['size'];
                $total_size_remaining += $torrent['size'] - $torrent['completed'];
            }

            $fetched_data['downloading_torrents'] = $state_counts['downloading'];
            $fetched_data['seeding_torrents'] = $state_counts['seeding'];
            $fetched_data['completed_torrents'] = $state_counts['completed'];
            $fetched_data['paused_torrents'] = $state_counts['paused'];
            $fetched_data['error_torrents'] = $state_counts['error'];
            $fetched_data['total_size'] = $total_size;
            $fetched_data['total_size_remaining'] = $total_size_remaining;
            $fetched_data['overall_progress'] = $total_size > 0 ? round((($total_size - $total_size_remaining) / $total_size) * 100) : 0;
        } elseif ($key === 'transfer') {
            $fetched_data['total_download_speed'] = $data['dl_info_speed'];
            $fetched_data['total_upload_speed'] = $data['up_info_speed'];
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}