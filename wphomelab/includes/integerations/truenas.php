<?php
/******************
* TrueNAS Data Collection
* ----------------------
* This function collects data from TrueNAS, a network-attached storage system, for dashboard display.
* It fetches information about the system status, storage usage, and network interfaces.
*
* Collected Data:
* - System information (hostname, version, uptime)
* - Storage usage (total capacity, used space, available space)
* - Network interface status and throughput
*
* Data not collected but available for extension:
* - Detailed storage information (pool status, disk status, snapshots)
* - CPU and memory usage statistics
* - Service status (SSH, FTP, SMB, NFS)
* - Replication and backup status
* - Alert and notification settings
* - Historical performance data
* - TrueNAS configuration and settings
*
* Requirements:
* - TrueNAS API should be accessible via the provided API URL.
* - API authentication (username and password or API key) is required.
*
* Parameters:
* - $api_url: The base URL of the TrueNAS API.
* - $auth: An array containing the authentication details (username and password or API key).
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*
* Example of fetched_data structure:
* {
*   "system_info": {
*     "hostname": "truenas.local",
*     "version": "TrueNAS-12.0-U4",
*     "uptime": "10 days, 5 hours, 30 minutes"
*   },
*   "storage_usage": {
*     "total_capacity": 10995116277760,
*     "used_space": 7516192768000,
*     "available_space": 3478923509760
*   },
*   "network_interfaces": [
*     {
*       "name": "em0",
*       "status": "up",
*       "throughput_rx": 5621003,
*       "throughput_tx": 2198677
*     },
*     {
*       "name": "em1",
*       "status": "down",
*       "throughput_rx": 0,
*       "throughput_tx": 0
*     }
*   ]
* }
*******************/
function homelab_fetch_truenas_data($api_url, $auth, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'system_info' => '/api/v2.0/system/info',
        'storage_usage' => '/api/v2.0/storage',
        'network_interfaces' => '/api/v2.0/interface',
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

        if (isset($auth['api_key'])) {
            $args['headers']['Authorization'] = 'Bearer ' . $auth['api_key'];
        } elseif (isset($auth['username']) && isset($auth['password'])) {
            $args['headers']['Authorization'] = 'Basic ' . base64_encode($auth['username'] . ':' . $auth['password']);
        }

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'system_info') {
            $fetched_data['system_info'] = array(
                'hostname' => $data['hostname'],
                'version' => $data['version'],
                'uptime' => $data['uptime_str'],
            );
        } elseif ($key === 'storage_usage') {
            $fetched_data['storage_usage'] = array(
                'total_capacity' => $data['total_capacity'],
                'used_space' => $data['used_space'],
                'available_space' => $data['available_space'],
            );
        } elseif ($key === 'network_interfaces') {
            $fetched_data['network_interfaces'] = array();
            foreach ($data as $interface) {
                $fetched_data['network_interfaces'][] = array(
                    'name' => $interface['name'],
                    'status' => $interface['state'],
                    'throughput_rx' => $interface['received']['bytes'],
                    'throughput_tx' => $interface['sent']['bytes'],
                );
            }
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}