<?php
/******************
* Proxmox Data Collection
* ----------------------
* This function collects data from Proxmox, a virtualization management platform, for dashboard display.
* It fetches information about the Proxmox nodes, virtual machines (VMs), LXC containers, storage resources,
* and other available resources.
*
* Collected Data:
* - Total number of resources
* - Resource counts for each type (nodes, VMs, LXC containers, etc.)
* - Number of running, stopped, and suspended resources for each type
* - Total storage capacity and usage
* - CPU usage percentage
* - Memory usage percentage
* - Raw API responses for each endpoint
*
* Data not collected but available for extension:
* - Detailed node information (name, IP address, CPU cores, memory)
* - Detailed VM and LXC container information (name, ID, OS, IP address, disk size)
* - Network usage statistics
* - Storage performance metrics (IOPS, throughput)
* - Task and event logs
* - User and permission management
*
* Example of fetched_data structure:
* {
*   "total_resources": 20,
*   "node_total": 3,
*   "node_running": 2,
*   "node_stopped": 1,
*   "qemu_total": 10,
*   "qemu_running": 8,
*   "qemu_stopped": 1,
*   "qemu_suspended": 1,
*   "lxc_total": 5,
*   "lxc_running": 4,
*   "lxc_stopped": 1,
*   "storage_capacity": 5000,
*   "storage_used": 3500,
*   "cpu_usage_percentage": 60,
*   "memory_usage_percentage": 75,
*   "raw_responses": {
*     "resources": "...",
*     "storage": "...",
*     "status": "..."
*   }
* }
*
* Requirements:
* - Proxmox API should be accessible via the provided API URL.
* - API authentication requires a valid username and API token.
*
* Parameters:
* - $api_url: The base URL of the Proxmox API.
* - $username: The username for authentication.
* - $api_key: The API token for authentication.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_proxmox_data($api_url, $username, $api_key, $service_id) {
    $api_url = rtrim($api_url, '/');
    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    $endpoints = array(
        'resources' => '/api2/json/cluster/resources',
        'storage' => '/api2/json/storage',
        'status' => '/api2/json/cluster/status',
        'nodes' => '/api2/json/nodes',
    );

    $fetched_data['raw_responses'] = array();

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'PVEAPIToken=' . $username . '!' . $api_key,
            ),
            'sslverify' => false,
        );

        error_log("Request Arguments: " . print_r($args, true));

        // Bypass SSL certificate verification
        //add_filter('https_local_ssl_verify', '__return_false');
        //add_filter('https_ssl_verify', '__return_false');

        $response = wp_remote_get($url, $args);

        // Remove the SSL verification bypass filters
        //remove_filter('https_local_ssl_verify', '__return_false');
        //remove_filter('https_ssl_verify', '__return_false');

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            error_log("Proxmox API Error: " . $error_message);
            continue;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $fetched_data['raw_responses'][$key] = $response_body;

        if ($response_code !== 200) {
            $error_message = "API request failed for endpoint '{$key}' with status code: " . $response_code;
            $error_timestamp = current_time('mysql');
            error_log("Proxmox API Error: " . $error_message);
            error_log("Response Body: " . $response_body);
            continue;
        }

        $data = json_decode($response_body, true);

        if ($data === null) {
            $error_message = "Invalid API response for endpoint '{$key}': " . $response_body;
            $error_timestamp = current_time('mysql');
            error_log("Proxmox API Error: " . $error_message);
            continue;
        }

        if (!isset($data['data']) || !is_array($data['data'])) {
            $error_message = "Missing or invalid 'data' in API response for endpoint '{$key}': " . $response_body;
            $error_timestamp = current_time('mysql');
            error_log("Proxmox API Error: " . $error_message);
            continue;
        }

        if ($key === 'resources') {
            $fetched_data['total_resources'] = count($data['data']);
            $resource_counts = array();

            foreach ($data['data'] as $resource) {
                $type = $resource['type'];

                if (!isset($resource_counts[$type])) {
                    $resource_counts[$type] = array(
                        'total' => 0,
                        'running' => 0,
                        'stopped' => 0,
                        'suspended' => 0,
                    );
                }

                $resource_counts[$type]['total']++;

                if ($type === 'node') {
                    if ($resource['status'] === 'online') {
                        $resource_counts[$type]['running']++;
                    } else {
                        $resource_counts[$type]['stopped']++;
                    }
                } elseif ($type === 'qemu' || $type === 'lxc') {
                    if ($resource['status'] === 'running') {
                        $resource_counts[$type]['running']++;
                    } elseif ($resource['status'] === 'stopped') {
                        $resource_counts[$type]['stopped']++;
                    } elseif ($resource['status'] === 'suspended') {
                        $resource_counts[$type]['suspended']++;
                    }
                }
            }

            foreach ($resource_counts as $type => $counts) {
                $fetched_data[$type . '_total'] = $counts['total'];
                $fetched_data[$type . '_running'] = $counts['running'];
                $fetched_data[$type . '_stopped'] = $counts['stopped'];
                $fetched_data[$type . '_suspended'] = $counts['suspended'];
            }
        } elseif ($key === 'storage') {
            $total_capacity = 0;
            $total_used = 0;

            foreach ($data['data'] as $storage) {
                if (isset($storage['maxdisk'])) {
                    $total_capacity += $storage['maxdisk'];
                }
                if (isset($storage['disk'])) {
                    $total_used += $storage['disk'];
                }
            }

            $fetched_data['storage_capacity'] = $total_capacity;
            $fetched_data['storage_used'] = $total_used;
        } elseif ($key === 'status') {
            if (isset($data['data']['cpu'])) {
                $fetched_data['cpu_usage_percentage'] = $data['data']['cpu'] * 100;
            }
            if (isset($data['data']['memory']['used']) && isset($data['data']['memory']['total'])) {
                $fetched_data['memory_usage_percentage'] = $data['data']['memory']['used'] / $data['data']['memory']['total'] * 100;
            }
        } elseif ($key === 'nodes') {
            $total_cpu_usage = 0;
            $total_memory_used = 0;
            $total_memory_total = 0;

            foreach ($data['data'] as $node) {
                if (isset($node['cpu'])) {
                    $total_cpu_usage += $node['cpu'];
                }
                if (isset($node['mem'])) {
                    $total_memory_used += $node['mem'];
                }
                if (isset($node['maxmem'])) {
                    $total_memory_total += $node['maxmem'];
                }
            }

            $node_count = count($data['data']);
            if ($node_count > 0) {
                $fetched_data['cpu_usage_percentage'] = $total_cpu_usage / $node_count;
                if ($total_memory_total > 0) {
                    $fetched_data['memory_usage_percentage'] = $total_memory_used / $total_memory_total * 100;
                }
            }
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}