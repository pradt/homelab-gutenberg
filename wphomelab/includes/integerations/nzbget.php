<?php
/******************
* NZBGet Data Collection
* ----------------------
* This function collects data from NZBGet, a Usenet download client, for dashboard display.
* It fetches information about the current downloads, queue status, and performance statistics.
*
* Collected Data:
* - Total number of downloads in the queue
* - Number of downloads by status (downloading, paused, completed, failed)
* - Total download speed
* - Total upload speed
* - Remaining download size
* - Download disk usage
*
* Data not collected but available for extension:
* - Detailed download information (filename, category, size, progress)
* - Download history and statistics
* - Server and connection status
* - NZBGet configuration and settings
*
* Opportunities for additional data collection:
* - Download performance over time (hourly, daily, weekly)
* - Download categories and distribution
* - Server health and performance metrics
* - Disk space utilization and trends
*
* Requirements:
* - NZBGet API should be accessible via the provided API URL.
* - API authentication requires a username and password.
*
* Parameters:
* - $api_url: The base URL of the NZBGet API.
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
*   "total_downloads": 10,
*   "downloading": 3,
*   "paused": 1,
*   "completed": 5,
*   "failed": 1,
*   "download_speed": 5000000,
*   "upload_speed": 100000,
*   "remaining_size": 10737418240,
*   "disk_usage": 53687091200
* }
*******************/
function homelab_fetch_nzbget_data($api_url, $username, $password, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'status' => '/jsonrpc/status',
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
                'method' => 'status',
                'params' => array(),
            )),
            'auth' => array($username, $password),
        );
        
        $response = wp_remote_post($url, $args);
        
        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($key === 'status') {
            $fetched_data['total_downloads'] = $data['result']['RemainingSizeLo'];
            $fetched_data['downloading'] = $data['result']['DownloadRate'];
            $fetched_data['paused'] = $data['result']['DownloadPaused'];
            $fetched_data['completed'] = $data['result']['PostJobCount'];
            $fetched_data['failed'] = $data['result']['ServerStandBy'];
            $fetched_data['download_speed'] = $data['result']['DownloadRate'];
            $fetched_data['upload_speed'] = $data['result']['AverageUploadRate'];
            $fetched_data['remaining_size'] = $data['result']['RemainingSizeMB'];
            $fetched_data['disk_usage'] = $data['result']['ServerDiskSpace'];
        }
    }
    
    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    
    return $fetched_data;
}
