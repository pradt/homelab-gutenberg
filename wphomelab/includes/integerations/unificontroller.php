<?php
/******************
* Unifi Controller Data Collection
* --------------------------------
* This function collects data from the Unifi Controller, a network management system, for dashboard display.
* It fetches information about the managed devices, clients, and network statistics.
*
* Collected Data:
* - Total number of managed devices
* - Number of devices by type (access points, switches, gateways)
* - Total number of connected clients
* - Number of clients by connection type (wired, wireless)
* - Total network traffic (upload and download)
*
* Data not collected but available for extension:
* - Detailed device information (name, model, IP address, firmware version)
* - Detailed client information (name, IP address, MAC address, operating system)
* - Device and client-specific traffic statistics
* - Network health and performance metrics (latency, signal strength, channel utilization)
* - WLAN configuration and settings
* - Security and access control settings
* - Event logs and alerts
*
* Requirements:
* - Unifi Controller API should be accessible via the provided API URL.
* - API authentication requires a valid username and password.
*
* Parameters:
* - $api_url: The base URL of the Unifi Controller API.
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
*   "total_devices": 10,
*   "devices_access_points": 5,
*   "devices_switches": 3,
*   "devices_gateways": 2,
*   "total_clients": 50,
*   "clients_wired": 20,
*   "clients_wireless": 30,
*   "total_traffic_upload": 1000,
*   "total_traffic_download": 5000
* }
*******************/
function homelab_fetch_unifi_data($api_url, $username, $password, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'devices' => '/api/s/default/stat/device',
        'clients' => '/api/s/default/stat/sta',
        'networks' => '/api/s/default/rest/networkconf',
    );
    
    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;
    
    // Authenticate and get the session cookie
    $auth_url = $api_url . '/api/login';
    $auth_data = array(
        'username' => $username,
        'password' => $password,
    );
    $auth_response = wp_remote_post($auth_url, array(
        'headers' => array('Content-Type' => 'application/json'),
        'body' => json_encode($auth_data),
    ));
    
    if (is_wp_error($auth_response)) {
        $error_message = "Authentication failed: " . $auth_response->get_error_message();
        $error_timestamp = current_time('mysql');
        homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
        return $fetched_data;
    }
    
    $auth_body = json_decode(wp_remote_retrieve_body($auth_response), true);
    $session_cookie = 'unifises=' . $auth_body['data'];
    
    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Cookie' => $session_cookie,
            ),
        );
        
        $response = wp_remote_get($url, $args);
        
        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($key === 'devices') {
            $fetched_data['total_devices'] = count($data['data']);
            
            $type_counts = array(
                'access_points' => 0,
                'switches' => 0,
                'gateways' => 0,
            );
            
            foreach ($data['data'] as $device) {
                $type = strtolower($device['type']);
                if (isset($type_counts[$type])) {
                    $type_counts[$type]++;
                }
            }
            
            $fetched_data['devices_access_points'] = $type_counts['access_points'];
            $fetched_data['devices_switches'] = $type_counts['switches'];
            $fetched_data['devices_gateways'] = $type_counts['gateways'];
        }
        
        if ($key === 'clients') {
            $fetched_data['total_clients'] = count($data['data']);
            
            $connection_counts = array(
                'wired' => 0,
                'wireless' => 0,
            );
            
            foreach ($data['data'] as $client) {
                $is_wired = $client['is_wired'];
                if ($is_wired) {
                    $connection_counts['wired']++;
                } else {
                    $connection_counts['wireless']++;
                }
            }
            
            $fetched_data['clients_wired'] = $connection_counts['wired'];
            $fetched_data['clients_wireless'] = $connection_counts['wireless'];
        }
        
        if ($key === 'networks') {
            $total_traffic_upload = 0;
            $total_traffic_download = 0;
            
            foreach ($data['data'] as $network) {
                $total_traffic_upload += $network['tx_bytes'];
                $total_traffic_download += $network['rx_bytes'];
            }
            
            $fetched_data['total_traffic_upload'] = $total_traffic_upload;
            $fetched_data['total_traffic_download'] = $total_traffic_download;
        }
    }
    
    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}