<?php
/******************
* Tdarr Data Collection
* ----------------------
* This function collects data from Tdarr, a distributed transcoding system, for dashboard display.
* It fetches information about the state of the Tdarr system, including the number of nodes, libraries, and active transcode jobs.
*
* Collected Data:
* - Total number of nodes in the Tdarr system
* - Number of active nodes
* - Number of inactive nodes
* - Total number of libraries
* - Number of libraries being processed
* - Number of libraries waiting for processing
* - Total number of active transcode jobs
* - Total number of queued transcode jobs
* - Total number of completed transcode jobs
* - Total number of failed transcode jobs
*
* Data not collected but available for extension:
* - Detailed node information (ID, hostname, IP address, status)
* - Node-specific performance metrics (CPU usage, memory usage)
* - Detailed library information (name, path, status, progress)
* - Transcode job details (input file, output file, settings, progress)
* - Historical data on transcode jobs (start time, end time, duration)
* - Tdarr configuration and settings
*
* Example data structure stored in $fetched_data:
* {
*   "total_nodes": 5,
*   "active_nodes": 4,
*   "inactive_nodes": 1,
*   "total_libraries": 10,
*   "processing_libraries": 2,
*   "waiting_libraries": 8,
*   "active_transcode_jobs": 15,
*   "queued_transcode_jobs": 30,
*   "completed_transcode_jobs": 100,
*   "failed_transcode_jobs": 5
* }
*
* Requirements:
* - Tdarr API should be accessible via the provided API URL.
* - API authentication token (API key) may be required depending on Tdarr configuration.
*
* Parameters:
* - $api_url: The base URL of the Tdarr API.
* - $api_key: The API key for authentication (if required).
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_tdarr_data($api_url, $api_key = '', $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'nodes' => '/api/v2/nodes',
        'libraries' => '/api/v2/libraries',
        'transcode_jobs' => '/api/v2/transcodejobs',
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

        switch ($key) {
            case 'nodes':
                $fetched_data['total_nodes'] = count($data);
                $fetched_data['active_nodes'] = count(array_filter($data, function ($node) {
                    return $node['status'] === 'active';
                }));
                $fetched_data['inactive_nodes'] = $fetched_data['total_nodes'] - $fetched_data['active_nodes'];
                break;

            case 'libraries':
                $fetched_data['total_libraries'] = count($data);
                $fetched_data['processing_libraries'] = count(array_filter($data, function ($library) {
                    return $library['status'] === 'processing';
                }));
                $fetched_data['waiting_libraries'] = count(array_filter($data, function ($library) {
                    return $library['status'] === 'waiting';
                }));
                break;

            case 'transcode_jobs':
                $fetched_data['active_transcode_jobs'] = count(array_filter($data, function ($job) {
                    return $job['status'] === 'active';
                }));
                $fetched_data['queued_transcode_jobs'] = count(array_filter($data, function ($job) {
                    return $job['status'] === 'queued';
                }));
                $fetched_data['completed_transcode_jobs'] = count(array_filter($data, function ($job) {
                    return $job['status'] === 'completed';
                }));
                $fetched_data['failed_transcode_jobs'] = count(array_filter($data, function ($job) {
                    return $job['status'] === 'failed';
                }));
                break;
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}