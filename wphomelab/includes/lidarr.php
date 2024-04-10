<?php
/***
 * Lidarr
 * ------
 * TODO: Upcoming albums, Missing albums are only counts, Implement the list through the block so that it is fetched on demand
 * to limit the amount of data.
 * Users can add the block of they want this to be displayed.
 * 
 */
function homelab_fetch_lidarr_data($api_url, $api_key, $service_id) {
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

    $artists_url = rtrim($api_url, '/') . '/api/v1/artist?apikey=' . $api_key;
    $artists_response = wp_remote_get($artists_url);
    $artists_count = 0;

    if (!is_wp_error($artists_response)) {
        $artists_data = json_decode(wp_remote_retrieve_body($artists_response), true);
        $artists_count = count($artists_data);
    }

    $upcoming_url = rtrim($api_url, '/') . '/api/v1/album/upcoming?start=0&end=0&includeAllArtists=true&apikey=' . $api_key;
    $upcoming_response = wp_remote_get($upcoming_url);
    $upcoming_albums_count = 0;

    if (!is_wp_error($upcoming_response)) {
        $upcoming_data = json_decode(wp_remote_retrieve_body($upcoming_response), true);
        $upcoming_albums_count = count($upcoming_data);
    }

    $missing_url = rtrim($api_url, '/') . '/api/v1/wanted/missing?apikey=' . $api_key;
    $missing_response = wp_remote_get($missing_url);
    $missing_albums_count = 0;

    if (!is_wp_error($missing_response)) {
        $missing_data = json_decode(wp_remote_retrieve_body($missing_response), true);
        $missing_albums_count = $missing_data['totalRecords'];
    }

    $tracks_url = rtrim($api_url, '/') . '/api/v1/track?apikey=' . $api_key;
    $tracks_response = wp_remote_get($tracks_url);
    $total_tracks = 0;

    if (!is_wp_error($tracks_response)) {
        $tracks_data = json_decode(wp_remote_retrieve_body($tracks_response), true);
        $total_tracks = count($tracks_data);
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
        'artists' => $artists_count,
        'upcoming_albums_count' => $upcoming_albums_count,
        'missing_albums_count' => $missing_albums_count,
        'total_tracks' => $total_tracks,
        'diskspace_usage' => $diskspace_usage,
    );

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}