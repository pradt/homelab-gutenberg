<?php
/******************
* EVCC Data Collection
* ----------------------
* This function collects data from EVCC, an electric vehicle charge controller, for dashboard display.
* It fetches information about the charging sessions, including the current charging status, energy consumption, and charging progress.
*
* Collected Data:
* - Current charging status (charging, discharging, idle)
* - Current power (in watts)
* - Total energy consumed in the current session (in watt-hours)
* - Charging progress percentage
* - Estimated time to complete charging
*
* Data not collected but available for extension:
* - Detailed charging session information (start time, end time, duration)
* - Historical charging session data
* - Vehicle information (make, model, battery capacity)
* - Charger information (charger type, maximum power output)
* - Energy source information (grid, solar, battery)
* - EVCC configuration and settings
*
* Example of fetched_data structure:
* {
*   "status": "charging",
*   "current_power": 7200,
*   "energy_consumed": 22500,
*   "progress_percentage": 80,
*   "estimated_time_to_complete": "00:30:00"
* }
*
* Requirements:
* - EVCC API should be accessible via the provided API URL.
* - API authentication token (API key) may be required depending on EVCC configuration.
*
* Parameters:
* - $api_url: The base URL of the EVCC API.
* - $api_key: The API key for authentication (if required).
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_evcc_data($api_url, $api_key = '', $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'status' => '/api/v1/status',
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

        if ($key === 'status') {
            $fetched_data['status'] = $data['status'];
            $fetched_data['current_power'] = $data['current_power'];
            $fetched_data['energy_consumed'] = $data['energy_consumed'];
            $fetched_data['progress_percentage'] = $data['progress_percentage'];
            $fetched_data['estimated_time_to_complete'] = $data['estimated_time_to_complete'];
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}