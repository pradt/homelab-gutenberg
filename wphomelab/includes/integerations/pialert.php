<?php
/******************
* PiAlert Data Collection
* ----------------------
* This function collects data from PiAlert, a Raspberry Pi monitoring tool, for dashboard display.
* It fetches information about the monitored Raspberry Pi devices, including their status, system metrics, and alert counts.
*
* Collected Data:
* - Total number of monitored Raspberry Pi devices
* - Number of devices by status (online, offline)
* - Average CPU usage across all devices
* - Average memory usage across all devices
* - Total number of alerts
*
* Data Structure Example (fetched_data):
* {
*   "total_devices": 5,
*   "devices_online": 4,
*   "devices_offline": 1,
*   "avg_cpu_usage": 45.6,
*   "avg_memory_usage": 60.2,
*   "total_alerts": 10
* }
*
* Data not collected but available for extension:
* - Detailed device information (name, IP address, model, OS version)
* - Device-specific system metrics (CPU temperature, disk usage, network traffic)
* - Alert details (severity, message, timestamp)
* - Historical performance data and trends
* - PiAlert configuration and settings
*
* Requirements:
* - PiAlert API should be accessible via the provided API URL.
* - API authentication token (API key) may be required depending on PiAlert configuration.
*
* Parameters:
* - $api_url: The base URL of the PiAlert API.
* - $api_key: The API key for authentication (if required).
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_pialert_data($api_url, $api_key = '', $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'devices' => '/api/v1/devices',
        'alerts' => '/api/v1/alerts',
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

        if ($key === 'devices') {
            $fetched_data['total_devices'] = count($data);
            $status_counts = array(
                'online' => 0,
                'offline' => 0,
            );
            $total_cpu_usage = 0;
            $total_memory_usage = 0;

            foreach ($data as $device) {
                $status = strtolower($device['status']);
                if (isset($status_counts[$status])) {
                    $status_counts[$status]++;
                }
                $total_cpu_usage += $device['cpu_usage'];
                $total_memory_usage += $device['memory_usage'];
            }

            $fetched_data['devices_online'] = $status_counts['online'];
            $fetched_data['devices_offline'] = $status_counts['offline'];
            $fetched_data['avg_cpu_usage'] = $fetched_data['total_devices'] > 0 ? $total_cpu_usage / $fetched_data['total_devices'] : 0;
            $fetched_data['avg_memory_usage'] = $fetched_data['total_devices'] > 0 ? $total_memory_usage / $fetched_data['total_devices'] : 0;
        } elseif ($key === 'alerts') {
            $fetched_data['total_alerts'] = count($data);
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}