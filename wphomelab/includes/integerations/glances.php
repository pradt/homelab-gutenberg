<?php
/******************
 * Glances Data Collection
 * -----------------------
 * This function collects data from Glances, a cross-platform system monitoring tool, for dashboard display.
 * It fetches information about the system's CPU, memory, disk usage, network traffic, and running processes.
 *
 * Collected Data:
 * - CPU usage percentage
 * - Memory usage percentage
 * - Disk usage percentage
 * - Network traffic (download and upload speeds)
 * - Number of running processes
 *
 * Data not collected but available for extension:
 * - Detailed CPU information (per-core usage, load average)
 * - Detailed memory information (used, free, cached, buffers)
 * - Detailed disk information (per-partition usage, I/O stats)
 * - Detailed network information (per-interface traffic, connections)
 * - Process details (CPU usage, memory usage, status, user)
 * - System information (hostname, OS, uptime)
 * - Sensor data (temperatures, fan speeds, battery)
 *
 * Requirements:
 * - Glances API should be accessible via the provided API URL.
 * - Glances should be configured to allow remote access (if accessing remotely).
 *
 * Parameters:
 * - $api_url: The URL of the Glances API.
 * - $service_id: The ID of the service being monitored.
 *
 * Error Handling:
 * - Captures any errors encountered during the API request process.
 * - Stores error messages and timestamps for troubleshooting.
 *******************/

 function homelab_fetch_glances_data($api_url, $service_id) {
    $api_url = rtrim($api_url, '/');

    $endpoints = array(
        'all' => '/',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'all') {
            $cpu_percent = $data['cpu']['total'];
            $memory_percent = $data['mem']['percent'];
            $disk_percent = $data['fs'][0]['percent'];
            $network_rx = $data['network']['rx'];
            $network_tx = $data['network']['tx'];
            $num_processes = $data['processcount']['total'];

            $fetched_data['cpu_usage'] = $cpu_percent;
            $fetched_data['memory_usage'] = $memory_percent;
            $fetched_data['disk_usage'] = $disk_percent;
            $fetched_data['network_download'] = $network_rx;
            $fetched_data['network_upload'] = $network_tx;
            $fetched_data['num_processes'] = $num_processes;
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}