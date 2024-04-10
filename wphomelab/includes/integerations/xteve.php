<?php
/******************
* Steve Data Collection
* ----------------------
* This function collects data from Steve, a network monitoring and management tool, for dashboard display.
* It fetches information about the monitored devices, including their status, CPU usage, memory usage, and network traffic.
*
* Collected Data:
* - Total number of monitored devices
* - Number of devices by status (up, down, unknown)
* - Average CPU usage across all devices
* - Average memory usage across all devices
* - Total network traffic (in and out) across all devices
*
* Data not collected but available for extension:
* - Detailed device information (name, IP address, operating system)
* - Device-specific CPU, memory, and network usage
* - Device health check details (last check time, error messages)
* - Alert and notification settings
* - Historical performance and usage data
* - Steve configuration and settings
*
* Additional opportunities for data collection:
* - Device inventory and asset management information
* - Network topology and connectivity details
* - Security and vulnerability assessment data
* - Application performance monitoring data
* - Log and event management data
*
* Requirements:
* - Steve API should be accessible via the provided API URL.
* - API authentication using username and password may be required depending on Steve configuration.
*
* Parameters:
* - $api_url: The base URL of the Steve API.
* - $username: The username for authentication (if required).
* - $password: The password for authentication (if required).
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*
* Example fetched_data JSON structure:
* {
*   "total_devices": 50,
*   "devices_up": 45,
*   "devices_down": 3,
*   "devices_unknown": 2,
*   "avg_cpu_usage": 60.5,
*   "avg_memory_usage": 70.2,
*   "total_network_traffic_in": 1000.0,
*   "total_network_traffic_out": 800.0
* }
*******************/
function homelab_fetch_steve_data($api_url, $username = '', $password = '', $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'devices' => '/api/v1/devices',
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

        if (!empty($username) && !empty($password)) {
            $args['headers']['Authorization'] = 'Basic ' . base64_encode($username . ':' . $password);
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
                'up' => 0,
                'down' => 0,
                'unknown' => 0,
            );
            $total_cpu_usage = 0;
            $total_memory_usage = 0;
            $total_network_traffic_in = 0;
            $total_network_traffic_out = 0;

            foreach ($data as $device) {
                $status = strtolower($device['status']);
                if (isset($status_counts[$status])) {
                    $status_counts[$status]++;
                } else {
                    $status_counts['unknown']++;
                }
                $total_cpu_usage += $device['cpu_usage'];
                $total_memory_usage += $device['memory_usage'];
                $total_network_traffic_in += $device['network_traffic_in'];
                $total_network_traffic_out += $device['network_traffic_out'];
            }

            $fetched_data['devices_up'] = $status_counts['up'];
            $fetched_data['devices_down'] = $status_counts['down'];
            $fetched_data['devices_unknown'] = $status_counts['unknown'];
            $fetched_data['avg_cpu_usage'] = $fetched_data['total_devices'] > 0 ? $total_cpu_usage / $fetched_data['total_devices'] : 0;
            $fetched_data['avg_memory_usage'] = $fetched_data['total_devices'] > 0 ? $total_memory_usage / $fetched_data['total_devices'] : 0;
            $fetched_data['total_network_traffic_in'] = $total_network_traffic_in;
            $fetched_data['total_network_traffic_out'] = $total_network_traffic_out;
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}