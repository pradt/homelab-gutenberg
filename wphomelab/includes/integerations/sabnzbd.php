<?php
/******************
* SABnzbd Data Collection
* ----------------------
* This function collects data from SABnzbd, a Usenet download client, for dashboard display.
* It fetches information about the current downloads, queue status, and server statistics.
*
* Collected Data:
* - Total number of downloads in the queue
* - Number of active downloads
* - Download queue size (in bytes)
* - Total download speed (in bytes per second)
* - Disk space usage (free and total)
*
* Data not collected but available for extension:
* - Detailed download information (name, size, status, category, priority)
* - Download history and statistics
* - Server information (host, port, SSL, username)
* - Configuration settings
* - Scheduler and RSS feed settings
*
* Opportunities for additional data:
* - Download completion notifications
* - Integration with media management tools (e.g., Sonarr, Radarr)
* - Historical download trends and analytics
*
* Requirements:
* - SABnzbd API should be accessible via the provided API URL.
* - API key is required for authentication.
*
* Parameters:
* - $api_url: The base URL of the SABnzbd API.
* - $api_key: The API key for authentication.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*
* Example of fetched_data structure:
* {
*   "total_downloads": 10,
*   "active_downloads": 2,
*   "queue_size": 5368709120,
*   "download_speed": 1048576,
*   "disk_free": 107374182400,
*   "disk_total": 214748364800
* }
*******************/
function homelab_fetch_sabnzbd_data($api_url, $api_key, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'queue' => '/api?mode=queue&output=json&apikey=' . $api_key,
        'server_stats' => '/api?mode=server_stats&output=json&apikey=' . $api_key,
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

        if ($key === 'queue') {
            $fetched_data['total_downloads'] = count($data['queue']['slots']);
            $fetched_data['active_downloads'] = $data['queue']['noofslots_downloading'];
            $fetched_data['queue_size'] = $data['queue']['mbleft'];
            $fetched_data['download_speed'] = $data['queue']['kbpersec'] * 1024;
        } elseif ($key === 'server_stats') {
            $fetched_data['disk_free'] = $data['diskspace1'];
            $fetched_data['disk_total'] = $data['diskspacetotal1'];
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}