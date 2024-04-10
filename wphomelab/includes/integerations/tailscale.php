<?php
/******************
* Tailscale Data Collection
* -------------------------
* This function collects data from Tailscale, a VPN service, for dashboard display.
* It fetches information about the connected devices, including their status, IP addresses, and last active times.
*
* Collected Data:
* - Total number of connected devices
* - Number of devices by status (online, offline)
* - Average last active time across all devices
* - List of connected devices with their name, IP address, and last active time
*
* Data not collected but available for extension:
* - Device operating system and version
* - Device architecture (e.g., amd64, arm64)
* - Device tags and ACL settings
* - Device public key and node key
* - Subnet routes and advertised routes
* - Tailscale network settings and configuration
*
* Requirements:
* - Tailscale API should be accessible via the provided API URL.
* - API authentication token (API key) is required for accessing the Tailscale API.
*
* Parameters:
* - $api_url: The base URL of the Tailscale API.
* - $api_key: The API key for authentication.
* - $service_id: The ID of the service being monitored.
*
* Stored Data Structure Example (fetched_data):
* {
*   "total_devices": 5,
*   "devices_online": 3,
*   "devices_offline": 2,
*   "avg_last_active_time": 1621234567,
*   "devices": [
*     {
*       "name": "Device 1",
*       "ip_address": "100.64.0.1",
*       "last_active_time": 1621234567
*     },
*     {
*       "name": "Device 2",
*       "ip_address": "100.64.0.2",
*       "last_active_time": 1621234568
*     },
*     ...
*   ]
* }
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_tailscale_data($api_url, $api_key, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'devices' => '/api/v2/tailnet/devices',
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
        
        if ($key === 'devices') {
            $fetched_data['total_devices'] = count($data['devices']);
            $status_counts = array(
                'online' => 0,
                'offline' => 0,
            );
            $total_last_active_time = 0;
            $fetched_data['devices'] = array();
            
            foreach ($data['devices'] as $device) {
                $status = $device['online'] ? 'online' : 'offline';
                $status_counts[$status]++;
                
                $fetched_data['devices'][] = array(
                    'name' => $device['hostname'],
                    'ip_address' => $device['addresses'][0],
                    'last_active_time' => strtotime($device['last_active']),
                );
                
                $total_last_active_time += strtotime($device['last_active']);
            }
            
            $fetched_data['devices_online'] = $status_counts['online'];
            $fetched_data['devices_offline'] = $status_counts['offline'];
            $fetched_data['avg_last_active_time'] = $fetched_data['total_devices'] > 0 ? $total_last_active_time / $fetched_data['total_devices'] : 0;
        }
    }
    
    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}