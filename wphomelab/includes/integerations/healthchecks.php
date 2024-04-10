<?php
/**********************
* Health Checks Data Collection
* ----------------------
* This function collects data from Health Checks, a cron job monitoring service, for dashboard display.
* It fetches information about the checks, their status, and overall usage statistics.
*
* Collected Data:
* - Total number of checks
* - Number of checks by status (up, down, paused)
* - Number of checks by schedule (daily, weekly, monthly)
* - Average ping frequency per check
* - Total number of pings received
*
* Data not collected but available for extension:
* - Detailed check information (name, description, tags, last ping)
* - Check configuration (schedule, grace period, timeout)
* - Ping history and response times
* - Alert channels and notification settings
* - Team and project management data
* - API usage and limits
*
* Opportunities for additional data collection:
* - Check uptime and reliability metrics
* - Ping latency and network performance
* - Alert frequency and resolution times
* - Integration and webhook usage
* - Account activity and user engagement
*
* Requirements:
* - Health Checks API should be accessible via the provided API URL.
* - API authentication requires an API key.
* - Specific check data requires the check's UUID.
*
* Parameters:
* - $api_url: The base URL of the Health Checks API.
* - $api_key: The API key for authentication.
* - $check_uuid: The UUID of the specific check (optional).
* - $service_id: The ID of the Health Checks service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
***********************/
function homelab_fetch_healthchecks_data($api_url, $api_key, $check_uuid = '', $service_id) {
    $api_url = rtrim($api_url, '/');

    $endpoints = array(
        'checks' => '/api/v1/checks/',
        'pings' => '/api/v1/pings/',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;

        if (!empty($check_uuid)) {
            $url .= $check_uuid . '/';
        }

        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-Api-Key' => $api_key,
            ),
        );

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'checks') {
            $fetched_data['total_checks'] = count($data['checks']);

            $status_counts = array(
                'up' => 0,
                'down' => 0,
                'paused' => 0,
            );

            $schedule_counts = array(
                'daily' => 0,
                'weekly' => 0,
                'monthly' => 0,
            );

            $total_ping_frequency = 0;

            foreach ($data['checks'] as $check) {
                $status = strtolower($check['status']);
                if (isset($status_counts[$status])) {
                    $status_counts[$status]++;
                }

                $schedule = strtolower($check['schedule']);
                if (isset($schedule_counts[$schedule])) {
                    $schedule_counts[$schedule]++;
                }

                $total_ping_frequency += $check['ping_frequency'];
            }

            $fetched_data['checks_up'] = $status_counts['up'];
            $fetched_data['checks_down'] = $status_counts['down'];
            $fetched_data['checks_paused'] = $status_counts['paused'];

            $fetched_data['checks_daily'] = $schedule_counts['daily'];
            $fetched_data['checks_weekly'] = $schedule_counts['weekly'];
            $fetched_data['checks_monthly'] = $schedule_counts['monthly'];

            $fetched_data['avg_ping_frequency'] = $fetched_data['total_checks'] > 0 ? $total_ping_frequency / $fetched_data['total_checks'] : 0;
        } elseif ($key === 'pings') {
            $fetched_data['total_pings'] = count($data['pings']);
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}