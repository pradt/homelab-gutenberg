<?php
/******************
* Nginx Proxy Manager Data Collection
* ------------------------------------
* This function collects data from Nginx Proxy Manager, a reverse proxy management tool, for dashboard display.
* It fetches information about the managed proxies, including their status, response times, and SSL certificate expiration.
*
* Collected Data:
* - Total number of managed proxies
* - Number of proxies by status (online, offline)
* - Average response time across all proxies
* - Number of proxies with SSL certificates expiring within a specified timeframe
*
* Data not collected but available for extension:
* - Detailed proxy information (name, domain, target, port)
* - Proxy-specific response times and error rates
* - Proxy access and error logs
* - SSL certificate details (issuer, expiration date, key size)
* - Nginx Proxy Manager configuration and settings
*
* Example of fetched_data structure:
* {
*   "total_proxies": 10,
*   "proxies_online": 8,
*   "proxies_offline": 2,
*   "avg_response_time": 150,
*   "expiring_ssl_count": 1
* }
*
* Requirements:
* - Nginx Proxy Manager API should be accessible via the provided API URL.
* - API authentication using username and password is required.
*
* Parameters:
* - $api_url: The base URL of the Nginx Proxy Manager API.
* - $username: The username for API authentication.
* - $password: The password for API authentication.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_nginx_proxy_manager_data($api_url, $username, $password, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'proxies' => '/api/nginx/proxy-hosts',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    // Authenticate and get the access token
    $auth_url = $api_url . '/api/tokens';
    $auth_args = array(
        'body' => json_encode(array(
            'identity' => $username,
            'secret' => $password,
        )),
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
    );
    $auth_response = wp_remote_post($auth_url, $auth_args);
    if (is_wp_error($auth_response)) {
        $error_message = "Authentication failed: " . $auth_response->get_error_message();
        $error_timestamp = current_time('mysql');
        homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
        return $fetched_data;
    }
    $auth_data = json_decode(wp_remote_retrieve_body($auth_response), true);
    $access_token = $auth_data['token'];

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $access_token,
            ),
        );

        $response = wp_remote_get($url, $args);
        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'proxies') {
            $fetched_data['total_proxies'] = count($data);
            $status_counts = array(
                'online' => 0,
                'offline' => 0,
            );
            $total_response_time = 0;
            $expiring_ssl_count = 0;

            foreach ($data as $proxy) {
                $status = strtolower($proxy['online'] ? 'online' : 'offline');
                $status_counts[$status]++;
                $total_response_time += $proxy['response_time'];

                // Check if SSL certificate is expiring within the next 30 days
                if ($proxy['ssl_expires'] && strtotime($proxy['ssl_expires']) < strtotime('+30 days')) {
                    $expiring_ssl_count++;
                }
            }

            $fetched_data['proxies_online'] = $status_counts['online'];
            $fetched_data['proxies_offline'] = $status_counts['offline'];
            $fetched_data['avg_response_time'] = $fetched_data['total_proxies'] > 0 ? $total_response_time / $fetched_data['total_proxies'] : 0;
            $fetched_data['expiring_ssl_count'] = $expiring_ssl_count;
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}