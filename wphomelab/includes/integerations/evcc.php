<?php
function homelab_fetch_evcc_data($api_url, $service_id) {
    $api_url = rtrim($api_url, '/');

    $endpoints = array(
        'status' => '/api/status',
        'session' => '/api/session',
        'sessions' => '/api/sessions',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'status') {
            $fetched_data['charging_status'] = $data['charging'] ? 'charging' : ($data['connected'] ? 'connected' : 'disconnected');
            $fetched_data['charging_power'] = $data['chargePower'];
        } elseif ($key === 'session') {
            $fetched_data['total_energy_charged'] = $data['chargedEnergy'];
            $fetched_data['session_duration'] = $data['duration'];
            $fetched_data['vehicle_id'] = $data['vehicleID'];
        } elseif ($key === 'sessions') {
            $fetched_data['total_sessions_count'] = count($data);
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}