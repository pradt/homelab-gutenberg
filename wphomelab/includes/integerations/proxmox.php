<?php
/******************
* Proxmox Data Collection
* ----------------------
* This function collects data from Proxmox, a virtualization management platform, for dashboard display.
* It fetches information about the Proxmox nodes, virtual machines (VMs), and storage resources.
*
* Collected Data:
* - Total number of nodes
* - Number of online and offline nodes
* - Total number of VMs
* - Number of running, stopped, and suspended VMs
* - Total storage capacity and usage
* - CPU usage percentage
* - Memory usage percentage
*
* Data not collected but available for extension:
* - Detailed node information (name, IP address, CPU cores, memory)
* - Detailed VM information (name, ID, OS, IP address, disk size)
* - Network usage statistics
* - Storage performance metrics (IOPS, throughput)
* - Task and event logs
* - User and permission management
*
* Example of fetched_data structure:
* {
*   "total_nodes": 3,
*   "nodes_online": 2,
*   "nodes_offline": 1,
*   "total_vms": 10,
*   "vms_running": 8,
*   "vms_stopped": 1,
*   "vms_suspended": 1,
*   "storage_capacity": 5000,
*   "storage_used": 3500,
*   "cpu_usage_percentage": 60,
*   "memory_usage_percentage": 75
* }
*
* Requirements:
* - Proxmox API should be accessible via the provided API URL.
* - API authentication requires a valid username and password.
*
* Parameters:
* - $api_url: The base URL of the Proxmox API.
* - $username: The username for authentication.
* - $password: The password for authentication.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_proxmox_data($api_url, $username, $password, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'nodes' => '/api2/json/nodes',
        'vms' => '/api2/json/cluster/resources?type=vm',
        'storage' => '/api2/json/storage',
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

        if ($key === 'nodes') {
            $fetched_data['total_nodes'] = count($data['data']);
            $online_nodes = 0;
            $offline_nodes = 0;
            foreach ($data['data'] as $node) {
                if ($node['online']) {
                    $online_nodes++;
                } else {
                    $offline_nodes++;
                }
            }
            $fetched_data['nodes_online'] = $online_nodes;
            $fetched_data['nodes_offline'] = $offline_nodes;
        } elseif ($key === 'vms') {
            $fetched_data['total_vms'] = count($data['data']);
            $running_vms = 0;
            $stopped_vms = 0;
            $suspended_vms = 0;
            foreach ($data['data'] as $vm) {
                if ($vm['status'] === 'running') {
                    $running_vms++;
                } elseif ($vm['status'] === 'stopped') {
                    $stopped_vms++;
                } elseif ($vm['status'] === 'suspended') {
                    $suspended_vms++;
                }
            }
            $fetched_data['vms_running'] = $running_vms;
            $fetched_data['vms_stopped'] = $stopped_vms;
            $fetched_data['vms_suspended'] = $suspended_vms;
        } elseif ($key === 'storage') {
            $total_capacity = 0;
            $total_used = 0;
            foreach ($data['data'] as $storage) {
                $total_capacity += $storage['maxdisk'];
                $total_used += $storage['disk'];
            }
            $fetched_data['storage_capacity'] = $total_capacity;
            $fetched_data['storage_used'] = $total_used;
        }
    }

    // Fetch CPU and memory usage (assuming available endpoints)
    $cpu_usage_url = $api_url . '/api2/json/nodes/pve/status';
    $memory_usage_url = $api_url . '/api2/json/nodes/pve/status';

    $cpu_usage_response = wp_remote_post($cpu_usage_url, $args);
    $memory_usage_response = wp_remote_post($memory_usage_url, $args);

    if (!is_wp_error($cpu_usage_response)) {
        $cpu_usage_data = json_decode(wp_remote_retrieve_body($cpu_usage_response), true);
        $fetched_data['cpu_usage_percentage'] = $cpu_usage_data['data']['cpu'];
    }

    if (!is_wp_error($memory_usage_response)) {
        $memory_usage_data = json_decode(wp_remote_retrieve_body($memory_usage_response), true);
        $fetched_data['memory_usage_percentage'] = $memory_usage_data['data']['memory']['used'] / $memory_usage_data['data']['memory']['total'] * 100;
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}