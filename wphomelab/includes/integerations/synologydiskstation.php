<?php
/******************
* Synology Disk Station Data Collection
* --------------------------------------
* This function collects data from a Synology Disk Station for dashboard display.
* It fetches information about the system status, storage usage, and performance metrics.
*
* Collected Data:
* - Total number of shares
* - Volume information (total size, used size, free size, used percentage)
* - System uptime
* - Number of volumes
* - CPU usage
* - Memory usage
*
* Data not collected but available for extension:
* - Detailed share information (owner, permissions, mount point type)
* - Network traffic statistics
* - Disk health status and S.M.A.R.T. information
* - User and group management data
* - Backup and replication status
* - Package and application details
*
* Opportunities for additional data collection:
* - System logs and event history
* - File system analytics (file types, sizes, modifications)
* - Active connections and resource utilization per user or IP
* - Scheduled task execution status
* - Surveillance station camera feeds and recordings
*
* Example of fetched_data structure:
* {
*   "total_shares": 10,
*   "total_size": 12000000000000,
*   "used_size": 6500000000000,
*   "free_size": 5500000000000,
*   "used_percentage": 54.17,
*   "uptime": 864000,
*   "volume_count": 2,
*   "cpu_usage": 25,
*   "memory_usage": 60
* }
*
* Requirements:
* - Synology Disk Station API should be accessible via the provided API URL.
* - API authentication (username and password) is required to access the API.
*
* Parameters:
* - $api_url: The base URL of the Synology Disk Station API.
* - $username: The username for API authentication.
* - $password: The password for API authentication.
* - $volume: The volume path for which to retrieve volume status information.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_synology_data($api_url, $username, $password, $volume, $service_id) {
    $auth_url = rtrim($api_url, '/') . '/webapi/auth.cgi';
    $auth_params = array(
        'api' => 'SYNO.API.Auth',
        'version' => '3',
        'method' => 'login',
        'account' => $username,
        'passwd' => $password,
        'session' => 'homelab_session',
        'format' => 'sid'
    );
    $auth_response = wp_remote_get($auth_url, array('body' => $auth_params));

    $fetched_data = array(
        'raw_responses' => array(),
    );
    $error_message = null;
    $error_timestamp = null;

    if (is_wp_error($auth_response)) {
        $error_message = $auth_response->get_error_message();
        $error_timestamp = current_time('mysql');
        error_log("Authentication failed: " . $error_message);
        homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
        return $fetched_data;
    }

    $auth_response_code = wp_remote_retrieve_response_code($auth_response);
    if ($auth_response_code !== 200) {
        $error_message = "Authentication failed with status code: $auth_response_code";
        $error_timestamp = current_time('mysql');
        error_log($error_message);
        homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
        return $fetched_data;
    }

    $auth_body = wp_remote_retrieve_body($auth_response);
    $fetched_data['raw_responses']['auth'] = $auth_body;

    $auth_data = json_decode($auth_body, true);
    $sid = $auth_data['data']['sid'];

    $api_info_url = rtrim($api_url, '/') . '/webapi/query.cgi';
    $api_info_params = array(
        'api' => 'SYNO.API.Info',
        'version' => '1',
        'method' => 'query',
        'query' => 'all'
    );
    $api_info_response = wp_remote_get($api_info_url, array('body' => $api_info_params));
    $api_info_data = json_decode(wp_remote_retrieve_body($api_info_response), true);

    if (!isset($api_info_data['data'])) {
        $error_message = "Invalid API info response: Missing 'data' key";
        $error_timestamp = current_time('mysql');
        error_log($error_message);
        homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
        return $fetched_data;
    }

    $endpoints = array(
        'shares' => array(
            'api' => 'SYNO.FileStation.List',
            'version' => '2',
            'method' => 'list_share',
            'additional' => 'size,owner,time,perm,mount_point_type,volume_status'
        ),
        'system' => array(
            'api' => 'SYNO.Core.System',
            'version' => '1',
            'method' => 'info'
        ),
        'volumes' => array(
            'api' => 'SYNO.Storage.Volume',
            'version' => '1',
            'method' => 'list'
        ),
        'resources' => array(
            'api' => 'SYNO.Core.System.Utilization',
            'version' => '1',
            'method' => 'get',
            'type' => 'current',
            'resource' => 'cpu,memory'
        ),
        'volume_status' => array(
            'api' => 'SYNO.FileStation.VolumeStatus',
            'version' => '1',
            'method' => 'list',
            'additional' => 'size_total,size_used,size_free,percentage_used',
            'volume_path' => $volume
        ),
    );

    foreach ($endpoints as $key => $endpoint) {
        $api_name = $endpoint['api'];

        if (!isset($api_info_data['data'][$api_name])) {
            $error_message = "API '$api_name' not found in API info response";
            $error_timestamp = current_time('mysql');
            error_log($error_message);
            continue;
        }

        $api_path = $api_info_data['data'][$api_name]['path'];
        $max_version = $api_info_data['data'][$api_name]['maxVersion'];

        $url = rtrim($api_url, '/') . '/webapi/' . $api_path;
        $params = array(
            'api' => $api_name,
            'version' => min($endpoint['version'], $max_version),
            'method' => $endpoint['method'],
            '_sid' => $sid
        );

        if (isset($endpoint['additional'])) {
            $params['additional'] = $endpoint['additional'];
        }

        if ($key === 'volume_status') {
            $params['volume_path'] = $volume;
        }

        $response = wp_remote_get($url, array('body' => $params));


        //$url = rtrim($api_url, '/') . $endpoint;
        //$response = wp_remote_get($url);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            error_log($error_message);
            continue;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            $error_message = "API request failed for endpoint '{$key}' with status code: $response_code";
            $error_timestamp = current_time('mysql');
            error_log($error_message);
            continue;
        }

        $response_body = wp_remote_retrieve_body($response);
        $fetched_data['raw_responses'][$key] = $response_body;

        $data = json_decode($response_body, true);

        if (!is_array($data)) {
            $error_message = "Invalid response data for endpoint '$key'";
            $error_timestamp = current_time('mysql');
            error_log($error_message);
            continue;
        }

        if ($key === 'shares' && isset($data['data']['shares'])) {
            $fetched_data['total_shares'] = count($data['data']['shares']);
        }

        if ($key === 'system' && isset($data['data']) && isset($data['data']['uptime'])) {
            $fetched_data['uptime'] = $data['data']['uptime'];
        }

        if ($key === 'volumes' && isset($data['data']['volumes'])) {
            $fetched_data['volume_count'] = count($data['data']['volumes']);
        }

        if ($key === 'resources' && isset($data['data'])) {
            $fetched_data['cpu_usage'] = $data['data']['cpu']['user_load'];
            $fetched_data['memory_usage'] = $data['data']['memory']['real_usage'];
        }

        if ($key === 'volume_status' && isset($data['data']['volumes'][0])) {
            $volume_info = $data['data']['volumes'][0];
            $fetched_data['total_size'] = $volume_info['size_total'];
            $fetched_data['used_size'] = $volume_info['size_used'];
            $fetched_data['free_size'] = $volume_info['size_free'];
            $fetched_data['used_percentage'] = $volume_info['percentage_used'];
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}