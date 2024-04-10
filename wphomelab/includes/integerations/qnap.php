<?
/******************
* QNAP Data Collection
* ----------------------
* This function collects data from QNAP, a network-attached storage (NAS) device, for dashboard display.
* It fetches information about the storage, resource usage, and health status of the QNAP device.
*
* Collected Data:
* - Total storage capacity
* - Used storage space
* - Free storage space
* - CPU usage percentage
* - Memory usage percentage
* - System health status
*
* Data not collected but available for extension:
* - Detailed volume information (name, size, file system, mount point)
* - Network interface statistics (IP address, bandwidth usage)
* - Disk health and SMART information
* - Active user sessions and connections
* - Running services and their status
* - System logs and event history
*
* Requirements:
* - QNAP device should be accessible via the provided API URL.
* - API authentication requires a username and password.
*
* Parameters:
* - $api_url: The base URL of the QNAP API.
* - $username: The username for authentication.
* - $password: The password for authentication.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*
* Example of fetched_data structure:
* {
*   "total_storage": 4000,
*   "used_storage": 2500,
*   "free_storage": 1500,
*   "cpu_usage": 35,
*   "memory_usage": 60,
*   "system_health": "normal"
* }
*******************/
function homelab_fetch_qnap_data($api_url, $username, $password, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'system_stats' => '/cgi-bin/management/manaRequest.cgi',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $args = array(
            'method' => 'POST',
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded',
            ),
            'body' => array(
                'subfunc' => 'system_stats',
                'user' => $username,
                'pwd' => $password,
            ),
        );

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'system_stats') {
            $fetched_data['total_storage'] = $data['system']['total_storage'];
            $fetched_data['used_storage'] = $data['system']['used_storage'];
            $fetched_data['free_storage'] = $data['system']['free_storage'];
            $fetched_data['cpu_usage'] = $data['system']['cpu_usage'];
            $fetched_data['memory_usage'] = $data['system']['memory_usage'];
            $fetched_data['system_health'] = $data['system']['health_status'];
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}