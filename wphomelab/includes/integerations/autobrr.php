<?php
/******************
 * Authentik Data Collection
 * -------------------------------
 * This function collects statistics from the Authentik API for dashboard display, using an API Key for authentication.
 * It fetches user counts, login counts, and failed login counts for the last 24 hours.
 *
 * Collected Data:
 * - Total number of users
 * - Number of logins in the last 24 hours
 * - Number of failed logins in the last 24 hours
 *
 * Data not collected but available for extension:
 * - User activity and session details
 * - Application and provider specific login statistics
 * - Detailed user profile information
 * - Authentication flow and policy data
 * - Audit logs and event timestamps
 *
 * Authentication:
 * The function uses an API Key passed in the header for authentication.
 *
 * Error Handling:
 * - Captures any errors encountered during the API request process.
 * - Stores error messages and timestamps for troubleshooting.
 *******************/
function homelab_fetch_authentik_data($api_url, $api_key, $service_id) {
    $users_url = rtrim($api_url, '/') . '/api/v3/core/users/';
    $logins_url = rtrim($api_url, '/') . '/api/v3/core/events/logins/?ordering=-timestamp&limit=1';
    $failed_logins_url = rtrim($api_url, '/') . '/api/v3/core/events/failed_logins/?ordering=-timestamp&limit=1';

    $headers = array(
        'Authorization' => 'Bearer ' . $api_key,
        'Accept' => 'application/json',
    );

    $users_response = wp_remote_get($users_url, array('headers' => $headers));
    $logins_response = wp_remote_get($logins_url, array('headers' => $headers));
    $failed_logins_response = wp_remote_get($failed_logins_url, array('headers' => $headers));

    $error_message = null;
    $error_timestamp = null;

    if (is_wp_error($users_response) || is_wp_error($logins_response) || is_wp_error($failed_logins_response)) {
        $error_message = "API request failed: " . $users_response->get_error_message() . ", " . $logins_response->get_error_message() . ", " . $failed_logins_response->get_error_message();
        $error_timestamp = current_time('mysql');
        return array();
    }

    $users_data = json_decode(wp_remote_retrieve_body($users_response), true);
    $logins_data = json_decode(wp_remote_retrieve_body($logins_response), true);
    $failed_logins_data = json_decode(wp_remote_retrieve_body($failed_logins_response), true);

    $total_users = $users_data['count'];
    $logins_last_24h = $logins_data['count'];
    $failed_logins_last_24h = $failed_logins_data['count'];

    $fetched_data = array(
        'total_users' => $total_users,
        'logins_last_24h' => $logins_last_24h,
        'failed_logins_last_24h' => $failed_logins_last_24h,
    );

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}

/******************
 * AutoBrr Data Collection
 * -------------------------------
 * This function collects statistics from the AutoBrr API for dashboard display, using an API Key for authentication.
 * It fetches counts for approved pushes, rejected pushes, filters, and indexers.
 *
 * Collected Data:
 * - Number of approved pushes
 * - Number of rejected pushes
 * - Number of filters
 * - Number of indexers
 *
 * Data not collected but available for extension:
 * - Detailed push history and status
 * - Filter and indexer configurations
 * - User and application settings
 * - Performance metrics and logs
 * - Storage and bandwidth usage statistics
 *
 * Authentication:
 * The function uses an API Key passed in the header for authentication.
 *
 * Error Handling:
 * - Captures any errors encountered during the API request process.
 * - Stores error messages and timestamps for troubleshooting.
 *******************/

 function homelab_fetch_autobrr_data($api_url, $api_key, $service_id) {
    $stats_url = rtrim($api_url, '/') . '/api/v1/stats';

    $headers = array(
        'Authorization' => 'Bearer ' . $api_key,
        'Accept' => 'application/json',
    );

    $response = wp_remote_get($stats_url, array('headers' => $headers));

    $error_message = null;
    $error_timestamp = null;

    if (is_wp_error($response)) {
        $error_message = "API request failed: " . $response->get_error_message();
        $error_timestamp = current_time('mysql');
        return array();
    }

    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        $error_message = "API request failed with status code: $response_code";
        $error_timestamp = current_time('mysql');
        return array();
    }

    $stats_data = json_decode(wp_remote_retrieve_body($response), true);

    $approved_pushes = $stats_data['approvedPushes'];
    $rejected_pushes = $stats_data['rejectedPushes'];
    $filters_count = $stats_data['filters'];
    $indexers_count = $stats_data['indexers'];

    $fetched_data = array(
        'approved_pushes' => $approved_pushes,
        'rejected_pushes' => $rejected_pushes,
        'filters_count' => $filters_count,
        'indexers_count' => $indexers_count,
    );

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}