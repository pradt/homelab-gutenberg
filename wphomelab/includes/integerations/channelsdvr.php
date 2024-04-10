<?php

/******************
 * Channels DVR Server Data Collection
 * ------------------------------------
 * This function collects statistics from the Channels DVR server API for dashboard display.
 * It fetches counts for active recordings, upcoming recordings, and total recordings.
 *
 * Collected Data:
 * - Number of active recordings
 * - Number of upcoming recordings
 * - Total number of recordings
 *
 * Data not collected but available for extension:
 * - Detailed information about each recording (title, channel, duration, etc.)
 * - Live TV guide data and channel information
 * - DVR settings and configuration
 * - Storage usage and disk space metrics
 * - User and device management data
 *
 * Authentication:
 * The Channels DVR server API does not require authentication for basic statistics.
 *
 * Error Handling:
 * - Captures any errors encountered during the API request process.
 * - Stores error messages and timestamps for troubleshooting.
 *******************/

 function homelab_fetch_channels_dvr_data($api_url, $service_id) {
    $active_recordings_url = rtrim($api_url, '/') . '/api/dvr/activeRecordings';
    $upcoming_recordings_url = rtrim($api_url, '/') . '/api/dvr/upcomingRecordings';
    $recordings_url = rtrim($api_url, '/') . '/api/dvr/recordings';

    $active_recordings_response = wp_remote_get($active_recordings_url);
    $upcoming_recordings_response = wp_remote_get($upcoming_recordings_url);
    $recordings_response = wp_remote_get($recordings_url);

    $error_message = null;
    $error_timestamp = null;

    if (is_wp_error($active_recordings_response) || is_wp_error($upcoming_recordings_response) || is_wp_error($recordings_response)) {
        $error_message = "API request failed: " . $active_recordings_response->get_error_message() . ", " . $upcoming_recordings_response->get_error_message() . ", " . $recordings_response->get_error_message();
        $error_timestamp = current_time('mysql');
        return array();
    }

    $active_recordings_data = json_decode(wp_remote_retrieve_body($active_recordings_response), true);
    $upcoming_recordings_data = json_decode(wp_remote_retrieve_body($upcoming_recordings_response), true);
    $recordings_data = json_decode(wp_remote_retrieve_body($recordings_response), true);

    $active_recordings_count = count($active_recordings_data);
    $upcoming_recordings_count = count($upcoming_recordings_data);
    $total_recordings_count = count($recordings_data);

    $fetched_data = array(
        'active_recordings_count' => $active_recordings_count,
        'upcoming_recordings_count' => $upcoming_recordings_count,
        'total_recordings_count' => $total_recordings_count,
    );

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}