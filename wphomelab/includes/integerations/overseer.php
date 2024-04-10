<?php
/***
 * Overseer
 * --------
 */

 function homelab_fetch_overseerr_data($api_url, $api_key, $service_id) {
    $url = rtrim($api_url, '/') . '/api/v1/request?take=0&apikey=' . $api_key;
    $response = wp_remote_get($url);

    $error_message = null;
    $error_timestamp = null;

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        $error_timestamp = current_time('mysql');
        return array();
    }

    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        $error_message = "API request failed with status code: $response_code";
        $error_timestamp = current_time('mysql');
        return array();
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);

    $pending = $data['pageInfo']['results'];
    $processing = 0;
    $approved = 0;
    $available = 0;

    foreach ($data['results'] as $request) {
        switch ($request['status']) {
            case 1:
                $processing++;
                break;
            case 2:
                $approved++;
                break;
            case 5:
                $available++;
                break;
        }
    }

    $users_url = rtrim($api_url, '/') . '/api/v1/user?take=0&apikey=' . $api_key;
    $users_response = wp_remote_get($users_url);
    $total_users = 0;

    if (!is_wp_error($users_response)) {
        $users_data = json_decode(wp_remote_retrieve_body($users_response), true);
        $total_users = $users_data['pageInfo']['results'];
    }

    $pending_requests_url = rtrim($api_url, '/') . '/api/v1/request?take=0&filter=pending&apikey=' . $api_key;
    $pending_requests_response = wp_remote_get($pending_requests_url);
    $pending_requests_count = 0;

    if (!is_wp_error($pending_requests_response)) {
        $pending_requests_data = json_decode(wp_remote_retrieve_body($pending_requests_response), true);
        $pending_requests_count = $pending_requests_data['pageInfo']['results'];
    }

    $recently_added_url = rtrim($api_url, '/') . '/api/v1/media?take=5&sort=added&apikey=' . $api_key;
    $recently_added_response = wp_remote_get($recently_added_url);
    $recently_added_media = array();

    if (!is_wp_error($recently_added_response)) {
        $recently_added_data = json_decode(wp_remote_retrieve_body($recently_added_response), true);
        $recently_added_media = $recently_added_data['results'];
    }

    $user_stats_url = rtrim($api_url, '/') . '/api/v1/request/stats?apikey=' . $api_key;
    $user_stats_response = wp_remote_get($user_stats_url);
    $user_request_stats = array();

    if (!is_wp_error($user_stats_response)) {
        $user_stats_data = json_decode(wp_remote_retrieve_body($user_stats_response), true);
        $user_request_stats = $user_stats_data;
    }

    $fetched_data = array(
        'pending' => $pending,
        'approved' => $approved,
        'available' => $available,
        'processing' => $processing,
        'total_users' => $total_users,
        'pending_requests_count' => $pending_requests_count,
        'recently_added_media' => $recently_added_media,
        'user_request_stats' => $user_request_stats,
    );

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}