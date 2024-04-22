<?php
/******************
 * FRITZ!Box Data Collection
 * --------------------------
 * This function collects data from the FRITZ!Box API for dashboard display, supporting authentication using the FRITZ!Box login credentials.
 * It fetches information about the device status, connected devices, and various statistics.
 *
 * Collected Data:
 * - Device model and firmware version
 * - Uptime of the device
 * - Number of connected devices
 * - Upstream and downstream speeds
 * - Total data transferred (sent and received)
 * - WAN connection status
 *
 * Data not collected but available for extension:
 * - Detailed information about connected devices (IP address, MAC address, hostname, etc.)
 * - WLAN configuration and status
 * - DECT device information and status
 * - Phone call logs and statistics
 * - Smart home device status and control
 *
 * Authentication:
 * This function requires authentication using the FRITZ!Box login credentials (username and password).
 * The username and password should be provided as parameters to the function.
 *
 * Parameters:
 * - $api_url: The base URL of the FRITZ!Box API.
 * - $username: The username for authentication (default: "admin").
 * - $password: The password for authentication.
 * - $service_id: The ID of the service being monitored.
 *
 * Error Handling:
 * - Captures any errors encountered during the API request process.
 * - Stores error messages and timestamps for troubleshooting.
 *******************/

 function homelab_fetch_fritzbox_data($api_url, $username = 'admin', $password, $service_id) {
    $api_url = rtrim($api_url, '/');

    $endpoints = array(
        'deviceinfo' => '/data.lua',
        'connectivity' => '/internet/inetstat_monitor.lua',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    // Authentication
    $auth_string = base64_encode($username . ':' . $password);

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $args = array(
            'headers' => array(
                'Authorization' => 'Basic ' . $auth_string,
                'Content-Type' => 'application/json',
            ),
        );
        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'deviceinfo') {
            $fetched_data['device_model'] = $data['model'];
            $fetched_data['firmware_version'] = $data['fw_version'];
            $fetched_data['uptime'] = $data['uptime'];
            $fetched_data['connected_devices'] = $data['active_hosts'];
        } elseif ($key === 'connectivity') {
            $fetched_data['upstream_speed'] = $data['upstream'];
            $fetched_data['downstream_speed'] = $data['downstream'];
            $fetched_data['data_sent'] = $data['bytes_sent'];
            $fetched_data['data_received'] = $data['bytes_received'];
            $fetched_data['wan_connection_status'] = $data['connection_status'];
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}