<?php
/******************
 * Gluten Data Collection
 * ----------------------
 * This function collects data from Gluten, a service monitoring tool, for dashboard display.
 * It fetches information about the monitored services, including their status, response times, and uptime percentages.
 *
 * Collected Data:
 * - Total number of monitored services
 * - Number of services by status (up, down, degraded, unknown)
 * - Average response time across all services
 * - Average uptime percentage across all services
 *
 * Data not collected but available for extension:
 * - Detailed service information (name, URL, port, protocol)
 * - Service-specific response times and uptime percentages
 * - Service health check details (last check time, error messages)
 * - Alert and notification settings
 * - Historical uptime and response time data
 * - Gluten configuration and settings
 *
 * Requirements:
 * - Gluten API should be accessible via the provided API URL.
 * - API authentication token (API key) may be required depending on Gluten configuration.
 *
 * Parameters:
 * - $api_url: The base URL of the Gluten API.
 * - $api_key: The API key for authentication (if required).
 * - $service_id: The ID of the service being monitored.
 *
 * Error Handling:
 * - Captures any errors encountered during the API request process.
 * - Stores error messages and timestamps for troubleshooting.
 *******************/

 function homelab_fetch_gluten_data($api_url, $api_key = '', $service_id) {
    $api_url = rtrim($api_url, '/');

    $endpoints = array(
        'services' => '/api/v1/services',
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

        if ($key === 'services') {
            $fetched_data['total_services'] = count($data);

            $status_counts = array(
                'up' => 0,
                'down' => 0,
                'degraded' => 0,
                'unknown' => 0,
            );

            $total_response_time = 0;
            $total_uptime_percentage = 0;

            foreach ($data as $service) {
                $status = strtolower($service['status']);
                if (isset($status_counts[$status])) {
                    $status_counts[$status]++;
                } else {
                    $status_counts['unknown']++;
                }

                $total_response_time += $service['response_time'];
                $total_uptime_percentage += $service['uptime_percentage'];
            }

            $fetched_data['services_up'] = $status_counts['up'];
            $fetched_data['services_down'] = $status_counts['down'];
            $fetched_data['services_degraded'] = $status_counts['degraded'];
            $fetched_data['services_unknown'] = $status_counts['unknown'];
            $fetched_data['avg_response_time'] = $fetched_data['total_services'] > 0 ? $total_response_time / $fetched_data['total_services'] : 0;
            $fetched_data['avg_uptime_percentage'] = $fetched_data['total_services'] > 0 ? $total_uptime_percentage / $fetched_data['total_services'] : 0;
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}