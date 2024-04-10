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
        'flows' => '/api/flows',
        'statistics' => '/api/statistics',
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

        if ($key === 'status') {
            $fetched_data['server_status'] = $data['status'] === 'running' ? 'running' : 'stopped';
            $fetched_data['server_version'] = $data['version'];
        } elseif ($key === 'flows') {
            $active_flows = 0;
            foreach ($data as $flow) {
                if ($flow['status'] === 'active') {
                    $active_flows++;
                }
            }
            $fetched_data['active_flows_count'] = $active_flows;
            $fetched_data['total_flows_count'] = count($data);
        } elseif ($key === 'statistics') {
            $fetched_data['processed_files_count'] = $data['processedFiles'];
            $fetched_data['processed_bytes_count'] = $data['processedBytes'];
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}