<?php
/******************
* Watchtower Data Collection
* ----------------------
* This function collects data from Watchtower, a container monitoring and management tool, for dashboard display.
* It fetches information about the monitored containers, including their status, image details, and resource usage.
*
* Collected Data:
* - Total number of monitored containers
* - Number of containers by status (running, stopped, paused, unknown)
* - Latest image version for each container
* - Resource usage (CPU and memory) for each container
*
* Data Structure Examples:
* fetched_data = {
*   'total_containers': 10,
*   'containers_running': 8,
*   'containers_stopped': 1,
*   'containers_paused': 0,
*   'containers_unknown': 1,
*   'containers': [
*     {
*       'name': 'app1',
*       'image': 'app1:latest',
*       'status': 'running',
*       'cpu_usage': 0.5,
*       'memory_usage': 256
*     },
*     ...
*   ]
* }
*
* Data not collected but available for extension:
* - Detailed container information (ID, created time, labels)
* - Container network settings and port mappings
* - Container volumes and mounts
* - Historical resource usage data
* - Container logs and events
* - Watchtower configuration and settings
*
* Opportunities for additional data:
* - Container health checks and status history
* - Container performance metrics and thresholds
* - Container dependency mappings and relationships
* - Integration with other monitoring systems or logging platforms
*
* Requirements:
* - Watchtower API should be accessible via the provided API URL.
* - API authentication token (API key) is required for accessing the Watchtower API.
*
* Parameters:
* - $api_url: The base URL of the Watchtower API.
* - $api_key: The API key for authentication.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_watchtower_data($api_url, $api_key, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'containers' => '/api/v1/containers',
    );
    
    $fetched_data = array(
        'total_containers' => 0,
        'containers_running' => 0,
        'containers_stopped' => 0,
        'containers_paused' => 0,
        'containers_unknown' => 0,
        'containers' => array(),
    );
    
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
        if ($key === 'containers') {
            $fetched_data['total_containers'] = count($data);
            
            foreach ($data as $container) {
                $status = strtolower($container['status']);
                switch ($status) {
                    case 'running':
                        $fetched_data['containers_running']++;
                        break;
                    case 'stopped':
                        $fetched_data['containers_stopped']++;
                        break;
                    case 'paused':
                        $fetched_data['containers_paused']++;
                        break;
                    default:
                        $fetched_data['containers_unknown']++;
                        break;
                }
                
                $fetched_data['containers'][] = array(
                    'name' => $container['name'],
                    'image' => $container['image'],
                    'status' => $status,
                    'cpu_usage' => $container['cpu_usage'],
                    'memory_usage' => $container['memory_usage'],
                );
            }
        }
    }
    
    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}