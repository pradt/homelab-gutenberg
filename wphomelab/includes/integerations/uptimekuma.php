<?php
/******************
* Uptime Kuma Data Collection
* ----------------------
* This function collects data from Uptime Kuma, a service monitoring tool, for dashboard display.
* It fetches information about the monitored services, including their status, response times, and uptime percentages.
*
* Collected Data:
* - Total number of monitored services
* - Number of services by status (up, down)
* - Average uptime percentage across all services
* - Current incident status (if any)
*
* Data not collected but available for extension:
* - Detailed service information (name, URL, port, protocol)
* - Service-specific response times and uptime percentages
* - Service health check details (last check time, error messages)
* - Alert and notification settings
* - Historical uptime and response time data
* - Uptime Kuma configuration and settings
*
* Requirements:
* - Uptime Kuma status page should be accessible via the provided URL.
* - Status page slug is required to identify the specific status page.
*
* Parameters:
* - $url: The base URL of the Uptime Kuma instance.
* - $slug: The slug of the status page.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the status page request process.
* - Stores error messages and timestamps for troubleshooting.
*
* Example of fetched_data structure:
* {
*   "total_services": 10,
*   "services_up": 8,
*   "services_down": 2,
*   "avg_uptime_percentage": 95.5,
*   "incident_status": false
* }
*******************/
function homelab_fetch_uptimekuma_data($url, $slug, $service_id) {
    $status_url = rtrim($url, '/') . '/status/' . $slug;
    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    $response = wp_remote_get($status_url);
    if (is_wp_error($response)) {
        $error_message = "Status page request failed: " . $response->get_error_message();
        $error_timestamp = current_time('mysql');
        homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
        return $fetched_data;
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);
    if (isset($data['monitors'])) {
        $fetched_data['total_services'] = count($data['monitors']);
        $status_counts = array(
            'up' => 0,
            'down' => 0,
        );
        $total_uptime_percentage = 0;

        foreach ($data['monitors'] as $monitor) {
            $status = strtolower($monitor['status']);
            if (isset($status_counts[$status])) {
                $status_counts[$status]++;
            }
            $total_uptime_percentage += $monitor['uptime'];
        }

        $fetched_data['services_up'] = $status_counts['up'];
        $fetched_data['services_down'] = $status_counts['down'];
        $fetched_data['avg_uptime_percentage'] = $fetched_data['total_services'] > 0 ? $total_uptime_percentage / $fetched_data['total_services'] : 0;
    }

    $fetched_data['incident_status'] = isset($data['incident']) && $data['incident'] !== null;

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}