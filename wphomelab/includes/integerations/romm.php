<?php
/******************
* ROMM Data Collection
* ----------------------
* This function collects data from ROMM, a hardware monitoring and management tool, for dashboard display.
* It fetches information about the monitored devices, including their status, temperature, CPU usage, and memory usage.
*
* Collected Data:
* - Total number of monitored devices
* - Number of devices by status (online, offline)
* - Average CPU usage across all devices
* - Average memory usage across all devices
* - Average temperature across all devices
*
* Data not collected but available for extension:
* - Detailed device information (name, IP address, operating system)
* - Device-specific CPU, memory, and temperature data
* - Network traffic and bandwidth usage
* - Storage capacity and usage
* - Installed software and updates
* - User and access management
* - Alert and notification settings
* - Historical performance and usage data
*
* Opportunities for additional data collection:
* - Device location and geolocation data
* - Power consumption and energy efficiency metrics
* - Integration with asset management systems
* - Correlation with application performance data
* - Predictive maintenance and failure analysis
*
* Requirements:
* - ROMM API should be accessible via the provided API URL.
* - API authentication with username and password may be required depending on ROMM configuration.
*
* Parameters:
* - $api_url: The base URL of the ROMM API.
* - $username: The username for authentication (if required).
* - $password: The password for authentication (if required).
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*
* Example of fetched_data structure:
* {
*   "total_devices": 50,
*   "devices_online": 45,
*   "devices_offline": 5,
*   "avg_cpu_usage": 60.5,
*   "avg_memory_usage": 70.2,
*   "avg_temperature": 45.8
* }
*******************/
function homelab_fetch_romm_data($api_url, $username = '', $password = '', $service_id) {
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
                'online' => 0,
                'offline' => 0,
            );
            $total_cpu_usage = 0;
            $total_memory_usage = 0;
            $total_temperature = 0;

            foreach ($data as $device) {
                $status = strtolower($device['status']);
                if (isset($status_counts[$status])) {
                    $status_counts[$status]++;
                }
                $total_cpu_usage += $device['cpu_usage'];
                $total_memory_usage += $device['memory_usage'];
                $total_temperature += $device['temperature'];
            }

            $fetched_data['devices_online'] = $status_counts['online'];
            $fetched_data['devices_offline'] = $status_counts['offline'];
            $fetched_data['avg_cpu_usage'] = $fetched_data['total_devices'] > 0 ? $total_cpu_usage / $fetched_data['total_devices'] : 0;
            $fetched_data['avg_memory_usage'] = $fetched_data['total_devices'] > 0 ? $total_memory_usage / $fetched_data['total_devices'] : 0;
            $fetched_data['avg_temperature'] = $fetched_data['total_devices'] > 0 ? $total_temperature / $fetched_data['total_devices'] : 0;
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}