<?php
/********************
* Synology Disk Station Data Collection
* --------------------------------------
* This function collects data from a Synology Disk Station for dashboard display.
* It fetches information about the system status, storage usage, and performance metrics.
*
* Collected Data:
* - Total number of shares
* - System information (model, RAM size, serial number, uptime, temperature warning)
* - Volume information (total size, used size, free size, used percentage)
* - CPU usage (user and system)
* - Memory usage
* - Network traffic (received and transmitted bytes)
* - Disk usage and performance metrics (utilization, read/write access, read/write bytes)
*
* Data not collected but available for extension:
* - Detailed share information (owner, permissions, mount point type)
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
*   "model": "DS920+",
*   "ram_size": 4096,
*   "serial": "1234567890",
*   "up_time": "123:45:67",
*   "temperature_warning": false,
*   "total_size": 12000000000000,
*   "used_size": 6500000000000,
*   "free_size": 5500000000000,
*   "used_percentage": 54.17,
*   "cpu_usage_user": 10,
*   "cpu_usage_system": 5,
*   "memory_usage": 60,
*   "network_rx": 1000000,
*   "network_tx": 500000,
*   "disk_usage": 75,
*   "disk_read_access": 1000,
*   "disk_write_access": 500,
*   "disk_read_bytes": 1000000,
*   "disk_write_bytes": 500000
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
********************/
function homelab_fetch_synology_data($api_url, $username, $password, $volume, $service_id) {
    // Authenticate and obtain the session ID (SID)
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

    // Initialize variables for fetched data, error message, and error timestamp
    $fetched_data = array(
        'raw_responses' => array(),
    );
    $error_message = null;
    $error_timestamp = null;

    // Check for authentication errors
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

    // Store the authentication response and extract the session ID (SID)
    $auth_body = wp_remote_retrieve_body($auth_response);
    $fetched_data['raw_responses']['auth'] = $auth_body;

    $auth_data = json_decode($auth_body, true);
    $sid = $auth_data['data']['sid'];

    // Retrieve API information
    $api_info_url = rtrim($api_url, '/') . '/webapi/query.cgi';
    $api_info_params = array(
        'api' => 'SYNO.API.Info',
        'version' => '1',
        'method' => 'query',
        'query' => 'all'
    );
    $api_info_response = wp_remote_get($api_info_url, array('body' => $api_info_params));
    $api_info_data = json_decode(wp_remote_retrieve_body($api_info_response), true);

    // Define the API endpoints and their parameters
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
            'resource' => 'cpu,memory,network,disk'
        ),
        'volume_status' => array(
            'api' => 'SYNO.FileStation.VolumeStatus',
            'version' => '1',
            'method' => 'list',
            'additional' => 'size_total,size_used,size_free,percentage_used',
            'volume_path' => $volume
        ),
    );

    // Iterate over each endpoint and make API requests
    foreach ($endpoints as $key => $endpoint) {
        $api_name = $endpoint['api'];

        // Check if the API is available in the API info response
        if (!isset($api_info_data['data'][$api_name])) {
            $error_message = "API '$api_name' not found in API info response";
            $error_timestamp = current_time('mysql');
            error_log($error_message);
            continue;
        }

        // Retrieve the API path and maximum version from the API info response
        $api_path = $api_info_data['data'][$api_name]['path'];
        $max_version = $api_info_data['data'][$api_name]['maxVersion'];

        // Construct the API request URL and parameters
        $url = rtrim($api_url, '/') . '/webapi/' . $api_path;
        $params = array(
            'api' => $api_name,
            'version' => min($endpoint['version'], $max_version),
            'method' => $endpoint['method'],
            '_sid' => $sid
        );

        // Add additional parameters based on the endpoint configuration
        if (isset($endpoint['additional'])) {
            $params['additional'] = $endpoint['additional'];
        }

        if (isset($endpoint['type'])) {
            $params['type'] = $endpoint['type'];
        }

        if (isset($endpoint['resource'])) {
            $params['resource'] = $endpoint['resource'];
        }

        if ($key === 'volume_status' && isset($endpoint['volume_path'])) {
            $params['volume_path'] = $endpoint['volume_path'];
        }

        // Make the API request
        $response = wp_remote_get($url, array('body' => $params));

        // Check for API request errors
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

        // Store the API response and extract relevant data
        $response_body = wp_remote_retrieve_body($response);
        $fetched_data['raw_responses'][$key] = $response_body;

        $data = json_decode($response_body, true);

        // Process and store the retrieved data based on the endpoint
        if ($key === 'shares' && isset($data['data']['shares'])) {
            $fetched_data['total_shares'] = count($data['data']['shares']);
        }

        if ($key === 'system' && isset($data['data'])) {
            $fetched_data['model'] = $data['data']['model'];
            $fetched_data['ram_size'] = $data['data']['ram_size'];
            $fetched_data['serial'] = $data['data']['serial'];
            $fetched_data['up_time'] = $data['data']['up_time'];
            $fetched_data['temperature_warning'] = $data['data']['temperature_warning'];
        }

        if ($key === 'resources' && isset($data['data'])) {
            $fetched_data['cpu_usage_user'] = $data['data']['cpu']['user_load'];
            $fetched_data['cpu_usage_system'] = $data['data']['cpu']['system_load'];
            $fetched_data['memory_usage'] = $data['data']['memory']['real_usage'];
            $fetched_data['network_rx'] = $data['data']['network'][0]['rx'];
            $fetched_data['network_tx'] = $data['data']['network'][0]['tx'];
            $fetched_data['disk_usage'] = $data['data']['disk']['total']['utilization'];
            $fetched_data['disk_read_access'] = $data['data']['disk']['total']['read_access'];
            $fetched_data['disk_write_access'] = $data['data']['disk']['total']['write_access'];
            $fetched_data['disk_read_bytes'] = $data['data']['disk']['total']['read_byte'];
            $fetched_data['disk_write_bytes'] = $data['data']['disk']['total']['write_byte'];
        }

        if ($key === 'volume_status' && isset($data['data']['volumes'][0])) {
            $volume_info = $data['data']['volumes'][0];
            $fetched_data['total_size'] = $volume_info['size_total'];
            $fetched_data['used_size'] = $volume_info['size_used'];
            $fetched_data['free_size'] = $volume_info['size_free'];
            $fetched_data['used_percentage'] = $volume_info['percentage_used'];
        }
    }

    // Save the fetched data and any error information
    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}