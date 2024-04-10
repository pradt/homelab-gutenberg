<?php
/******************
* Syncthing Relay Server Data Collection
* --------------------------------------
* This function collects data from a Syncthing Relay Server for dashboard display.
* It fetches information about the relay server's status, connected devices, and traffic statistics.
*
* Collected Data:
* - Relay server status (online/offline)
* - Total number of connected devices
* - Total traffic relayed (in bytes)
* - Traffic relayed in the last 24 hours (in bytes)
*
* Data not collected but available for extension:
* - Detailed device information (device ID, name, version)
* - Device-specific traffic statistics
* - Relay server configuration and settings
* - Historical traffic data
* - Geographic distribution of connected devices
*
* Opportunities for additional data:
* - Relay server performance metrics (CPU usage, memory usage)
* - Network latency and bandwidth metrics
* - Security and authentication settings
* - Integration with Syncthing device management API
*
* Requirements:
* - Syncthing Relay Server API should be accessible via the provided API URL.
* - API authentication (if required) should be properly configured.
*
* Parameters:
* - $api_url: The base URL of the Syncthing Relay Server API.
* - $api_key: The API key for authentication (if required).
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*
* Example of fetched_data structure:
* {
*   "relay_status": "online",
*   "connected_devices": 50,
*   "total_traffic_relayed": 1234567890,
*   "traffic_relayed_24h": 98765432
* }
*******************/
function homelab_fetch_syncthing_relay_data($api_url, $api_key = '', $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'status' => '/status',
        'traffic' => '/traffic',
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
            $fetched_data['relay_status'] = $data['status'];
            $fetched_data['connected_devices'] = $data['numDevices'];
        } elseif ($key === 'traffic') {
            $fetched_data['total_traffic_relayed'] = $data['totalBytesRelayed'];
            $fetched_data['traffic_relayed_24h'] = $data['bytesRelayed24h'];
        }
    }
    
    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}