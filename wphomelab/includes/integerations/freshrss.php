<?php
/******************
 * FreshRSS Data Collection
 * -------------------------
 * This function collects data from the FreshRSS API for dashboard display, supporting authentication using a username and password.
 * It fetches information about the user's feeds, categories, and articles.
 *
 * Collected Data:
 * - Total number of feeds
 * - Number of categories
 * - Number of unread articles
 * - Number of read articles
 * - Number of starred articles
 *
 * Data not collected but available for extension:
 * - Detailed feed information (title, URL, description, etc.)
 * - Detailed category information (name, parent category, etc.)
 * - Detailed article information (title, URL, content, author, etc.)
 * - User preferences and settings
 * - Feed synchronization status and logs
 *
 * Authentication:
 * This function requires authentication using a username and password.
 * The username and password should be provided as parameters to the function.
 *
 * Parameters:
 * - $api_url: The base URL of the FreshRSS API.
 * - $username: The username for authentication.
 * - $password: The password for authentication.
 * - $service_id: The ID of the service being monitored.
 *
 * Error Handling:
 * - Captures any errors encountered during the API request process.
 * - Stores error messages and timestamps for troubleshooting.
 *******************/

 function homelab_fetch_freshrss_data($api_url, $username, $password, $service_id) {
    $api_url = rtrim($api_url, '/');

    $endpoints = array(
        'feeds' => '/api/feeds.php',
        'categories' => '/api/categories.php',
        'entries' => '/api/entries.php',
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

        if ($key === 'feeds') {
            $fetched_data['total_feeds'] = count($data);
        } elseif ($key === 'categories') {
            $fetched_data['total_categories'] = count($data);
        } elseif ($key === 'entries') {
            $unread_articles = 0;
            $read_articles = 0;
            $starred_articles = 0;

            foreach ($data['entries'] as $entry) {
                if ($entry['is_read']) {
                    $read_articles++;
                } else {
                    $unread_articles++;
                }
                if ($entry['is_favorite']) {
                    $starred_articles++;
                }
            }

            $fetched_data['unread_articles'] = $unread_articles;
            $fetched_data['read_articles'] = $read_articles;
            $fetched_data['starred_articles'] = $starred_articles;
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}