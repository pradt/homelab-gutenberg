<?php 
/******************
* Portainer Data Collection
* -------------------------
* This function collects data from Portainer, a container management tool, for dashboard display.
* It fetches information about the managed endpoints, stacks, containers, and images.
*
* Collected Data:
* - Total number of endpoints
* - Total number of stacks
* - Total number of containers (running, stopped, total)
* - Total number of images
*
* Data not collected but available for extension:
* - Detailed endpoint information (name, URL, status, version)
* - Stack-specific details (name, status, services, containers)
* - Container-specific details (name, status, image, created, network, ports)
* - Image-specific details (name, tag, size, created)
* - Resource usage statistics (CPU, memory, network)
* - Event and activity logs
* - User and team management information
* - Portainer settings and configurations
*
* Example of fetched_data structure:
* {
*   "total_endpoints": 5,
*   "total_stacks": 10,
*   "containers_running": 25,
*   "containers_stopped": 5,
*   "total_containers": 30,
*   "total_images": 50
* }
*
* Requirements:
* - Portainer API should be accessible via the provided API URL.
* - API authentication key (API key) is required for accessing the Portainer API.
*
* Parameters:
* - $api_url: The base URL of the Portainer API.
* - $api_key: The API key for authentication.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_portainer_data($api_url, $api_key, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'endpoints' => '/api/endpoints',
        'stacks' => '/api/stacks',
        'containers' => '/api/containers/json',
        'images' => '/api/images/json',
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
        
        switch ($key) {
            case 'endpoints':
                $fetched_data['total_endpoints'] = count($data);
                break;
            case 'stacks':
                $fetched_data['total_stacks'] = count($data);
                break;
            case 'containers':
                $fetched_data['containers_running'] = count(array_filter($data, function($container) {
                    return $container['State'] === 'running';
                }));
                $fetched_data['containers_stopped'] = count(array_filter($data, function($container) {
                    return $container['State'] === 'exited';
                }));
                $fetched_data['total_containers'] = count($data);
                break;
            case 'images':
                $fetched_data['total_images'] = count($data);
                break;
        }
    }
    
    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}