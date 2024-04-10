<?php
/**********************
* Homebox Data Collection
* ----------------------
* This function collects data from Homebox, a home server management platform, for dashboard display.
* It fetches information about the server, services, and overall system status.
*
* Collected Data:
* - Server hostname and operating system
* - Total number of services
* - Number of services by status (running, stopped, error)
* - Average uptime per service
* - System resource utilization (CPU, memory, disk)
*
* Data not collected but available for extension:
* - Detailed service information (name, description, configuration)
* - Service logs and error messages
* - Network and port configuration
* - User and permission management
* - Backup and restore settings
* - Plugin and extension data
*
* Opportunities for additional data collection:
* - Service performance and response times
* - Resource usage trends and anomaly detection
* - Network traffic and bandwidth analysis
* - Security and access control metrics
* - User activity and audit logging
*
* Requirements:
* - Homebox API should be accessible via the provided API URL.
* - API authentication requires a username and password.
*
* Parameters:
* - $api_url: The base URL of the Homebox API.
* - $username: The username for authentication.
* - $password: The password for authentication.
* - $service_id: The ID of the Homebox service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
***********************/
function homelab_fetch_homebox_data($api_url, $username, $password, $service_id) {
    $api_url = rtrim($api_url, '/');

    $endpoints = array(
        'server' => '/api/server',
        'services' => '/api/services',
        'system' => '/api/system',
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
            'body' => json_encode(array(
                'username' => $username,
                'password' => $password,
            )),
        );

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'server') {
            $fetched_data['hostname'] = $data['hostname'];
            $fetched_data['operating_system'] = $data['os'];
        } elseif ($key === 'services') {
            $fetched_data['total_services'] = count($data);

            $status_counts = array(
                'running' => 0,
                'stopped' => 0,
                'error' => 0,
            );

            $total_uptime = 0;

            foreach ($data as $service) {
                $status = strtolower($service['status']);
                if (isset($status_counts[$status])) {
                    $status_counts[$status]++;
                }

                $total_uptime += $service['uptime'];
            }

            $fetched_data['services_running'] = $status_counts['running'];
            $fetched_data['services_stopped'] = $status_counts['stopped'];
            $fetched_data['services_error'] = $status_counts['error'];
            $fetched_data['avg_uptime'] = $fetched_data['total_services'] > 0 ? $total_uptime / $fetched_data['total_services'] : 0;
        } elseif ($key === 'system') {
            $fetched_data['cpu_usage'] = $data['cpu_usage'];
            $fetched_data['memory_usage'] = $data['memory_usage'];
            $fetched_data['disk_usage'] = $data['disk_usage'];
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}