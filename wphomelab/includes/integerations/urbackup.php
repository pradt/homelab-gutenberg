<?php
/******************
* UptimeRobot Data Collection
* ---------------------------
* This function collects data from UptimeRobot, a website monitoring service, for dashboard display.
* It fetches information about the monitored websites, including their status, response times, and uptime percentages.
*
* Collected Data:
* - Total number of monitored websites
* - Number of websites by status (up, down, paused)
* - Average response time across all websites
* - Average uptime percentage across all websites
*
* Data not collected but available for extension:
* - Detailed website information (name, URL, monitoring interval)
* - Website-specific response times and uptime percentages
* - Website health check details (last check time, error messages)
* - Alert and notification settings
* - Historical uptime and response time data
* - UptimeRobot account and monitor configuration
*
* Additional data opportunities:
* - SSL certificate expiration dates
* - Website performance metrics (e.g., load time, page size)
* - Geographical distribution of monitored websites
* - Downtime and uptime durations for each website
*
* Requirements:
* - UptimeRobot API should be accessible via the provided API URL.
* - API key is required for authentication.
*
* Parameters:
* - $api_url: The base URL of the UptimeRobot API.
* - $api_key: The API key for authentication.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*
* Example fetched_data structure:
* {
*   "total_monitors": 10,
*   "monitors_up": 8,
*   "monitors_down": 1,
*   "monitors_paused": 1,
*   "avg_response_time": 500,
*   "avg_uptime_percentage": 99.5
* }
*******************/
/* function homelab_fetch_uptimerobot_data($api_url, $api_key, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'monitors' => '/v2/getMonitors',
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
            'body' => json_encode(array(
                'api_key' => $api_key,
                'format' => 'json',
            )),
        );

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'monitors') {
            $fetched_data['total_monitors'] = count($data['monitors']);

            $status_counts = array(
                'up' => 0,
                'down' => 0,
                'paused' => 0,
            );
            $total_response_time = 0;
            $total_uptime_percentage = 0;

            foreach ($data['monitors'] as $monitor) {
                $status = strtolower($monitor['status']);
                if (isset($status_counts[$status])) {
                    $status_counts[$status]++;
                }
                $total_response_time += $monitor['average_response_time'];
                $total_uptime_percentage += $monitor['custom_uptime_ratio'];
            }

            $fetched_data['monitors_up'] = $status_counts['up'];
            $fetched_data['monitors_down'] = $status_counts['down'];
            $fetched_data['monitors_paused'] = $status_counts['paused'];
            $fetched_data['avg_response_time'] = $fetched_data['total_monitors'] > 0 ? $total_response_time / $fetched_data['total_monitors'] : 0;
            $fetched_data['avg_uptime_percentage'] = $fetched_data['total_monitors'] > 0 ? $total_uptime_percentage / $fetched_data['total_monitors'] : 0;
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
} */