<?php
/**********************
* Home Assistant Data Collection
* ----------------------
* This function collects data from Home Assistant, a home automation platform, for dashboard display.
* It fetches information about the devices, entities, and overall system status.
*
* Collected Data:
* - Total number of devices
* - Number of devices by category (light, switch, sensor, etc.)
* - Number of entities by domain (light, switch, sensor, etc.)
* - Average state changes per entity
* - System health and runtime information
*
* Data not collected but available for extension:
* - Detailed device information (name, model, manufacturer)
* - Entity details (state, attributes, last changed)
* - Automation and script configurations
* - Scene and group definitions
* - Notification and alert settings
* - User and permission management
*
* Opportunities for additional data collection:
* - Device and entity usage patterns
* - Automation and script execution history
* - Energy consumption and efficiency metrics
* - Sensor data trends and anomaly detection
* - Integration and third-party service usage
*
* Requirements:
* - Home Assistant API should be accessible via the provided API URL.
* - API authentication requires an API key.
*
* Parameters:
* - $api_url: The base URL of the Home Assistant API.
* - $api_key: The API key for authentication.
* - $service_id: The ID of the Home Assistant service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
***********************/
function homelab_fetch_homeassistant_data($api_url, $api_key, $service_id) {
    $api_url = rtrim($api_url, '/');

    $endpoints = array(
        'devices' => '/api/states',
        'entities' => '/api/states',
        'system' => '/api/config',
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

        if ($key === 'devices' || $key === 'entities') {
            $device_counts = array();
            $entity_counts = array();
            $total_state_changes = 0;

            foreach ($data as $entity) {
                $device_class = $entity['attributes']['device_class'] ?? 'unknown';
                if (isset($device_counts[$device_class])) {
                    $device_counts[$device_class]++;
                } else {
                    $device_counts[$device_class] = 1;
                }

                $domain = explode('.', $entity['entity_id'])[0];
                if (isset($entity_counts[$domain])) {
                    $entity_counts[$domain]++;
                } else {
                    $entity_counts[$domain] = 1;
                }

                $total_state_changes += $entity['attributes']['state_changes'] ?? 0;
            }

            $fetched_data['total_devices'] = count($data);
            $fetched_data['devices_by_category'] = $device_counts;
            $fetched_data['entities_by_domain'] = $entity_counts;
            $fetched_data['avg_state_changes'] = $fetched_data['total_devices'] > 0 ? $total_state_changes / $fetched_data['total_devices'] : 0;
        } elseif ($key === 'system') {
            $fetched_data['system_health'] = $data['state'];
            $fetched_data['system_runtime'] = $data['last_boot'];
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}