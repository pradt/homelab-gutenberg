<?php 
/******************
* pyLoad Data Collection
* ----------------------
* This function collects data from pyLoad, a download manager, for dashboard display.
* It fetches information about the downloads, including their status, progress, speed, and estimated time left.
*
* Collected Data:
* - Total number of downloads
* - Number of downloads by status (finished, queued, skipped, failed)
* - Current download speed
* - Total downloaded size
* - Total download progress percentage
*
* Data not collected but available for extension:
* - Detailed download information (name, size, added date, finished date)
* - Package and file-level details
* - Captcha and account management settings
* - Bandwidth and traffic usage statistics
* - Historical download data and logs
* - pyLoad configuration and settings
*
* Example of fetched_data structure:
* {
*   "total_downloads": 50,
*   "downloads_finished": 30,
*   "downloads_queued": 10,
*   "downloads_skipped": 5,
*   "downloads_failed": 5,
*   "current_speed": 1024,
*   "total_size": 10737418240,
*   "total_progress": 75
* }
*
* Requirements:
* - pyLoad API should be accessible via the provided API URL.
* - API authentication using username and password is required.
*
* Parameters:
* - $api_url: The base URL of the pyLoad API.
* - $username: The username for authentication.
* - $password: The password for authentication.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_pyload_data($api_url, $username, $password, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'downloads' => '/api/downloads',
        'status' => '/api/statusServer',
    );
    
    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;
    
    $login_url = $api_url . '/api/login';
    $login_data = array(
        'username' => $username,
        'password' => $password,
    );
    
    $login_response = wp_remote_post($login_url, array(
        'headers' => array('Content-Type' => 'application/json'),
        'body' => json_encode($login_data),
    ));
    
    if (is_wp_error($login_response)) {
        $error_message = "Login request failed: " . $login_response->get_error_message();
        $error_timestamp = current_time('mysql');
        homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
        return $fetched_data;
    }
    
    $login_data = json_decode(wp_remote_retrieve_body($login_response), true);
    $session_id = $login_data['session'];
    
    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Cookie' => 'session=' . $session_id,
            ),
        );
        
        $response = wp_remote_get($url, $args);
        
        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($key === 'downloads') {
            $fetched_data['total_downloads'] = count($data);
            
            $status_counts = array(
                'finished' => 0,
                'queued' => 0,
                'skipped' => 0,
                'failed' => 0,
            );
            
            foreach ($data as $download) {
                $status = strtolower($download['status']);
                if (isset($status_counts[$status])) {
                    $status_counts[$status]++;
                }
            }
            
            $fetched_data['downloads_finished'] = $status_counts['finished'];
            $fetched_data['downloads_queued'] = $status_counts['queued'];
            $fetched_data['downloads_skipped'] = $status_counts['skipped'];
            $fetched_data['downloads_failed'] = $status_counts['failed'];
        } elseif ($key === 'status') {
            $fetched_data['current_speed'] = $data['speed'];
            $fetched_data['total_size'] = $data['total'];
            $fetched_data['total_progress'] = $data['download'];
        }
    }
    
    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    
    return $fetched_data;
}