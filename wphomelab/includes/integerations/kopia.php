<?php
/******************
* Kopia Data Collection
* ----------------------
* This function collects data from Kopia, a backup and restore tool, for dashboard display.
* It fetches information about the backup status, repository statistics, and snapshot details.
*
* Collected Data:
* - Repository status (connected, disconnected)
* - Repository ID
* - Total data size
* - Last snapshot timestamp
* - Number of snapshots
* - Total backup files count
* - Total backup directories count
*
* Data not collected but available for extension:
* - Detailed snapshot information (snapshot ID, source, duration, size)
* - Backup performance metrics (throughput, upload/download speeds)
* - Backup schedule and policy details
* - Restore history and status
* - Kopia configuration and settings
*
* Additional opportunities for data collection:
* - Repository storage breakdown (local, cloud, etc.)
* - Backup retention policy details
* - Backup job status and progress
* - Error and warning logs
* - User activity and access logs
*
* Requirements:
* - Kopia API should be accessible via the provided API URL.
* - API authentication requires a username and password.
*
* Parameters:
* - $api_url: The base URL of the Kopia API.
* - $username: The username for API authentication.
* - $password: The password for API authentication.
* - $service_id: The ID of the Kopia service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*
* Example of fetched_data structure:
* {
*   "repository_status": "connected",
*   "repository_id": "abcdef123456",
*   "total_data_size": 1099511627776,
*   "last_snapshot_timestamp": "2023-05-20T10:30:00Z",
*   "total_snapshots": 100,
*   "total_files": 1000000,
*   "total_directories": 50000
* }
*******************/
function homelab_fetch_kopia_data($api_url, $username, $password, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'repo' => '/api/v1/repo',
        'stats' => '/api/v1/repo/stats',
        'snapshots' => '/api/v1/snapshots',
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
            'auth' => array($username, $password),
        );
        
        $response = wp_remote_get($url, $args);
        
        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($key === 'repo') {
            $fetched_data['repository_status'] = $data['connected'] ? 'connected' : 'disconnected';
            $fetched_data['repository_id'] = $data['id'];
        } elseif ($key === 'stats') {
            $fetched_data['total_data_size'] = $data['total_size'];
            $fetched_data['total_files'] = $data['total_file_count'];
            $fetched_data['total_directories'] = $data['total_dir_count'];
        } elseif ($key === 'snapshots') {
            $fetched_data['total_snapshots'] = count($data);
            if (!empty($data)) {
                $last_snapshot = end($data);
                $fetched_data['last_snapshot_timestamp'] = $last_snapshot['start_time'];
            }
        }
    }
    
    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    
    return $fetched_data;
}