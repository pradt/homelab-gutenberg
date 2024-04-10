<?php
/***
 * Synology Download Station
 * -------------------------
 * 
 */

 function homelab_fetch_synology_download_station_data($api_url, $username, $password, $service_id) {
    
    $auth_url = rtrim($api_url, '/') . '/webapi/auth.cgi?api=SYNO.API.Auth&version=3&method=login&account=' . $username . '&passwd=' . $password . '&session=homelab_session';
    $auth_response = wp_remote_get($auth_url);

    $error_message = null;
    $error_timestamp = null;

    if (is_wp_error($auth_response)) {
        $error_message = $auth_response->get_error_message();
        $error_timestamp = current_time('mysql');
        return array();
    }

    $auth_response_code = wp_remote_retrieve_response_code($auth_response);
    if ($auth_response_code !== 200) {
        $error_message = "Authentication failed with status code: $auth_response_code";
        $error_timestamp = current_time('mysql');
        return array();
    }

    $auth_data = json_decode(wp_remote_retrieve_body($auth_response), true);
    $sid = $auth_data['data']['sid'];

    $task_url = rtrim($api_url, '/') . '/webapi/entry.cgi?api=SYNO.DownloadStation.Task&version=1&method=list&additional=detail,transfer,tracker&sid=' . $sid;
    $task_response = wp_remote_get($task_url);

    $error_message = null;
    $error_timestamp = null;

    if (is_wp_error($task_response)) {
        $error_message = $task_response->get_error_message();
        $error_timestamp = current_time('mysql');
        return array();
    }

    $task_response_code = wp_remote_retrieve_response_code($task_response);
    if ($task_response_code !== 200) {
        $error_message = "API request failed with status code: $task_response_code";
        $error_timestamp = current_time('mysql');
        return array();
    }

    $task_data = json_decode(wp_remote_retrieve_body($task_response), true);
    $tasks = $task_data['data']['tasks'];

    $active_downloads = 0;
    $waiting_downloads = 0;
    $completed_downloads = 0;
    $total_downloads = count($tasks);

    $total_download_speed = 0;
    $total_upload_speed = 0;

    foreach ($tasks as $task) {
        if ($task['status'] === 'downloading') {
            $active_downloads++;
            $total_download_speed += $task['additional']['transfer']['speed_download'];
        } elseif ($task['status'] === 'waiting') {
            $waiting_downloads++;
        } elseif ($task['status'] === 'finished') {
            $completed_downloads++;
        }

        $total_upload_speed += $task['additional']['transfer']['speed_upload'];
    }

    $fetched_data = array(
        'active_downloads' => $active_downloads,
        'waiting_downloads' => $waiting_downloads,
        'completed_downloads' => $completed_downloads,
        'total_downloads' => $total_downloads,
        'total_download_speed' => $total_download_speed,
        'total_upload_speed' => $total_upload_speed,
    );

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}