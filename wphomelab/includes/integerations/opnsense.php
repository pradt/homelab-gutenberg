<?php
/******************
* OPNSense Data Collection
* ----------------------
* This function collects data from OPNSense, a firewall and routing platform, for dashboard display.
* It fetches information about the system status, interfaces, firewall rules, and VPN connections.
*
* Collected Data:
* - System status (hostname, version, uptime)
* - Interface status (name, IP address, status, traffic statistics)
* - Firewall rule counts (total, enabled, disabled)
* - VPN connection status (name, type, status, connected users)
*
* Data Structure Example (fetched_data):
* {
*   "system_status": {
*     "hostname": "opnsense.example.com",
*     "version": "21.7.1",
*     "uptime": "10 days, 5 hours, 30 minutes"
*   },
*   "interfaces": [
*     {
*       "name": "WAN",
*       "ip_address": "192.0.2.100",
*       "status": "up",
*       "traffic_in": 1234567,
*       "traffic_out": 2345678
*     },
*     ...
*   ],
*   "firewall_rules": {
*     "total": 100,
*     "enabled": 80,
*     "disabled": 20
*   },
*   "vpn_connections": [
*     {
*       "name": "Site-to-Site VPN",
*       "type": "IPsec",
*       "status": "connected",
*       "connected_users": 5
*     },
*     ...
*   ]
* }
*
* Data not collected but available for extension:
* - Detailed system information (CPU, memory, disk usage)
* - DHCP leases and static mappings
* - DNS resolver settings and queries
* - Proxy server configuration and usage
* - Intrusion detection and prevention (IDS/IPS) events
* - Captive portal sessions and authentication logs
* - Traffic shaper settings and statistics
*
* Requirements:
* - OPNSense API should be accessible via the provided API URL.
* - API authentication using username and password is required.
*
* Parameters:
* - $api_url: The base URL of the OPNSense API.
* - $username: The username for API authentication.
* - $password: The password for API authentication.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_opnsense_data($api_url, $username, $password, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'system_status' => '/api/diagnostics/system/status',
        'interfaces' => '/api/interfaces',
        'firewall_rules' => '/api/firewall/filter/searchRule',
        'vpn_connections' => '/api/vpn/ipsec/sainfo',
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
        
        switch ($key) {
            case 'system_status':
                $fetched_data['system_status'] = array(
                    'hostname' => $data['hostname'],
                    'version' => $data['product_version'],
                    'uptime' => $data['uptime'],
                );
                break;
            case 'interfaces':
                $fetched_data['interfaces'] = array();
                foreach ($data as $interface) {
                    $fetched_data['interfaces'][] = array(
                        'name' => $interface['descr'],
                        'ip_address' => $interface['ipaddr'],
                        'status' => $interface['status'],
                        'traffic_in' => $interface['inbytes'],
                        'traffic_out' => $interface['outbytes'],
                    );
                }
                break;
            case 'firewall_rules':
                $fetched_data['firewall_rules'] = array(
                    'total' => $data['total'],
                    'enabled' => $data['enabled'],
                    'disabled' => $data['disabled'],
                );
                break;
            case 'vpn_connections':
                $fetched_data['vpn_connections'] = array();
                foreach ($data as $connection) {
                    $fetched_data['vpn_connections'][] = array(
                        'name' => $connection['description'],
                        'type' => $connection['ike_version'],
                        'status' => $connection['state'],
                        'connected_users' => count($connection['connected_users']),
                    );
                }
                break;
        }
    }
    
    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}