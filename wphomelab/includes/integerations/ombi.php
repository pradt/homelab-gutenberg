<?php
/******************
* Ombi Data Collection
* ----------------------
* This function collects data from Ombi, a media request management system, for dashboard display.
* It fetches information about media requests, including the total number of requests, pending requests,
* approved requests, and available requests.
*
* Collected Data:
* - Total number of media requests
* - Number of pending requests
* - Number of approved requests
* - Number of available requests
*
* Data not collected but available for extension:
* - Detailed request information (title, type, status, requester, request date)
* - User-specific request data (requests per user)
* - Media-specific data (movies, TV shows, music)
* - Request trend data (requests over time)
* - Ombi configuration and settings
*
* Example of fetched_data structure:
* {
*   "total_requests": 100,
*   "pending_requests": 20,
*   "approved_requests": 50,
*   "available_requests": 30
* }
*
* Requirements:
* - Ombi API should be accessible via the provided API URL.
* - API authentication token (API key) is required for accessing the Ombi API.
*
* Parameters:
* - $api_url: The base URL of the Ombi API.
* - $api_key: The API key for authentication.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_ombi_data($api_url, $api_key, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'requests' => '/api/v1/Request/count',
    );
    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'ApiKey' => $api_key,
            ),
        );

        $response = wp_remote_get($url, $args);
        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        if ($key === 'requests') {
            $fetched_data['total_requests'] = $data['total'];
            $fetched_data['pending_requests'] = $data['pending'];
            $fetched_data['approved_requests'] = $data['approved'];
            $fetched_data['available_requests'] = $data['available'];
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}