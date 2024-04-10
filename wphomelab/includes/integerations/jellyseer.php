<?php
/**********************
* Jellyseerr Data Collection
* ----------------------
* This function collects data from Jellyseerr, a request management system for Jellyfin, for dashboard display.
* It fetches information about the requests, users, and overall system status.
*
* Collected Data:
* - Total number of requests
* - Number of requests by status (pending, approved, available)
* - Total number of users
* - Average requests per user
* - System resource utilization (CPU, memory)
*
* Data not collected but available for extension:
* - Detailed request information (title, year, requester, date)
* - User details (username, email, role)
* - Request history and trends
* - Media type and category distribution
* - Notification and email settings
* - Integration and API usage
*
* Opportunities for additional data collection:
* - Request fulfillment time and efficiency
* - User engagement and satisfaction metrics
* - Content popularity and demand analysis
* - Server performance and scalability metrics
* - Third-party service integration and automation
*
* Requirements:
* - Jellyseerr API should be accessible via the provided API URL.
* - API authentication requires an API key.
*
* Parameters:
* - $api_url: The base URL of the Jellyseerr API.
* - $api_key: The API key for authentication.
* - $service_id: The ID of the Jellyseerr service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
***********************/
function homelab_fetch_jellyseerr_data($api_url, $api_key, $service_id) {
    $api_url = rtrim($api_url, '/');

    $endpoints = array(
        'requests' => '/request',
        'users' => '/user',
        'system' => '/status',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;

        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-Api-Key' => $api_key,
            ),
        );

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'requests') {
            $fetched_data['total_requests'] = count($data);

            $status_counts = array(
                'pending' => 0,
                'approved' => 0,
                'available' => 0,
            );

            foreach ($data as $request) {
                $status = strtolower($request['status']);
                if (isset($status_counts[$status])) {
                    $status_counts[$status]++;
                }
            }

            $fetched_data['requests_pending'] = $status_counts['pending'];
            $fetched_data['requests_approved'] = $status_counts['approved'];
            $fetched_data['requests_available'] = $status_counts['available'];
        } elseif ($key === 'users') {
            $fetched_data['total_users'] = count($data);

            $total_requests = $fetched_data['total_requests'] ?? 0;
            $fetched_data['avg_requests_per_user'] = $fetched_data['total_users'] > 0 ? $total_requests / $fetched_data['total_users'] : 0;
        } elseif ($key === 'system') {
            $fetched_data['cpu_usage'] = $data['cpu'];
            $fetched_data['memory_usage'] = $data['memory'];
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}