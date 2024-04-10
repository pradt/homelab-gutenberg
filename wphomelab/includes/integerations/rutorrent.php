<?php
/******************
* ruTorrent Data Collection
* --------------------------
* This function collects data from ruTorrent, a web-based BitTorrent client, for dashboard display.
* It fetches information about the active and completed torrents, download and upload speeds, and disk usage.
*
* Collected Data:
* - Total number of active torrents
* - Total number of completed torrents
* - Current download speed (in bytes per second)
* - Current upload speed (in bytes per second)
* - Total disk space used by torrents
*
* Data not collected but available for extension:
* - Detailed torrent information (name, size, progress, ratio, peers, seeds)
* - Torrent file list and file priorities
* - Torrent trackers and tracker status
* - Torrent labels or categories
* - Torrent download and upload limits
* - Torrent queue and priority settings
* - ruTorrent configuration and settings
*
* Requirements:
* - ruTorrent API should be accessible via the provided API URL.
* - API authentication (username and password) may be required depending on ruTorrent configuration.
*
* Parameters:
* - $api_url: The base URL of the ruTorrent API.
* - $username: The username for authentication (if required).
* - $password: The password for authentication (if required).
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*
* Example of fetched_data structure:
* {
*   "active_torrents": 5,
*   "completed_torrents": 10,
*   "download_speed": 1024000,
*   "upload_speed": 512000,
*   "disk_space_used": 107374182400
* }
*******************/
function homelab_fetch_rutorrent_data($api_url, $username = '', $password = '', $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'torrents' => '/plugins/httprpc/action.php',
    );
    
    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;
    
    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $args = array(
            'body' => json_encode(array(
                'mode' => 'list',
            )),
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
        );
        
        if (!empty($username) && !empty($password)) {
            $args['headers']['Authorization'] = 'Basic ' . base64_encode($username . ':' . $password);
        }
        
        $response = wp_remote_post($url, $args);
        
        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($key === 'torrents') {
            $active_torrents = 0;
            $completed_torrents = 0;
            $download_speed = 0;
            $upload_speed = 0;
            $disk_space_used = 0;
            
            foreach ($data as $torrent) {
                if ($torrent['state'] === 'active') {
                    $active_torrents++;
                } elseif ($torrent['state'] === 'complete') {
                    $completed_torrents++;
                }
                
                $download_speed += $torrent['dl_speed'];
                $upload_speed += $torrent['up_speed'];
                $disk_space_used += $torrent['size_bytes'];
            }
            
            $fetched_data['active_torrents'] = $active_torrents;
            $fetched_data['completed_torrents'] = $completed_torrents;
            $fetched_data['download_speed'] = $download_speed;
            $fetched_data['upload_speed'] = $upload_speed;
            $fetched_data['disk_space_used'] = $disk_space_used;
        }
    }
    
    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}