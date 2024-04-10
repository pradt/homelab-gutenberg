<?php
/******************
* Netdata Data Collection
* ----------------------
* This function collects data from Netdata, a real-time performance and health monitoring tool, for dashboard display.
* It fetches information about the system metrics, including CPU usage, memory usage, disk I/O, and network traffic.
*
* Collected Data:
* - CPU usage percentage
* - Memory usage percentage
* - Disk read and write operations per second
* - Network receive and transmit bandwidth in bytes per second
*
* Data not collected but available for extension:
* - Detailed CPU metrics (per-core usage, load average, interrupts)
* - Detailed memory metrics (used, cached, buffered, free)
* - Detailed disk metrics (I/O time, queued operations, backlog)
* - Detailed network metrics (packets, errors, drops)
* - System load, uptime, and processes
* - Application-specific metrics (e.g., web server requests, database queries)
* - Custom plugins and charts
*
* Opportunities for additional data:
* - Historical data and trends over time
* - Anomaly detection and alerting based on thresholds
* - Correlation of metrics across multiple systems or services
* - Integration with other monitoring and logging tools
*
* Requirements:
* - Netdata API should be accessible via the provided API URL.
* - API authentication token (API key) may be required depending on Netdata configuration.
*
* Parameters:
* - $api_url: The base URL of the Netdata API.
* - $api_key: The API key for authentication (if required).
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*
* Example of fetched_data structure:
* {
*   "cpu_usage_percentage": 25.5,
*   "memory_usage_percentage": 60.2,
*   "disk_read_ops": 100,
*   "disk_write_ops": 50,
*   "network_receive_bandwidth": 1024,
*   "network_transmit_bandwidth": 2048
* }
*******************/
function homelab_fetch_netdata_data($api_url, $api_key = '', $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'cpu' => '/api/v1/data?chart=system.cpu&after=-1&options=percentage',
        'memory' => '/api/v1/data?chart=system.ram&after=-1&options=percentage',
        'disk' => '/api/v1/data?chart=system.io&after=-1',
        'network' => '/api/v1/data?chart=system.net&after=-1',
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
            $args['headers']['X-Auth-Token'] = $api_key;
        }

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'cpu') {
            $fetched_data['cpu_usage_percentage'] = $data['data'][0][1];
        } elseif ($key === 'memory') {
            $fetched_data['memory_usage_percentage'] = $data['data'][0][1];
        } elseif ($key === 'disk') {
            $fetched_data['disk_read_ops'] = $data['data'][0][1];
            $fetched_data['disk_write_ops'] = $data['data'][0][2];
        } elseif ($key === 'network') {
            $fetched_data['network_receive_bandwidth'] = $data['data'][0][1];
            $fetched_data['network_transmit_bandwidth'] = $data['data'][0][2];
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}