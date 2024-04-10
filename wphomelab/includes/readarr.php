<?php
/***
 * TODO: Missing books count is being retrieved, a list can be implemented through the book for ondemand 
 * 
 * 
 */
function homelab_fetch_readarr_data($api_url, $api_key, $service_id) {
    $url = rtrim($api_url, '/') . '/api/v1/queue?apikey=' . $api_key;
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

    $books_url = rtrim($api_url, '/') . '/api/v1/book?apikey=' . $api_key;
    $books_response = wp_remote_get($books_url);
    $books_count = 0;

    if (!is_wp_error($books_response)) {
        $books_data = json_decode(wp_remote_retrieve_body($books_response), true);
        $books_count = count($books_data);
    }

    $upcoming_url = rtrim($api_url, '/') . '/api/v1/book/upcoming?apikey=' . $api_key;
    $upcoming_response = wp_remote_get($upcoming_url);
    $upcoming_books_count = 0;

    if (!is_wp_error($upcoming_response)) {
        $upcoming_data = json_decode(wp_remote_retrieve_body($upcoming_response), true);
        $upcoming_books_count = count($upcoming_data);
    }

    $missing_url = rtrim($api_url, '/') . '/api/v1/wanted/missing?apikey=' . $api_key;
    $missing_response = wp_remote_get($missing_url);
    $missing_books_count = 0;

    if (!is_wp_error($missing_response)) {
        $missing_data = json_decode(wp_remote_retrieve_body($missing_response), true);
        $missing_books_count = $missing_data['totalRecords'];
    }

    $authors_url = rtrim($api_url, '/') . '/api/v1/author?apikey=' . $api_key;
    $authors_response = wp_remote_get($authors_url);
    $total_authors = 0;

    if (!is_wp_error($authors_response)) {
        $authors_data = json_decode(wp_remote_retrieve_body($authors_response), true);
        $total_authors = count($authors_data);
    }

    $diskspace_url = rtrim($api_url, '/') . '/api/v1/diskspace?apikey=' . $api_key;
    $diskspace_response = wp_remote_get($diskspace_url);
    $diskspace_usage = 0;

    if (!is_wp_error($diskspace_response)) {
        $diskspace_data = json_decode(wp_remote_retrieve_body($diskspace_response), true);
        foreach ($diskspace_data as $disk) {
            $diskspace_usage += $disk['totalSpace'];
        }
    }

    $fetched_data = array(
        'wanted' => $wanted,
        'queued' => $queued,
        'books' => $books_count,
        'upcoming_books_count' => $upcoming_books_count,
        'missing_books_count' => $missing_books_count,
        'total_authors' => $total_authors,
        'diskspace_usage' => $diskspace_usage,
    );

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}