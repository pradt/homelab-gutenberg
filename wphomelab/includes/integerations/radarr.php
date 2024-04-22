<?php
/******************
* Radarr Data Collection
* ----------------------
* This function collects data from Radarr, a movie collection management tool, for dashboard display.
* It fetches information about the movie library, including the total number of movies, movie status counts,
* and disk space usage.
*
* Collected Data:
* - Total number of movies
* - Number of movies by status (monitored, unmonitored, available, missing)
* - Total disk space used by the movie library
* - Total file size of the movie library
*
* Data not collected but available for extension:
* - Detailed movie information (title, year, rating, overview)
* - Movie file details (file path, size, quality, codecs)
* - Movie collection and tag information
* - Radarr configuration and settings
* - Download client and indexer integration details
* - Radarr update and backup status
*
* Opportunities for additional data:
* - Recently added movies
* - Upcoming movie releases
* - Movie recommendations based on library content
* - Library growth and activity stats over time
* - Custom filtering and sorting options for movies
*
* Example fetched_data structure:
* {
*   "total_movies": 500,
*   "monitored_movies": 450,
*   "unmonitored_movies": 50,
*   "available_movies": 400,
*   "missing_movies": 100,
*   "total_disk_space": "2 TB",
*   "total_file_size": "1.5 TB"
* }
*
* Requirements:
* - Radarr API should be accessible via the provided API URL.
* - API authentication key may be required depending on Radarr configuration.
*
* Parameters:
* - $api_url: The base URL of the Radarr API.
* - $api_key: The API key for authentication (if required).
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_radarr_data($api_url, $api_key = '', $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'movies' => '/api/v3/movie',
        'diskspace' => '/api/v3/diskspace',
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
            $args['headers']['X-Api-Key'] = $api_key;
        }
        
        $response = wp_remote_get($url, $args);
        
        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($key === 'movies') {
            $fetched_data['total_movies'] = count($data);
            
            $status_counts = array(
                'monitored' => 0,
                'unmonitored' => 0,
                'available' => 0,
                'missing' => 0,
            );
            
            foreach ($data as $movie) {
                if ($movie['monitored']) {
                    $status_counts['monitored']++;
                } else {
                    $status_counts['unmonitored']++;
                }
                
                if ($movie['hasFile']) {
                    $status_counts['available']++;
                } else {
                    $status_counts['missing']++;
                }
            }
            
            $fetched_data['monitored_movies'] = $status_counts['monitored'];
            $fetched_data['unmonitored_movies'] = $status_counts['unmonitored'];
            $fetched_data['available_movies'] = $status_counts['available'];
            $fetched_data['missing_movies'] = $status_counts['missing'];
        }
        
        if ($key === 'diskspace') {
            $total_disk_space = 0;
            $total_file_size = 0;
            
            foreach ($data as $disk) {
                $total_disk_space += $disk['totalSpace'];
                $total_file_size += $disk['totalUsed'];
            }
            
            $fetched_data['total_disk_space'] = homelab_format_bytes($total_disk_space);
            $fetched_data['total_file_size'] = homelab_format_bytes($total_file_size);
        }
    }
    
    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}