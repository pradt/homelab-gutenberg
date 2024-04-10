<?
/******************
 * ChangeDetection.io Self-Hosted Data Collection
 * ----------------------------------------------
 * This function collects statistics from the ChangeDetection.io self-hosted API for dashboard display, using an API key for authentication.
 * It fetches counts for watched URLs, triggered URLs, and the last time data was fetched.
 *
 * Collected Data:
 * - Number of watched URLs
 * - Number of triggered URLs
 * - Timestamp of the last data fetch
 *
 * Data not collected but available for extension:
 * - Detailed information about each watched URL
 * - Change history and diff data for triggered URLs
 * - Notification settings and alert configurations
 * - User and account management data
 * - System performance metrics and logs
 *
 * Authentication:
 * The function uses an API key passed in the header for authentication.
 *
 * Error Handling:
 * - Captures any errors encountered during the API request process.
 * - Stores error messages and timestamps for troubleshooting.
 *******************/

 function homelab_fetch_changedetection_data($api_url, $api_key, $service_id) {
    $watched_urls_url = rtrim($api_url, '/') . '/api/v1/urls/watched';
    $triggered_urls_url = rtrim($api_url, '/') . '/api/v1/urls/triggered';
    $last_fetch_url = rtrim($api_url, '/') . '/api/v1/fetch/last';

    $headers = array(
        'Authorization' => 'Bearer ' . $api_key,
        'Accept' => 'application/json',
    );

    $watched_urls_response = wp_remote_get($watched_urls_url, array('headers' => $headers));
    $triggered_urls_response = wp_remote_get($triggered_urls_url, array('headers' => $headers));
    $last_fetch_response = wp_remote_get($last_fetch_url, array('headers' => $headers));

    $error_message = null;
    $error_timestamp = null;

    if (is_wp_error($watched_urls_response) || is_wp_error($triggered_urls_response) || is_wp_error($last_fetch_response)) {
        $error_message = "API request failed: " . $watched_urls_response->get_error_message() . ", " . $triggered_urls_response->get_error_message() . ", " . $last_fetch_response->get_error_message();
        $error_timestamp = current_time('mysql');
        return array();
    }

    $watched_urls_data = json_decode(wp_remote_retrieve_body($watched_urls_response), true);
    $triggered_urls_data = json_decode(wp_remote_retrieve_body($triggered_urls_response), true);
    $last_fetch_data = json_decode(wp_remote_retrieve_body($last_fetch_response), true);

    $watched_urls_count = count($watched_urls_data);
    $triggered_urls_count = count($triggered_urls_data);
    $last_fetch_timestamp = $last_fetch_data['timestamp'];

    $fetched_data = array(
        'watched_urls_count' => $watched_urls_count,
        'triggered_urls_count' => $triggered_urls_count,
        'last_fetch_timestamp' => $last_fetch_timestamp,
    );

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}