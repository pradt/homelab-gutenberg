<?php
/******************
 * Unifi Controller Data Collection
 * --------------------------------
 * This function collects data from the Unifi Controller, a network management system, for dashboard display.
 * It fetches information about the managed devices, clients, and network statistics.
 *
 * Collected Data:
 * - Total number of managed devices
 * - Number of devices by type (access points, switches, gateways)
 * - Total number of connected clients
 * - Number of clients by connection type (wired, wireless)
 * - Total network traffic (upload and download)
 *
 * Data not collected but available for extension:
 * - Detailed device information (name, model, IP address, firmware version)
 * - Detailed client information (name, IP address, MAC address, operating system)
 * - Device and client-specific traffic statistics
 * - Network health and performance metrics (latency, signal strength, channel utilization)
 * - WLAN configuration and settings
 * - Security and access control settings
 * - Event logs and alerts
 *
 * Requirements:
 * - Unifi Controller API should be accessible via the provided API URL.
 * - API authentication requires a valid username and password.
 *
 * Parameters:
 * - $api_url: The base URL of the Unifi Controller API.
 * - $username: The username for authentication.
 * - $password: The password for authentication.
 * - $service_id: The ID of the service being monitored.
 *
 * Error Handling:
 * - Captures any errors encountered during the API request process.
 * - Stores error messages and timestamps for troubleshooting.
 *
 * Example of fetched_data structure:
 * {
 *   "total_devices": 10,
 *   "devices_access_points": 5,
 *   "devices_switches": 3,
 *   "devices_gateways": 2,
 *   "total_clients": 50,
 *   "clients_wired": 20,
 *   "clients_wireless": 30,
 *   "total_traffic_upload": 1000,
 *   "total_traffic_download": 5000
 * }
 *******************/
function homelab_fetch_unifi_data($api_url, $username, $password, $service_id)
{
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'devices' => '/proxy/network/api/s/default/stat/device',
        'clients' => '/proxy/network/api/s/default/stat/sta',
        'networks' => '/proxy/network/api/s/default/rest/networkconf',
        'health' => '/proxy/network/api/s/default/stat/health'
    );

    $fetched_data = array(
        'raw_responses' => array(),
    );
    $error_message = null;
    $error_timestamp = null;

    // Make an initial request to the Unifi Controller API
    $initial_response = wp_remote_get($api_url, array(
        'sslverify' => false,
    ));

    $csrf_token = null;
    if (!is_wp_error($initial_response)) {
        $response_headers = wp_remote_retrieve_headers($initial_response);
        if (isset($response_headers['X-CSRF-TOKEN'])) {
            $csrf_token = $response_headers['X-CSRF-TOKEN'];
        }
    }

    // Authenticate and get the session cookie
    $auth_url = $api_url . '/api/auth/login';
    $auth_data = array(
        'username' => $username,
        'password' => $password,
        'token' => "",
    );
    $auth_response = wp_remote_post($auth_url, array(
        'headers' => array('Content-Type' => 'application/json'),
        'body' => json_encode($auth_data),
        'sslverify' => false,
    ));

    if (is_wp_error($auth_response)) {
        $error_message = "Authentication failed: " . $auth_response->get_error_message();
        $error_timestamp = current_time('mysql');
        homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
        return $fetched_data;
    }

    $auth_body = wp_remote_retrieve_body($auth_response);
    $fetched_data['raw_responses']['auth'] = $auth_body;

    $auth_data = json_decode($auth_body, true);

    if (isset($auth_data['error'])) {
        $error_message = "Authentication failed: " . json_encode($auth_data['error']);
        $error_timestamp = current_time('mysql');
        homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
        return $fetched_data;
    }

    $session_cookie = null;
    $auth_headers = wp_remote_retrieve_headers($auth_response);
    if (isset($auth_headers['Set-Cookie'])) {
        $cookies = explode(';', $auth_headers['Set-Cookie']);
        foreach ($cookies as $cookie) {
            if (strpos($cookie, 'TOKEN=') === 0) {
                $session_cookie = trim($cookie);
                break;
            }
        }
    }

    if (!$session_cookie) {
        $error_message = "Authentication response does not contain the expected session cookie";
        $error_timestamp = current_time('mysql');
        homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
        return $fetched_data;
    }

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Cookie' => $session_cookie,
                'X-Csrf-Token' => $csrf_token,
            ),
            'sslverify' => false,
        );

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $response_body = wp_remote_retrieve_body($response);
        error_log("API response for endpoint '{$key}': " . $response_body);

        // Enable debug logging
        //error_reporting(E_ALL);
        //ini_set('display_errors', 1);

        // Log the API request details
        //error_log("API request: " . $url);
        //error_log("Request headers: " . json_encode($args['headers']));

        // Log the API response
        //error_log("API response: " . $response_body);

        $fetched_data['raw_responses'][$key] = $response_body;

        $data = json_decode($response_body, true);

        if (isset($data['error'])) {
            $error_message = "API request failed for endpoint '{$key}': " . $data['error']['message'];
            $error_timestamp = current_time('mysql');
            continue;
        }

        if ($key === 'devices' && isset($data['data'])) {
            $fetched_data['total_devices'] = count($data['data']);

            $type_counts = array(
                'access_points' => 0,
                'switches' => 0,
                'gateways' => 0,
            );

            foreach ($data['data'] as $device) {
                $type = strtolower($device['type']);
                if (isset($type_counts[$type])) {
                    $type_counts[$type]++;
                }
            }

            $fetched_data['devices_access_points'] = $type_counts['access_points'];
            $fetched_data['devices_switches'] = $type_counts['switches'];
            $fetched_data['devices_gateways'] = $type_counts['gateways'];
        }

        if ($key === 'clients' && isset($data['data'])) {
            $fetched_data['total_clients'] = count($data['data']);

            $connection_counts = array(
                'wired' => 0,
                'wireless' => 0,
            );

            foreach ($data['data'] as $client) {
                $is_wired = $client['is_wired'];
                if ($is_wired) {
                    $connection_counts['wired']++;
                } else {
                    $connection_counts['wireless']++;
                }
            }

            $fetched_data['clients_wired'] = $connection_counts['wired'];
            $fetched_data['clients_wireless'] = $connection_counts['wireless'];
        }

        if ($key === 'networks' && isset($data['data'])) {
            $total_traffic_upload = 0;
            $total_traffic_download = 0;

            foreach ($data['data'] as $network) {
                $total_traffic_upload += $network['tx_bytes'];
                $total_traffic_download += $network['rx_bytes'];
            }

            $fetched_data['total_traffic_upload'] = $total_traffic_upload;
            $fetched_data['total_traffic_download'] = $total_traffic_download;
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}