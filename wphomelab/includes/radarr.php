<?php
/**
 * Radarr
 * ------
 * TODO: Get the upcoming movies, missing movies, on demand via the Block. 
 * Only counts are being fetched to limit the amount of data. 
 */
function homelab_fetch_radarr_data($api_url, $api_key, $service_id) {
    $url = rtrim($api_url, '/') . '/api/v3/queue?apikey=' . $api_key;
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

    $wanted = count($data['records']);
    $queued = count(array_filter($data['records'], function ($item) {
        return $item['status'] === 'queued';
    }));

    $movies_url = rtrim($api_url, '/') . '/api/v3/movie?apikey=' . $api_key;
    $movies_response = wp_remote_get($movies_url);
    $movies_count = 0;

    if (is_wp_error($movies_response)) {
        $error_message = $movies_response->get_error_message();
        $error_timestamp = current_time('mysql');
    } else {
        $movies_data = json_decode(wp_remote_retrieve_body($movies_response), true);
        $movies_count = count($movies_data);
    }

    $fetched_data = array(
        'wanted' => $wanted,
        'queued' => $queued,
        'movies' => $movies_count,
    );

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}