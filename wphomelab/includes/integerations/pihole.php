<?php
/******************
* Pi-hole Data Collection
* ----------------------
* This function collects data from Pi-hole, a network-level ad and tracker blocking tool, for dashboard display.
* It fetches information about the DNS queries, including total queries, blocked queries, and percentage of queries blocked.
*
* Collected Data:
* - Total number of DNS queries
* - Number of blocked queries
* - Percentage of queries blocked
* - Number of queries forwarded
* - Number of queries cached
* - Number of unique domains
* - Number of unique clients
*
* Data not collected but available for extension:
* - Top queried domains
* - Top clients
* - Top advertisers
* - Query types (A, AAAA, PTR, etc.)
* - Detailed query logs
* - Pi-hole configuration settings
* - Gravity (blocklist) information
* - DHCP leases
*
* Example of fetched_data structure:
* {
*   "dns_queries_today": 10000,
*   "ads_blocked_today": 2500,
*   "ads_percentage_today": 25,
*   "queries_forwarded": 7500,
*   "queries_cached": 2500,
*   "unique_domains": 500,
*   "unique_clients": 20
* }
*
* Requirements:
* - Pi-hole API should be accessible via the provided API URL.
* - API authentication token (API key) is required to access the Pi-hole API.
*
* Parameters:
* - $api_url: The base URL of the Pi-hole API.
* - $api_key: The API key for authentication.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_pihole_data($api_url, $api_key, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'summary' => '/admin/api.php?summary',
    );
    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-Pi-hole-Auth' => $api_key,
            ),
        );

        $response = wp_remote_get($url, $args);
        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'summary') {
            $fetched_data['dns_queries_today'] = intval($data['dns_queries_today']);
            $fetched_data['ads_blocked_today'] = intval($data['ads_blocked_today']);
            $fetched_data['ads_percentage_today'] = floatval($data['ads_percentage_today']);
            $fetched_data['queries_forwarded'] = intval($data['queries_forwarded']);
            $fetched_data['queries_cached'] = intval($data['queries_cached']);
            $fetched_data['unique_domains'] = intval($data['unique_domains']);
            $fetched_data['unique_clients'] = intval($data['unique_clients']);
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}
