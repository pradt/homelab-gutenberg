<?php
/******************
 * ESPHome Data Collection
 * --------------------
 * This function collects data from the ESPHome API for dashboard display, without requiring authentication.
 * It fetches the count of devices by state and other relevant information.
 *
 * Collected Data:
 * - Total number of devices
 * - Number of online devices
 * - Number of offline devices
 * - Number of devices with unknown state
 * - ESPHome server version
 * - Number of enabled devices
 * - Number of disabled devices
 *
 * Data not collected but available for extension:
 * - Detailed device information (name, MAC address, IP address, etc.)
 * - Device configuration and settings
 * - Sensor data and device state
 * - Automation and script execution status
 * - System resource usage and performance data
 *
 * Authentication:
 * This function does not require authentication to access ESPHome API information.
 *
 * Parameters:
 * - $api_url: The base URL of the ESPHome API.
 * - $service_id: The ID of the service being monitored.
 *
 * Error Handling:
 * - Captures any errors encountered during the API request process.
 * - Stores error messages and timestamps for troubleshooting.
 *******************/

 function homelab_fetch_esphome_data($api_url, $service_id) {
    $api_url = rtrim($api_url, '/');

    $endpoints = array(
        'devices' => '/devices',
        'info' => '/info',
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

        if ($key === 'devices') {
            $total_devices = count($data);
            $online_devices = 0;
            $offline_devices = 0;
            $unknown_devices = 0;
            $enabled_devices = 0;
            $disabled_devices = 0;

            foreach ($data as $device) {
                switch ($device['state']) {
                    case 'online':
                        $online_devices++;
                        break;
                    case 'offline':
                        $offline_devices++;
                        break;
                    default:
                        $unknown_devices++;
                        break;
                }

                if ($device['enabled']) {
                    $enabled_devices++;
                } else {
                    $disabled_devices++;
                }
            }

            $fetched_data['total_devices_count'] = $total_devices;
            $fetched_data['online_devices_count'] = $online_devices;
            $fetched_data['offline_devices_count'] = $offline_devices;
            $fetched_data['unknown_devices_count'] = $unknown_devices;
            $fetched_data['enabled_devices_count'] = $enabled_devices;
            $fetched_data['disabled_devices_count'] = $disabled_devices;
        } elseif ($key === 'info') {
            $fetched_data['esphome_version'] = $data['version'];
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}