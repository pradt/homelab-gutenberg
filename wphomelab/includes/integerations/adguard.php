<?php
/**********
 * AdGuard Home Data Collection
 * ----------------------------
 * This function collects data from the AdGuard Home API for dashboard display, incorporating authentication with a username and password.
 * It fetches statistics on the total number of DNS queries, the number of blocked queries, the percentage of queries blocked, the number
 * of active DNS filters, and the number of URL rewrite rules.
 * 
 * Data not collected but available for extension:
 * - Detailed info on blocked services
 * - Top queried domains
 * - Top blocked domains
 * - Information on clients making the most queries
 * - System status and resource usage (CPU, RAM, etc.)
 * 
 * Authentication:
 * The function uses basic HTTP authentication to access the AdGuard Home API, requiring a username and password.
 ***********/

 function homelab_fetch_adguard_home_data($api_url, $username, $password, $service_id) {
    // Encode the username and password for basic auth
    $auth = base64_encode("$username:$password");

    // Construct the URL for the statistics endpoint
    $stats_url = rtrim($api_url, '/') . '/control/stats';

    // Make the request to the AdGuard Home API with the Authorization header
    $response = wp_remote_get($stats_url, array(
        'headers' => array(
            'Authorization' => 'Basic ' . $auth,
        ),
    ));

    // Initialize error handling variables
    $error_message = null;
    $error_timestamp = null;

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        $error_timestamp = current_time('mysql');
    } else {
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            $error_message = "API request failed with status code: $response_code";
            $error_timestamp = current_time('mysql');
        }
    }

    // Initialize the array to hold the fetched data
    $adguard_data = array(
        'total_queries' => 0,
        'blocked_queries' => 0,
        'percentage_blocked' => 0,
        'active_dns_filters' => 0,
        'rewrite_rules' => 0,
    );

    if (empty($error_message)) {
        // Decode the response body into an associative array
        $stats_data = json_decode(wp_remote_retrieve_body($response), true);

        // Extract required data
        $total_queries = $stats_data['num_dns_queries'];
        $blocked_queries = $stats_data['num_blocked_filtering'];
        $percentage_blocked = round(($blocked_queries / $total_queries) * 100, 2);

        // Assuming another endpoint for filter status: /control/filtering/status
        $filter_status_url = rtrim($api_url, '/') . '/control/filtering/status';
        $filter_response = wp_remote_get($filter_status_url, array(
            'headers' => array(
                'Authorization' => 'Basic ' . $auth,
            ),
        ));

        if (!is_wp_error($filter_response) && wp_remote_retrieve_response_code($filter_response) === 200) {
            $filter_data = json_decode(wp_remote_retrieve_body($filter_response), true);
            $active_dns_filters = count($filter_data['filters']);
            $rewrite_rules = $filter_data['num_rewrites'];
        }

        // Prepare the data for saving or displaying
        $adguard_data = array(
            'total_queries' => $total_queries,
            'blocked_queries' => $blocked_queries,
            'percentage_blocked' => $percentage_blocked,
            'active_dns_filters' => $active_dns_filters,
            'rewrite_rules' => $rewrite_rules,
        );
    }

    // Save the fetched data along with any error messages or timestamps
    homelab_save_service_data($service_id, $adguard_data, $error_message, $error_timestamp);

    return $adguard_data;
}