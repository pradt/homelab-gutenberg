<?php
/******************
* Traefik Data Collection
* ----------------------
* This function collects data from Traefik, a reverse proxy and load balancer, for dashboard display.
* It fetches information about the configured routes, services, and middlewares, as well as traffic metrics.
*
* Collected Data:
* - Total number of configured routes
* - Total number of configured services
* - Total number of configured middlewares
* - Total number of requests (last 24 hours)
* - Total number of successful requests (last 24 hours)
* - Total number of server errors (last 24 hours)
* - Average response time (last 24 hours)
*
* Data not collected but available for extension:
* - Detailed route information (rule, service, middlewares, priority)
* - Detailed service information (load balancer, servers, health check)
* - Detailed middleware information (type, configuration)
* - Traffic metrics per route and service
* - Error rates and logs
* - TLS certificate information
* - Traefik configuration and settings
*
* Data Structure Example:
* {
*   "total_routes": 10,
*   "total_services": 5,
*   "total_middlewares": 3,
*   "total_requests": 1000,
*   "total_success_requests": 950,
*   "total_server_errors": 5,
*   "avg_response_time": 150
* }
*
* Requirements:
* - Traefik API should be accessible via the provided API URL.
* - API authentication (username and password) may be required depending on Traefik configuration.
*
* Parameters:
* - $api_url: The base URL of the Traefik API.
* - $username: The username for authentication (if required).
* - $password: The password for authentication (if required).
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_traefik_data($api_url, $username = '', $password = '', $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'overview' => '/api/overview',
        'metrics' => '/metrics',
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

        if (!empty($username) && !empty($password)) {
            $args['headers']['Authorization'] = 'Basic ' . base64_encode($username . ':' . $password);
        }

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'overview') {
            $fetched_data['total_routes'] = count($data['http']['routers']);
            $fetched_data['total_services'] = count($data['http']['services']);
            $fetched_data['total_middlewares'] = count($data['http']['middlewares']);
        } elseif ($key === 'metrics') {
            // Process metrics data to extract relevant information
            $metrics_data = explode("\n", $data);
            foreach ($metrics_data as $metric) {
                if (strpos($metric, 'traefik_entrypoint_requests_total{') === 0) {
                    $fetched_data['total_requests'] = intval(explode(' ', $metric)[1]);
                } elseif (strpos($metric, 'traefik_entrypoint_requests_total{code="2"}') === 0) {
                    $fetched_data['total_success_requests'] = intval(explode(' ', $metric)[1]);
                } elseif (strpos($metric, 'traefik_entrypoint_requests_total{code="5"}') === 0) {
                    $fetched_data['total_server_errors'] = intval(explode(' ', $metric)[1]);
                } elseif (strpos($metric, 'traefik_entrypoint_request_duration_seconds_sum') === 0) {
                    $duration_sum = floatval(explode(' ', $metric)[1]);
                    $fetched_data['avg_response_time'] = $fetched_data['total_requests'] > 0 ? ($duration_sum / $fetched_data['total_requests']) * 1000 : 0;
                }
            }
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}