<?php
/******************
* OpenDTU Data Collection
* ----------------------
* This function collects data from OpenDTU, an open-source monitoring system for solar inverters, for dashboard display.
* It fetches information about the monitored inverters, including their status, energy production, and performance metrics.
*
* Collected Data:
* - Total number of monitored inverters
* - Number of inverters by status (online, offline, error)
* - Total energy production (daily, monthly, yearly)
* - Average efficiency across all inverters
* - Average power output across all inverters
*
* Data not collected but available for extension:
* - Detailed inverter information (name, serial number, model, location)
* - Inverter-specific energy production and performance metrics
* - Inverter health check details (last communication time, error messages)
* - Alert and notification settings
* - Historical energy production and performance data
* - OpenDTU configuration and settings
*
* Example of fetched_data structure:
* {
*   "total_inverters": 5,
*   "inverters_online": 4,
*   "inverters_offline": 1,
*   "inverters_error": 0,
*   "total_energy_daily": 150.5,
*   "total_energy_monthly": 4500.0,
*   "total_energy_yearly": 54000.0,
*   "avg_efficiency": 95.8,
*   "avg_power_output": 2500.0
* }
*
* Requirements:
* - OpenDTU API should be accessible via the provided API URL.
* - API authentication token (API key) may be required depending on OpenDTU configuration.
*
* Parameters:
* - $api_url: The base URL of the OpenDTU API.
* - $api_key: The API key for authentication (if required).
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_opendtu_data($api_url, $api_key = '', $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'inverters' => '/api/v1/inverters',
        'energy' => '/api/v1/energy',
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
        if ($key === 'inverters') {
            $fetched_data['total_inverters'] = count($data);
            $status_counts = array(
                'online' => 0,
                'offline' => 0,
                'error' => 0,
            );
            $total_efficiency = 0;
            $total_power_output = 0;
            foreach ($data as $inverter) {
                $status = strtolower($inverter['status']);
                if (isset($status_counts[$status])) {
                    $status_counts[$status]++;
                }
                $total_efficiency += $inverter['efficiency'];
                $total_power_output += $inverter['power_output'];
            }
            $fetched_data['inverters_online'] = $status_counts['online'];
            $fetched_data['inverters_offline'] = $status_counts['offline'];
            $fetched_data['inverters_error'] = $status_counts['error'];
            $fetched_data['avg_efficiency'] = $fetched_data['total_inverters'] > 0 ? $total_efficiency / $fetched_data['total_inverters'] : 0;
            $fetched_data['avg_power_output'] = $fetched_data['total_inverters'] > 0 ? $total_power_output / $fetched_data['total_inverters'] : 0;
        } elseif ($key === 'energy') {
            $fetched_data['total_energy_daily'] = $data['daily'];
            $fetched_data['total_energy_monthly'] = $data['monthly'];
            $fetched_data['total_energy_yearly'] = $data['yearly'];
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}