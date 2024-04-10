<?php
/******************
* Mikrotik Data Collection
* -------------------------
* This function collects data from Mikrotik, a network router and firewall, for dashboard display.
* It fetches information about the device's system resources, network interfaces, and traffic statistics.
*
* Collected Data:
* - Device uptime
* - CPU load percentage
* - Total memory and free memory
* - Total number of network interfaces
* - Number of interfaces by status (up, down)
* - Total received and transmitted bytes
* - Total received and transmitted packets
*
* Data not collected but available for extension:
* - Detailed interface information (name, IP address, MAC address)
* - Interface-specific traffic statistics
* - Firewall rules and statistics
* - DHCP server leases
* - Wireless client information
* - Routing table entries
* - System logs and events
*
* Opportunities for data utilization:
* - Monitoring device health and performance
* - Analyzing network traffic patterns and trends
* - Identifying top talkers and bandwidth consumers
* - Detecting network anomalies and security threats
* - Optimizing network configuration and resource allocation
*
* Requirements:
* - Mikrotik device should be accessible via the provided API URL.
* - API authentication using username and password is required.
*
* Parameters:
* - $api_url: The base URL of the Mikrotik API.
* - $username: The username for API authentication.
* - $password: The password for API authentication.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*
* Example of fetched_data structure:
* {
*   "uptime": "1d 4h 30m",
*   "cpu_load": 15.3,
*   "total_memory": 1024,
*   "free_memory": 512,
*   "total_interfaces": 5,
*   "interfaces_up": 4,
*   "interfaces_down": 1,
*   "total_rx_bytes": 10485760,
*   "total_tx_bytes": 5242880,
*   "total_rx_packets": 100000,
*   "total_tx_packets": 50000
* }
*******************/
function homelab_fetch_mikrotik_data($api_url, $username, $password, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'system' => '/rest/system/resource',
        'interfaces' => '/rest/interface',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $args = array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($username . ':' . $password),
            ),
        );

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'system') {
            $fetched_data['uptime'] = $data['uptime'];
            $fetched_data['cpu_load'] = $data['cpu-load'];
            $fetched_data['total_memory'] = $data['total-memory'];
            $fetched_data['free_memory'] = $data['free-memory'];
        } elseif ($key === 'interfaces') {
            $fetched_data['total_interfaces'] = count($data);
            $status_counts = array(
                'up' => 0,
                'down' => 0,
            );
            $total_rx_bytes = 0;
            $total_tx_bytes = 0;
            $total_rx_packets = 0;
            $total_tx_packets = 0;

            foreach ($data as $interface) {
                $status = strtolower($interface['running']);
                if (isset($status_counts[$status])) {
                    $status_counts[$status]++;
                }
                $total_rx_bytes += $interface['rx-byte'];
                $total_tx_bytes += $interface['tx-byte'];
                $total_rx_packets += $interface['rx-packet'];
                $total_tx_packets += $interface['tx-packet'];
            }

            $fetched_data['interfaces_up'] = $status_counts['up'];
            $fetched_data['interfaces_down'] = $status_counts['down'];
            $fetched_data['total_rx_bytes'] = $total_rx_bytes;
            $fetched_data['total_tx_bytes'] = $total_tx_bytes;
            $fetched_data['total_rx_packets'] = $total_rx_packets;
            $fetched_data['total_tx_packets'] = $total_tx_packets;
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}