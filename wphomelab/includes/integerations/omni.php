<?php
/******************
* Omni Data Collection
* ----------------------
* This function collects data from Omni, a log management and analysis platform, for dashboard display.
* It fetches information about the log entries, including their severity levels, counts, and trends.
*
* Collected Data:
* - Total number of log entries
* - Number of log entries by severity level (critical, error, warning, info, debug)
* - Average number of log entries per day
* - Trend of log entries over time (increasing, decreasing, stable)
*
* Data not collected but available for extension:
* - Detailed log entry information (timestamp, message, source, tags)
* - Log entry search and filtering capabilities
* - Aggregated log statistics by source, tags, or custom fields
* - Alerting and notification settings based on log patterns
* - Integration with other tools and platforms
* - Omni configuration and settings
*
* Example of fetched_data structure:
* {
*   "total_logs": 10000,
*   "severity_counts": {
*     "critical": 100,
*     "error": 500,
*     "warning": 1000,
*     "info": 5000,
*     "debug": 3400
*   },
*   "avg_logs_per_day": 1000,
*   "log_trend": "increasing"
* }
*
* Requirements:
* - Omni API should be accessible via the provided API URL.
* - API authentication token (API key) is required for making requests to the Omni API.
*
* Parameters:
* - $api_url: The base URL of the Omni API.
* - $api_key: The API key for authentication.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_omni_data($api_url, $api_key, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'logs' => '/api/v1/logs',
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

        if ($key === 'logs') {
            $fetched_data['total_logs'] = $data['total'];
            $fetched_data['severity_counts'] = $data['severity_counts'];
            $fetched_data['avg_logs_per_day'] = $data['avg_logs_per_day'];
            $fetched_data['log_trend'] = $data['log_trend'];
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}