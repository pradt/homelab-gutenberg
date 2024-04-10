<?php
/***
 * Prowlarr
 */
function homelab_fetch_prowlarr_data($api_url, $api_key, $service_id) {
    $url = rtrim($api_url, '/') . '/api/v1/indexerstats?apikey=' . $api_key;
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

    $grabs_count = $data['numberOfGrabs'];
    $queries_count = $data['numberOfQueries'];
    $fail_grabs_count = $data['numberOfFailedGrabs'];
    $fail_queries_count = $data['numberOfFailedQueries'];

    $indexers_url = rtrim($api_url, '/') . '/api/v1/indexer?apikey=' . $api_key;
    $indexers_response = wp_remote_get($indexers_url);
    $indexer_statuses = array();
    $total_indexers = 0;

    if (!is_wp_error($indexers_response)) {
        $indexers_data = json_decode(wp_remote_retrieve_body($indexers_response), true);
        foreach ($indexers_data as $indexer) {
            $indexer_statuses[] = array(
                'name' => $indexer['name'],
                'enabled' => $indexer['enable'],
                'status' => $indexer['status'],
            );
        }
        $total_indexers = count($indexers_data);
    }

    $priorities_url = rtrim($api_url, '/') . '/api/v1/indexer/priority?apikey=' . $api_key;
    $priorities_response = wp_remote_get($priorities_url);
    $indexer_priority = array();

    if (!is_wp_error($priorities_response)) {
        $priorities_data = json_decode(wp_remote_retrieve_body($priorities_response), true);
        $indexer_priority = $priorities_data;
    }

    $fetched_data = array(
        'grabs' => $grabs_count,
        'queries' => $queries_count,
        'fail_grabs' => $fail_grabs_count,
        'fail_queries' => $fail_queries_count,
        'indexer_statuses' => $indexer_statuses,
        'total_indexers' => $total_indexers,
        'indexer_priority' => $indexer_priority,
    );

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}