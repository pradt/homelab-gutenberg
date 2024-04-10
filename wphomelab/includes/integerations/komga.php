<?php
/**********************
* Komga Data Collection
* ----------------------
* This function collects data from Komga, a self-hosted comics and manga server, for dashboard display.
* It fetches information about the libraries, series, users, and overall system status.
*
* Collected Data:
* - Total number of libraries
* - Number of series per library
* - Total number of users
* - Average series per user
* - System storage utilization
*
* Data not collected but available for extension:
* - Detailed library information (name, description, type)
* - Series details (title, author, genre, release year)
* - User details (username, email, role, last active)
* - Reading progress and history
* - Bookmark and collection management
* - Plugin and extension data
*
* Opportunities for additional data collection:
* - Library growth and activity trends
* - Series popularity and read count analytics
* - User engagement and retention metrics
* - Content discovery and recommendation insights
* - Server performance and scalability metrics
*
* Requirements:
* - Komga API should be accessible via the provided API URL.
* - API authentication requires a username and password.
*
* Parameters:
* - $api_url: The base URL of the Komga API.
* - $username: The username for authentication.
* - $password: The password for authentication.
* - $service_id: The ID of the Komga service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
***********************/
function homelab_fetch_komga_data($api_url, $username, $password, $service_id) {
    $api_url = rtrim($api_url, '/');

    $endpoints = array(
        'libraries' => '/api/v1/libraries',
        'series' => '/api/v1/series',
        'users' => '/api/v1/users',
        'system' => '/api/v1/system/info',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;

        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($username . ':' . $password),
            ),
        );

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'libraries') {
            $fetched_data['total_libraries'] = count($data['content']);

            $series_counts = array();

            foreach ($data['content'] as $library) {
                $library_id = $library['id'];
                $series_counts[$library_id] = $library['seriesCount'];
            }

            $fetched_data['series_per_library'] = $series_counts;
        } elseif ($key === 'series') {
            $fetched_data['total_series'] = $data['numberOfElements'];
        } elseif ($key === 'users') {
            $fetched_data['total_users'] = count($data['content']);

            $total_series = $fetched_data['total_series'] ?? 0;
            $fetched_data['avg_series_per_user'] = $fetched_data['total_users'] > 0 ? $total_series / $fetched_data['total_users'] : 0;
        } elseif ($key === 'system') {
            $fetched_data['storage_usage'] = $data['storageUsage'];
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}