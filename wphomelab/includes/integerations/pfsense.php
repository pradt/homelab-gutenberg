<?php
/******************
* pfSense Data Collection
* ----------------------
* This function collects data from pfSense, a firewall and router software, for dashboard display.
* It fetches information about the firewall status, network interfaces, and system statistics.
*
* Collected Data:
* - Firewall status (enabled/disabled)
* - Number of active firewall rules
* - List of network interfaces with their status and IP addresses
* - System CPU usage percentage
* - System memory usage percentage
* - System uptime
*
* Data not collected but available for extension:
* - Detailed firewall rule information (source, destination, protocol, action)
* - Traffic statistics per interface (bytes sent/received, packets sent/received)
* - VPN status and connection details
* - DHCP leases and static mappings
* - DNS resolver settings and query statistics
* - Captive portal status and user statistics
* - Package and update information
*
* Requirements:
* - pfSense API should be accessible via the provided API URL.
* - API authentication using either an API key or username/password.
*
* Parameters:
* - $api_url: The base URL of the pfSense API.
* - $api_key: The API key for authentication (optional).
* - $username: The username for authentication (optional).
* - $password: The password for authentication (optional).
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*
* Example of fetched_data structure:
* {
*   "firewall_status": "enabled",
*   "active_rules_count": 10,
*   "interfaces": [
*     {
*       "name": "WAN",
*       "status": "up",
*       "ip_address": "192.168.1.1"
*     },
*     {
*       "name": "LAN",
*       "status": "up",
*       "ip_address": "10.0.0.1"
*     }
*   ],
*   "cpu_usage": 25.5,
*   "memory_usage": 60.2,
*   "uptime": "3 days, 5 hours, 30 minutes"
* }
*******************/
function homelab_fetch_pfsense_data($api_url, $api_key = '', $username = '', $password = '', $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'firewall' => '/api/v1/firewall/status',
        'interfaces' => '/api/v1/interfaces',
        'system' => '/api/v1/system/stats',
    );
    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    $args = array(
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
    );

    if (!empty($api_key)) {
        $args['headers']['Authorization'] = 'Bearer ' . $api_key;
    } elseif (!empty($username) && !empty($password)) {
        $args['headers']['Authorization'] = 'Basic ' . base64_encode($username . ':' . $password);
    }

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'firewall') {
            $fetched_data['firewall_status'] = $data['status'];
            $fetched_data['active_rules_count'] = $data['active_rules_count'];
        } elseif ($key === 'interfaces') {
            $fetched_data['interfaces'] = array();
            foreach ($data as $interface) {
                $fetched_data['interfaces'][] = array(
                    'name' => $interface['name'],
                    'status' => $interface['status'],
                    'ip_address' => $interface['ip_address'],
                );
            }
        } elseif ($key === 'system') {
            $fetched_data['cpu_usage'] = $data['cpu_usage'];
            $fetched_data['memory_usage'] = $data['memory_usage'];
            $fetched_data['uptime'] = $data['uptime'];
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}