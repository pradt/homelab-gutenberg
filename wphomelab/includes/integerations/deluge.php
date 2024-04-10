<?php
/***
 * Deluge
 * ------
 * The data points that can be displayed on the dashboard for Deluge include:
 *
 * Download rate: The current download speed.
 * Upload rate: The current upload speed.
 * Total download: The total amount of data downloaded.
 * Total upload: The total amount of data uploaded.
 * Leech count: The number of torrents currently leeching (downloading).
 * Seed count: The number of torrents currently seeding (uploading).
 * Paused count: The number of paused torrents.
 */


 function homelab_fetch_deluge_data($api_url, $api_key, $service_id) {
    $url = rtrim($api_url, '/') . '/json';
    $body = json_encode(array(
        'method' => 'web.update_ui',
        'params' => array(
            array(
                'stats',
                'torrents',
            ),
        ),
        'id' => 1,
    ));

    $response = wp_remote_post($url, array(
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
        'body' => $body,
    ));

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

    $stats = $data['result']['stats'];
    $torrents = $data['result']['torrents'];

    $download_rate = $stats['download_rate'];
    $upload_rate = $stats['upload_rate'];
    $total_download = $stats['total_download'];
    $total_upload = $stats['total_upload'];

    $leech_count = 0;
    $seed_count = 0;
    $paused_count = 0;

    foreach ($torrents as $torrent) {
        if ($torrent['state'] === 'Seeding') {
            $seed_count++;
        } elseif ($torrent['state'] === 'Downloading') {
            $leech_count++;
        } elseif ($torrent['state'] === 'Paused') {
            $paused_count++;
        }
    }

    $fetched_data = array(
        'download_rate' => $download_rate,
        'upload_rate' => $upload_rate,
        'total_download' => $total_download,
        'total_upload' => $total_upload,
        'leech_count' => $leech_count,
        'seed_count' => $seed_count,
        'paused_count' => $paused_count,
    );

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}