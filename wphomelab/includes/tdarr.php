<?php
/***
 * Tdarr
 * -----
 * TODO: 
 * 
 */
function homelab_fetch_tdarr_data($api_url, $api_key, $service_id) {
    $url = rtrim($api_url, '/') . '/api/v2/status?apikey=' . $api_key;
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

    $queue = $data['queue'];
    $processed = $data['processed'];
    $errored = $data['errored'];
    $saved = $data['saved'];

    $libraries_url = rtrim($api_url, '/') . '/api/v2/libraries?apikey=' . $api_key;
    $libraries_response = wp_remote_get($libraries_url);
    $total_libraries = 0;

    if (!is_wp_error($libraries_response)) {
        $libraries_data = json_decode(wp_remote_retrieve_body($libraries_response), true);
        $total_libraries = count($libraries_data);
    }

    $nodes_url = rtrim($api_url, '/') . '/api/v2/nodes?apikey=' . $api_key;
    $nodes_response = wp_remote_get($nodes_url);
    $node_statuses = array();

    if (!is_wp_error($nodes_response)) {
        $nodes_data = json_decode(wp_remote_retrieve_body($nodes_response), true);
        foreach ($nodes_data as $node) {
            $node_statuses[] = array(
                'name' => $node['name'],
                'status' => $node['status'],
            );
        }
    }

    $transcode_url = rtrim($api_url, '/') . '/api/v2/transcode-cache?apikey=' . $api_key;
    $transcode_response = wp_remote_get($transcode_url);
    $current_transcode = null;

    if (!is_wp_error($transcode_response)) {
        $transcode_data = json_decode(wp_remote_retrieve_body($transcode_response), true);
        if (!empty($transcode_data)) {
            $current_transcode = $transcode_data[0]['file'];
        }
    }

    $history_url = rtrim($api_url, '/') . '/api/v2/history?take=5&apikey=' . $api_key;
    $history_response = wp_remote_get($history_url);
    $transcode_history = array();

    if (!is_wp_error($history_response)) {
        $history_data = json_decode(wp_remote_retrieve_body($history_response), true);
        $transcode_history = $history_data;
    }

    $fetched_data = array(
        'queue' => $queue,
        'processed' => $processed,
        'errored' => $errored,
        'saved' => $saved,
        'total_libraries' => $total_libraries,
        'node_statuses' => $node_statuses,
        'current_transcode' => $current_transcode,
        'transcode_history' => $transcode_history,
    );

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}