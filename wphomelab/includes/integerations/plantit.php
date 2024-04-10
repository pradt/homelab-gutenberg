<?php
/******************
* Plant-It Data Collection
* ----------------------
* This function collects data from Plant-It, a plant monitoring and management system, for dashboard display.
* It fetches information about the monitored plants, including their status, sensor readings, and growth progress.
*
* Collected Data:
* - Total number of monitored plants
* - Number of plants by status (healthy, warning, critical, unknown)
* - Average soil moisture percentage across all plants
* - Average temperature reading across all plants
* - Average growth progress percentage across all plants
*
* Data not collected but available for extension:
* - Detailed plant information (name, species, location)
* - Plant-specific sensor readings (light, humidity, nutrient levels)
* - Watering and fertilization schedules
* - Plant images and time-lapse videos
* - Historical sensor data and growth trends
* - Plant-It configuration and settings
*
* Opportunities for additional data:
* - Plant health alerts and notifications
* - Recommended actions based on plant status and conditions
* - Integration with weather data for outdoor plants
* - Comparison and benchmarking of plant performance
*
* Requirements:
* - Plant-It API should be accessible via the provided API URL.
* - API authentication key (API key) is required for accessing the Plant-It API.
*
* Parameters:
* - $api_url: The base URL of the Plant-It API.
* - $api_key: The API key for authentication.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*
* Example fetched_data structure:
* {
*   "total_plants": 10,
*   "plants_healthy": 7,
*   "plants_warning": 2,
*   "plants_critical": 1,
*   "plants_unknown": 0,
*   "avg_soil_moisture": 68.5,
*   "avg_temperature": 25.3,
*   "avg_growth_progress": 75.8
* }
*******************/
function homelab_fetch_plantit_data($api_url, $api_key, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'plants' => '/api/v1/plants',
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
        if ($key === 'plants') {
            $fetched_data['total_plants'] = count($data);
            $status_counts = array(
                'healthy' => 0,
                'warning' => 0,
                'critical' => 0,
                'unknown' => 0,
            );
            $total_soil_moisture = 0;
            $total_temperature = 0;
            $total_growth_progress = 0;

            foreach ($data as $plant) {
                $status = strtolower($plant['status']);
                if (isset($status_counts[$status])) {
                    $status_counts[$status]++;
                } else {
                    $status_counts['unknown']++;
                }
                $total_soil_moisture += $plant['soil_moisture'];
                $total_temperature += $plant['temperature'];
                $total_growth_progress += $plant['growth_progress'];
            }

            $fetched_data['plants_healthy'] = $status_counts['healthy'];
            $fetched_data['plants_warning'] = $status_counts['warning'];
            $fetched_data['plants_critical'] = $status_counts['critical'];
            $fetched_data['plants_unknown'] = $status_counts['unknown'];
            $fetched_data['avg_soil_moisture'] = $fetched_data['total_plants'] > 0 ? $total_soil_moisture / $fetched_data['total_plants'] : 0;
            $fetched_data['avg_temperature'] = $fetched_data['total_plants'] > 0 ? $total_temperature / $fetched_data['total_plants'] : 0;
            $fetched_data['avg_growth_progress'] = $fetched_data['total_plants'] > 0 ? $total_growth_progress / $fetched_data['total_plants'] : 0;
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}