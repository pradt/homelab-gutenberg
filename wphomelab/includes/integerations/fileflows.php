<?php
/******************
 * FileFlows Data Collection
 * -------------------------
 * This function collects data from the FileFlows API for dashboard display, without requiring authentication.
 * It fetches the status of the FileFlows server, the number of active flows, and other relevant information.
 *
 * Collected Data:
 * - FileFlows server status (running or stopped)
 * - Number of active flows
 * - Total number of flows
 * - Total number of processed files
 * - Total number of processed bytes
 * - FileFlows server version
 *
 * Data not collected but available for extension:
 * - Detailed flow information (name, description, status, etc.)
 * - Flow execution history and statistics
 * - Node and worker information
 * - Error logs and diagnostics
 * - User and authentication data
 *
 * Authentication:
 * This function does not require authentication to access the FileFlows API.
 *
 * Parameters:
 * - $api_url: The base URL of the FileFlows API.
 * - $service_id: The ID of the service being monitored.
 *
 * Error Handling:
 * - Captures any errors encountered during the API request process.
 * - Stores error messages and timestamps for troubleshooting.
 *******************/

 function homelab_fetch_fileflows_data($api_url, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'status' => '/api/status',
        //'flows' => '/api/flows',
        //'statistics' => '/api/statistics',
    );
    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    $fetched_data['raw_responses'] = array();

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            error_log("FileFlows API Error: " . $error_message);
            continue;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $fetched_data['raw_responses'][$key] = $response_body;

        if ($response_code !== 200) {
            $error_message = "API request failed for endpoint '{$key}' with status code: " . $response_code;
            $error_timestamp = current_time('mysql');
            error_log("FileFlows API Error: " . $error_message);
            error_log("Response Body: " . $response_body);
            continue;
        }

        $content_type = wp_remote_retrieve_header($response, 'content-type');
        if (strpos($content_type, 'application/json') === false) {
            $error_message = "Unexpected content type for endpoint '{$key}': " . $content_type;
            $error_timestamp = current_time('mysql');
            error_log("FileFlows API Error: " . $error_message);
            continue;
        }

        $data = json_decode($response_body, true);

        if ($data === null) {
            $error_message = "Invalid API response for endpoint '{$key}': " . $response_body;
            $error_timestamp = current_time('mysql');
            error_log("FileFlows API Error: " . $error_message);
            continue;
        }

        if ($key === 'status') {
            if (isset($data['queue']) && isset($data['processing'])) {
                $fetched_data['queue'] = $data['queue'];
                $fetched_data['processing'] = $data['processing'];
            } else {
                $error_message = "Missing required fields in 'status' API response: " . $response_body;
                $error_timestamp = current_time('mysql');
                error_log("FileFlows API Error: " . $error_message);
            }
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}