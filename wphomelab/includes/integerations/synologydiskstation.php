<?php

/***
 * Synology Disk Station
 * ---------------------
 * Total Shares: The total number of shares on the Synology DiskStation.
Total Size: The total storage capacity of the specified volume (if provided).
Used Size: The used storage space of the specified volume (if provided).
Free Size: The free storage space of the specified volume (if provided).
Used Percentage: The percentage of used storage space of the specified volume (if provided).
 * Uptime: The uptime of the Synology DiskStation in seconds.
Volume Count: The total number of available volumes on the Synology DiskStation.
CPU Usage: The current CPU usage percentage.
Memory Usage: The current memory usage percentage.
 */

 function homelab_fetch_synology_data($api_url, $username, $password, $volume, $service_id) {
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

    $stats_url = rtrim($api_url, '/') . '/webapi/entry.cgi?api=SYNO.FileStation.List&version=2&method=list_share&additional=size,owner,time,perm,mount_point_type,volume_status&sid=' . $sid;
    $stats_response = wp_remote_get($stats_url);

    if (is_wp_error($stats_response)) {
        $error_message = $stats_response->get_error_message();
        $error_timestamp = current_time('mysql');
        return array();
    }

    $stats_response_code = wp_remote_retrieve_response_code($stats_response);
    if ($stats_response_code !== 200) {
        $error_message = "API request failed with status code: $stats_response_code";
        $error_timestamp = current_time('mysql');
        return array();
    }

    $stats_data = json_decode(wp_remote_retrieve_body($stats_response), true);
    $total_shares = count($stats_data['data']['shares']);

    $system_url = rtrim($api_url, '/') . '/webapi/entry.cgi?api=SYNO.Core.System&version=1&method=info&sid=' . $sid;
    $system_response = wp_remote_get($system_url);

    if (!is_wp_error($system_response) && wp_remote_retrieve_response_code($system_response) === 200) {
        $system_data = json_decode(wp_remote_retrieve_body($system_response), true);
        $uptime = $system_data['data']['uptime'];
    } else {
        $uptime = 0;
    }

    $volume_url = rtrim($api_url, '/') . '/webapi/entry.cgi?api=SYNO.Storage.Volume&version=1&method=list&sid=' . $sid;
    $volume_response = wp_remote_get($volume_url);

    if (!is_wp_error($volume_response) && wp_remote_retrieve_response_code($volume_response) === 200) {
        $volume_data = json_decode(wp_remote_retrieve_body($volume_response), true);
        $volumes = $volume_data['data']['volumes'];
        $volume_count = count($volumes);
    } else {
        $volume_count = 0;
    }

    $resource_url = rtrim($api_url, '/') . '/webapi/entry.cgi?api=SYNO.Core.System.Utilization&version=1&method=get&type=current&resource=cpu,memory&sid=' . $sid;
    $resource_response = wp_remote_get($resource_url);

    if (!is_wp_error($resource_response) && wp_remote_retrieve_response_code($resource_response) === 200) {
        $resource_data = json_decode(wp_remote_retrieve_body($resource_response), true);
        $cpu_usage = $resource_data['data']['cpu']['user_load'];
        $memory_usage = $resource_data['data']['memory']['real_usage'];
    } else {
        $cpu_usage = 0;
        $memory_usage = 0;
    }

    $volume_url = rtrim($api_url, '/') . '/webapi/entry.cgi?api=SYNO.FileStation.VolumeStatus&version=1&method=list&additional=size_total,size_used,size_free,percentage_used&volume_path=' . $volume . '&sid=' . $sid;
    $volume_response = wp_remote_get($volume_url);

    if (!is_wp_error($volume_response) && wp_remote_retrieve_response_code($volume_response) === 200) {
        $volume_data = json_decode(wp_remote_retrieve_body($volume_response), true);
        $volume_info = $volume_data['data']['volumes'][0];
        $total_size = $volume_info['size_total'];
        $used_size = $volume_info['size_used'];
        $free_size = $volume_info['size_free'];
        $used_percentage = $volume_info['percentage_used'];
    } else {
        $total_size = 0;
        $used_size = 0;
        $free_size = 0;
        $used_percentage = 0;
    }

    $fetched_data = array(
        'total_shares' => $total_shares,
        'total_size' => $total_size,
        'used_size' => $used_size,
        'free_size' => $free_size,
        'used_percentage' => $used_percentage,
        'uptime' => $uptime,
        'volume_count' => $volume_count,
        'cpu_usage' => $cpu_usage,
        'memory_usage' => $memory_usage,
    );

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}