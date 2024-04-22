<?php
/******************
* Overseer Data Collection
* -------------------------
* This function collects data from Overseer, a server monitoring tool, for dashboard display.
* It fetches information about the monitored servers, including their status, CPU usage, memory usage, and disk usage.
*
* Collected Data:
* - Total number of monitored servers
* - Number of servers by status (online, offline, warning, unknown)
* - Average CPU usage across all servers
* - Average memory usage across all servers
* - Average disk usage across all servers
*
* Data not collected but available for extension:
* - Detailed server information (name, IP address, operating system)
* - Server-specific CPU, memory, and disk usage
* - Network traffic and bandwidth usage
* - Process and service monitoring details
* - Alert and notification settings
* - Historical performance data
* - Overseer configuration and settings
*
* Example of fetched_data structure:
* {
*   "total_servers": 10,
*   "servers_online": 8,
*   "servers_offline": 1,
*   "servers_warning": 1,
*   "servers_unknown": 0,
*   "avg_cpu_usage": 45.6,
*   "avg_memory_usage": 60.2,
*   "avg_disk_usage": 75.8
* }
*
* Requirements:
* - Overseer API should be accessible via the provided API URL.
* - API authentication token (API key) may be required depending on Overseer configuration.
*
* Parameters:
* - $api_url: The base URL of the Overseer API.
* - $api_key: The API key for authentication (if required).
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/

function homelab_fetch_overseer_data($api_url, $api_key = '', $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'servers' => '/api/v1/servers',
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
        
        if ($key === 'servers') {
            $fetched_data['total_servers'] = count($data);
            $status_counts = array(
                'online' => 0,
                'offline' => 0,
                'warning' => 0,
                'unknown' => 0,
            );
            $total_cpu_usage = 0;
            $total_memory_usage = 0;
            $total_disk_usage = 0;
            
            foreach ($data as $server) {
                $status = strtolower($server['status']);
                if (isset($status_counts[$status])) {
                    $status_counts[$status]++;
                } else {
                    $status_counts['unknown']++;
                }
                $total_cpu_usage += $server['cpu_usage'];
                $total_memory_usage += $server['memory_usage'];
                $total_disk_usage += $server['disk_usage'];
            }
            
            $fetched_data['servers_online'] = $status_counts['online'];
            $fetched_data['servers_offline'] = $status_counts['offline'];
            $fetched_data['servers_warning'] = $status_counts['warning'];
            $fetched_data['servers_unknown'] = $status_counts['unknown'];
            $fetched_data['avg_cpu_usage'] = $fetched_data['total_servers'] > 0 ? $total_cpu_usage / $fetched_data['total_servers'] : 0;
            $fetched_data['avg_memory_usage'] = $fetched_data['total_servers'] > 0 ? $total_memory_usage / $fetched_data['total_servers'] : 0;
            $fetched_data['avg_disk_usage'] = $fetched_data['total_servers'] > 0 ? $total_disk_usage / $fetched_data['total_servers'] : 0;
        }
    }
    
    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}