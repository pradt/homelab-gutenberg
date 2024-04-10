<?php
/******************
* Speedtest Tracker Data Collection
* ----------------------------------
* This function collects data from Speedtest Tracker, a tool for monitoring internet speed and performance.
* It fetches information about the speedtest results, including download speed, upload speed, ping, and test timestamps.
*
* Collected Data:
* - Latest speedtest result (download speed, upload speed, ping)
* - Average download speed over a specified period
* - Average upload speed over a specified period
* - Average ping over a specified period
* - Number of tests performed within a specified period
*
* Data not collected but available for extension:
* - Detailed speedtest result history
* - ISP information (name, IP address)
* - Server information (name, location, distance)
* - Network configuration details
* - Latency and jitter measurements
* - Packet loss percentage
* - Historical performance trends and graphs
*
* Opportunities for additional data:
* - Comparison of speedtest results with advertised speeds from ISP
* - Analysis of network stability and consistency
* - Identification of peak usage times and network congestion
* - Integration with other network monitoring tools for comprehensive insights
*
* Example of fetched_data structure:
* {
*   "latest_test": {
*     "download_speed": 95.8,
*     "upload_speed": 12.3,
*     "ping": 24,
*     "timestamp": "2023-06-10T15:30:00Z"
*   },
*   "avg_download_speed": 92.5,
*   "avg_upload_speed": 11.8,
*   "avg_ping": 26,
*   "total_tests": 100
* }
*
* Requirements:
* - Speedtest Tracker API should be accessible via the provided API URL.
* - API authentication token (API key) may be required depending on Speedtest Tracker configuration.
*
* Parameters:
* - $api_url: The base URL of the Speedtest Tracker API.
* - $api_key: The API key for authentication (if required).
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_speedtest_data($api_url, $api_key = '', $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'latest' => '/api/v1/speedtest/latest',
        'average' => '/api/v1/speedtest/average',
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
            $args['headers']['Authorization'] = 'Bearer ' . $api_key;
        }
        $response = wp_remote_get($url, $args);
        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }
        $data = json_decode(wp_remote_retrieve_body($response), true);
        if ($key === 'latest') {
            $fetched_data['latest_test'] = array(
                'download_speed' => $data['download'],
                'upload_speed' => $data['upload'],
                'ping' => $data['ping'],
                'timestamp' => $data['timestamp'],
            );
        } elseif ($key === 'average') {
            $fetched_data['avg_download_speed'] = $data['download'];
            $fetched_data['avg_upload_speed'] = $data['upload'];
            $fetched_data['avg_ping'] = $data['ping'];
            $fetched_data['total_tests'] = $data['total_tests'];
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}