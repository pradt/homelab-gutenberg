<?php

/******************
* What's Up Docker Data Collection
* --------------------------------
* This function collects data from What's Up Docker, a Docker container monitoring tool, for dashboard display.
* It fetches information about the running containers, including their status, resource usage, and network statistics.
*
* Collected Data:
* - Total number of running containers
* - Number of containers by status (running, exited, paused)
* - Average CPU usage across all containers
* - Average memory usage across all containers
* - Total network input and output traffic
*
* Data not collected but available for extension:
* - Detailed container information (name, image, command, ports)
* - Container-specific resource usage (CPU, memory, disk)
* - Container logs and event streams
* - Docker host system information (CPU, memory, disk, network)
* - Docker Swarm cluster status and node information
* - Docker volume and network details
*
* Requirements:
* - What's Up Docker API should be accessible via the provided API URL.
* - API authentication (username and password) may be required depending on the configuration.
*
* Parameters:
* - $api_url: The base URL of the What's Up Docker API.
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
*   "total_containers": 10,
*   "containers_running": 8,
*   "containers_exited": 1,
*   "containers_paused": 1,
*   "avg_cpu_usage": 25.5,
*   "avg_memory_usage": 512.75,
*   "total_network_input": 1024,
*   "total_network_output": 2048
* }
*******************/
function homelab_fetch_whats_up_docker_data($api_url, $username = '', $password = '', $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'containers' => '/api/v1/containers',
        'stats' => '/api/v1/stats',
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
        if ($key === 'containers') {
            $fetched_data['total_containers'] = count($data);
            $status_counts = array(
                'running' => 0,
                'exited' => 0,
                'paused' => 0,
            );
            foreach ($data as $container) {
                $status = strtolower($container['State']);
                if (isset($status_counts[$status])) {
                    $status_counts[$status]++;
                }
            }
            $fetched_data['containers_running'] = $status_counts['running'];
            $fetched_data['containers_exited'] = $status_counts['exited'];
            $fetched_data['containers_paused'] = $status_counts['paused'];
        } elseif ($key === 'stats') {
            $total_cpu_usage = 0;
            $total_memory_usage = 0;
            $total_network_input = 0;
            $total_network_output = 0;
            foreach ($data as $stats) {
                $total_cpu_usage += $stats['cpu_usage'];
                $total_memory_usage += $stats['memory_usage'];
                $total_network_input += $stats['network_input'];
                $total_network_output += $stats['network_output'];
            }
            $fetched_data['avg_cpu_usage'] = $fetched_data['total_containers'] > 0 ? $total_cpu_usage / $fetched_data['total_containers'] : 0;
            $fetched_data['avg_memory_usage'] = $fetched_data['total_containers'] > 0 ? $total_memory_usage / $fetched_data['total_containers'] : 0;
            $fetched_data['total_network_input'] = $total_network_input;
            $fetched_data['total_network_output'] = $total_network_output;
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}