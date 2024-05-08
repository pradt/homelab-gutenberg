<?php
/******************
* Overseerr Data Collection
* -------------------------
* This function collects data from Overseerr, a request management and media discovery tool, for dashboard display.
* It fetches information about the media request counts using the /api/v1/request/count endpoint.
*
* Collected Data:
* - Total number of media requests
* - Number of movie requests
* - Number of TV show requests
* - Number of pending requests
* - Number of approved requests
* - Number of declined requests
* - Number of processing requests
* - Number of available requests
*
* Requirements:
* - Overseerr API should be accessible via the provided API URL.
* - API authentication token (API key) is required for accessing the Overseerr API.
*
* Parameters:
* - $api_url: The base URL of the Overseerr API.
* - $api_key: The API key for authentication.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/

function homelab_fetch_overseerr_data($api_url, $api_key, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoint = '/api/v1/request/count';
    
    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    $url = $api_url . $endpoint;
    $args = array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'X-Api-Key' => $api_key,
        ),
    );
    
    $response = wp_remote_get($url, $args);
    
    if (is_wp_error($response)) {
        $error_message = "API request failed for endpoint '{$endpoint}': " . $response->get_error_message();
        $error_timestamp = current_time('mysql');
    } else {
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        $fetched_data = array(
            'total_requests' => $data['total'],
            'movie_requests' => $data['movie'],
            'tv_requests' => $data['tv'],
            'pending_requests' => $data['pending'],
            'approved_requests' => $data['approved'],
            'declined_requests' => $data['declined'],
            'processing_requests' => $data['processing'],
            'available_requests' => $data['available'],
        );
    }
    
    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}