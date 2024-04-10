<?php
/******************
* Tube Archivist Data Collection
* ------------------------------
* This function collects data from Tube Archivist, a YouTube video archiving tool, for dashboard display.
* It fetches information about the archived videos, including their count, total duration, and storage usage.
*
* Collected Data:
* - Total number of archived videos
* - Total duration of archived videos (in seconds)
* - Total storage used by archived videos (in bytes)
* - Number of videos by status (downloaded, queued, failed)
*
* Data not collected but available for extension:
* - Detailed video information (title, description, tags, thumbnail URL)
* - Video-specific metrics (views, likes, comments)
* - Channel and playlist information
* - Download progress and status updates
* - Archive settings and configurations
*
* Requirements:
* - Tube Archivist API should be accessible via the provided API URL.
* - API authentication key (API key) is required for accessing the API.
*
* Parameters:
* - $api_url: The base URL of the Tube Archivist API.
* - $api_key: The API key for authentication.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*
* Example of fetched_data structure:
* {
*   "total_videos": 500,
*   "total_duration": 180000,
*   "total_storage": 10737418240,
*   "videos_downloaded": 450,
*   "videos_queued": 30,
*   "videos_failed": 20
* }
*******************/
function homelab_fetch_tube_archivist_data($api_url, $api_key, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'videos' => '/api/v1/videos',
        'stats' => '/api/v1/stats',
    );
    
    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;
    
    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key,
            ),
        );
        
        $response = wp_remote_get($url, $args);
        
        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($key === 'videos') {
            $fetched_data['total_videos'] = count($data);
            
            $total_duration = 0;
            $status_counts = array(
                'downloaded' => 0,
                'queued' => 0,
                'failed' => 0,
            );
            
            foreach ($data as $video) {
                $total_duration += $video['duration'];
                $status = strtolower($video['status']);
                if (isset($status_counts[$status])) {
                    $status_counts[$status]++;
                }
            }
            
            $fetched_data['total_duration'] = $total_duration;
            $fetched_data['videos_downloaded'] = $status_counts['downloaded'];
            $fetched_data['videos_queued'] = $status_counts['queued'];
            $fetched_data['videos_failed'] = $status_counts['failed'];
        } elseif ($key === 'stats') {
            $fetched_data['total_storage'] = $data['total_storage'];
        }
    }
    
    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}