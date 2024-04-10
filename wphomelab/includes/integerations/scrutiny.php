<?php
/******************
* Scrutiny Data Collection
* ----------------------
* This function collects data from Scrutiny, a hard drive monitoring tool, for dashboard display.
* It fetches information about the monitored hard drives, including their health status, temperature, and performance metrics.
*
* Collected Data:
* - Total number of monitored hard drives
* - Number of hard drives by health status (healthy, warning, critical, unknown)
* - Average temperature across all hard drives
* - Average read/write speed across all hard drives
*
* Data not collected but available for extension:
* - Detailed hard drive information (model, serial number, capacity, firmware version)
* - Hard drive-specific temperature and performance metrics
* - SMART attributes and their values
* - Historical temperature and performance data
* - Scrutiny configuration and settings
*
* Example of fetched_data structure:
* {
*   "total_drives": 5,
*   "drives_healthy": 4,
*   "drives_warning": 1,
*   "drives_critical": 0,
*   "drives_unknown": 0,
*   "avg_temperature": 38.5,
*   "avg_read_speed": 150.2,
*   "avg_write_speed": 120.8
* }
*
* Requirements:
* - Scrutiny API should be accessible via the provided API URL.
* - API authentication token (API key) may be required depending on Scrutiny configuration.
*
* Parameters:
* - $api_url: The base URL of the Scrutiny API.
* - $api_key: The API key for authentication (if required).
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_scrutiny_data($api_url, $api_key = '', $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'drives' => '/api/v1/drives',
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

        if ($key === 'drives') {
            $fetched_data['total_drives'] = count($data);
            $status_counts = array(
                'healthy' => 0,
                'warning' => 0,
                'critical' => 0,
                'unknown' => 0,
            );
            $total_temperature = 0;
            $total_read_speed = 0;
            $total_write_speed = 0;

            foreach ($data as $drive) {
                $status = strtolower($drive['health_status']);
                if (isset($status_counts[$status])) {
                    $status_counts[$status]++;
                } else {
                    $status_counts['unknown']++;
                }
                $total_temperature += $drive['temperature'];
                $total_read_speed += $drive['read_speed'];
                $total_write_speed += $drive['write_speed'];
            }

            $fetched_data['drives_healthy'] = $status_counts['healthy'];
            $fetched_data['drives_warning'] = $status_counts['warning'];
            $fetched_data['drives_critical'] = $status_counts['critical'];
            $fetched_data['drives_unknown'] = $status_counts['unknown'];
            $fetched_data['avg_temperature'] = $fetched_data['total_drives'] > 0 ? $total_temperature / $fetched_data['total_drives'] : 0;
            $fetched_data['avg_read_speed'] = $fetched_data['total_drives'] > 0 ? $total_read_speed / $fetched_data['total_drives'] : 0;
            $fetched_data['avg_write_speed'] = $fetched_data['total_drives'] > 0 ? $total_write_speed / $fetched_data['total_drives'] : 0;
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}