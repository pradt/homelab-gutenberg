<?php
/******************
 * Cloudflare Tunnels Data Collection
 * -----------------------------------
 * This function collects statistics from the Cloudflare Tunnels API for dashboard display, using an API key, tunnel ID, and account ID for authentication.
 * It fetches information about the tunnel, including its status, connected connections, and traffic data.
 *
 * Collected Data:
 * - Tunnel status (active or inactive)
 * - Number of active connections
 * - Total number of connections
 * - Ingress and egress traffic data
 *
 * Data not collected but available for extension:
 * - Detailed connection information (source IP, user agent, etc.)
 * - Historical traffic data and metrics
 * - Tunnel configuration settings
 * - Cloudflare account and user details
 * - Logs and error messages
 *
 * Authentication:
 * The function uses an API key passed in the header for authentication, along with the tunnel ID and account ID.
 *
 * Error Handling:
 * - Captures any errors encountered during the API request process.
 * - Stores error messages and timestamps for troubleshooting.
 *******************/

 function homelab_fetch_cloudflare_tunnels_data($api_url, $api_key, $tunnel_id, $account_id, $service_id) {
    $tunnel_url = rtrim($api_url, '/') . '/accounts/' . $account_id . '/tunnels/' . $tunnel_id;

    $headers = array(
        'Authorization' => 'Bearer ' . $api_key,
        'Content-Type' => 'application/json',
    );

    $tunnel_response = wp_remote_get($tunnel_url, array('headers' => $headers));

    $error_message = null;
    $error_timestamp = null;

    if (is_wp_error($tunnel_response)) {
        $error_message = "API request failed: " . $tunnel_response->get_error_message();
        $error_timestamp = current_time('mysql');
        return array();
    }

    $tunnel_data = json_decode(wp_remote_retrieve_body($tunnel_response), true);

    $tunnel_status = $tunnel_data['tunnel']['status'];
    $active_connections = $tunnel_data['tunnel']['connections']['active'];
    $total_connections = $tunnel_data['tunnel']['connections']['total'];
    $ingress_traffic = $tunnel_data['tunnel']['traffic']['ingress'];
    $egress_traffic = $tunnel_data['tunnel']['traffic']['egress'];

    $fetched_data = array(
        'tunnel_status' => $tunnel_status,
        'active_connections' => $active_connections,
        'total_connections' => $total_connections,
        'ingress_traffic' => $ingress_traffic,
        'egress_traffic' => $egress_traffic,
    );

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}