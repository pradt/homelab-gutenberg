<?php
/******************
* Prometheus Data Collection
* ---------------------------
* This function collects data from Prometheus, a monitoring and alerting system, for dashboard display.
* It fetches metrics and statistics from Prometheus to provide an overview of the monitored services and resources.
*
* Collected Data:
* - Total number of monitored targets
* - Number of targets by state (up, down)
* - CPU usage percentage across all targets
* - Memory usage percentage across all targets
* - Disk usage percentage across all targets
*
* Data Structure:
* {
*   "total_targets": 10,
*   "targets_up": 8,
*   "targets_down": 2,
*   "avg_cpu_usage": 65.5,
*   "avg_memory_usage": 75.2,
*   "avg_disk_usage": 60.8
* }
*
* Data not collected but available for extension:
* - Detailed target information (hostname, IP address, port, labels)
* - Service-specific metrics (request rate, error rate, latency)
* - Network-related metrics (bandwidth usage, packet loss)
* - Custom application metrics exposed by services
* - Alerting rules and their status
* - Historical data and time series for various metrics
*
* Opportunities for additional data:
* - Anomaly detection and forecasting based on historical data
* - Correlation analysis between different metrics and services
* - Capacity planning and resource optimization insights
* - Integration with incident management and ticketing systems
* - Visualization of metrics using charts and graphs
*
* Requirements:
* - Prometheus server should be accessible via the provided API URL.
* - API authentication (if configured) should be handled using the provided authentication mechanism.
*
* Parameters:
* - $api_url: The base URL of the Prometheus API.
* - $auth_token: The authentication token for accessing the Prometheus API (if required).
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_prometheus_data($api_url, $auth_token = '', $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'targets' => '/api/v1/targets',
        'cpu_usage' => '/api/v1/query?query=100%20-%20(avg%20by%20(instance)%20(rate(node_cpu_seconds_total{mode="idle"}[5m]))%20*%20100)',
        'memory_usage' => '/api/v1/query?query=(node_memory_MemTotal_bytes%20-%20node_memory_MemFree_bytes)%20/%20node_memory_MemTotal_bytes%20*%20100',
        'disk_usage' => '/api/v1/query?query=(node_filesystem_size_bytes%7Bmountpoint%3D%22/%22%7D%20-%20node_filesystem_free_bytes%7Bmountpoint%3D%22/%22%7D)%20/%20node_filesystem_size_bytes%7Bmountpoint%3D%22/%22%7D%20*%20100',
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

        if (!empty($auth_token)) {
            $args['headers']['Authorization'] = 'Bearer ' . $auth_token;
        }

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'targets') {
            $fetched_data['total_targets'] = count($data['data']['activeTargets']);
            $fetched_data['targets_up'] = 0;
            $fetched_data['targets_down'] = 0;

            foreach ($data['data']['activeTargets'] as $target) {
                if ($target['health'] === 'up') {
                    $fetched_data['targets_up']++;
                } else {
                    $fetched_data['targets_down']++;
                }
            }
        } elseif ($key === 'cpu_usage' || $key === 'memory_usage' || $key === 'disk_usage') {
            $metric_name = 'avg_' . str_replace('_usage', '', $key);
            $fetched_data[$metric_name] = $data['data']['result'][0]['value'][1];
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}