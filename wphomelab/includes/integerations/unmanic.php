<?php
/******************
* Unmanic Data Collection
* ----------------------
* This function collects data from Unmanic, a video library tool, for dashboard display.
* It fetches information about the video library, including the total number of videos,
* the count of videos by status (queued, in-progress, completed, failed), and disk usage.
*
* Collected Data:
* - Total number of videos in the library
* - Number of videos by status (queued, in-progress, completed, failed)
* - Total disk space used by the video library
* - Available disk space
*
* Data not collected but available for extension:
* - Detailed video information (title, file path, duration, resolution, codec)
* - Video metadata (tags, genres, release year)
* - Video thumbnails or poster images
* - Video playback statistics (views, watch time)
* - User-specific video library data (watch history, favorites, playlists)
* - Unmanic settings and configuration
*
* Example of fetched_data structure:
* {
*   "total_videos": 100,
*   "videos_queued": 10,
*   "videos_in_progress": 5,
*   "videos_completed": 80,
*   "videos_failed": 5,
*   "total_disk_space": "500 GB",
*   "available_disk_space": "250 GB"
* }
*
* Requirements:
* - Unmanic API should be accessible via the provided API URL.
* - API authentication token (API key) may be required depending on Unmanic configuration.
*
* Parameters:
* - $api_url: The base URL of the Unmanic API.
* - $api_key: The API key for authentication (if required).
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_unmanic_data($api_url, $api_key = '', $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'videos' => '/api/v1/videos',
        'disk_usage' => '/api/v1/disk-usage',
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
        );
        
        if (!empty($api_key)) {
            $args['headers']['Authorization'] = 'Bearer ' . $api_key;
        }
        
        $response = wp_remote_get($url, $args);
        
        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($key === 'videos') {
            $fetched_data['total_videos'] = count($data);
            
            $status_counts = array(
                'queued' => 0,
                'in_progress' => 0,
                'completed' => 0,
                'failed' => 0,
            );
            
            foreach ($data as $video) {
                $status = strtolower($video['status']);
                if (isset($status_counts[$status])) {
                    $status_counts[$status]++;
                }
            }
            
            $fetched_data['videos_queued'] = $status_counts['queued'];
            $fetched_data['videos_in_progress'] = $status_counts['in_progress'];
            $fetched_data['videos_completed'] = $status_counts['completed'];
            $fetched_data['videos_failed'] = $status_counts['failed'];
        } elseif ($key === 'disk_usage') {
            $fetched_data['total_disk_space'] = $data['total'];
            $fetched_data['available_disk_space'] = $data['available'];
        }
    }
    
    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    
    return $fetched_data;
}