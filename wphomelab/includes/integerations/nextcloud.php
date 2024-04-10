<?php
/******************
* Nextcloud Data Collection
* -------------------------
* This function collects data from Nextcloud, a self-hosted file sync and share platform, for dashboard display.
* It fetches information about the user's storage usage, file counts, and activity.
*
* Collected Data:
* - Total storage capacity
* - Used storage space
* - Free storage space
* - Number of files
* - Number of folders
* - Recent activity (file uploads, downloads, shares)
*
* Data not collected but available for extension:
* - Detailed file and folder information (names, sizes, timestamps)
* - User quota information
* - Shared files and folders
* - File versions and revisions
* - Nextcloud app usage and settings
* - User profile information
*
* Requirements:
* - Nextcloud API should be accessible via the provided API URL.
* - API authentication using either an API key or username/password is required.
*
* Parameters:
* - $api_url: The base URL of the Nextcloud API.
* - $api_key: The API key for authentication (if using API key).
* - $username: The username for authentication (if using username/password).
* - $password: The password for authentication (if using username/password).
* - $service_id: The ID of the service being monitored.
*
* Example of fetched_data structure:
* {
*   "storage_capacity": 1000000000,
*   "storage_used": 250000000,
*   "storage_free": 750000000,
*   "num_files": 5000,
*   "num_folders": 500,
*   "recent_activity": [
*     {
*       "type": "file_upload",
*       "file_name": "example.txt",
*       "timestamp": "2023-05-22T10:30:00Z"
*     },
*     {
*       "type": "file_download",
*       "file_name": "document.pdf",
*       "timestamp": "2023-05-22T09:45:00Z"
*     }
*   ]
* }
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_nextcloud_data($api_url, $api_key = '', $username = '', $password = '', $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'storage' => '/ocs/v2.php/apps/files_sharing/api/v1/shares',
        'files' => '/ocs/v2.php/apps/files/api/v1/files',
        'activity' => '/ocs/v2.php/apps/activity/api/v1/activity',
    );
    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    $headers = array(
        'OCS-APIRequest' => 'true',
        'Content-Type' => 'application/json',
    );

    if (!empty($api_key)) {
        $headers['Authorization'] = 'Bearer ' . $api_key;
    } elseif (!empty($username) && !empty($password)) {
        $auth_token = base64_encode($username . ':' . $password);
        $headers['Authorization'] = 'Basic ' . $auth_token;
    } else {
        $error_message = "API authentication credentials missing";
        $error_timestamp = current_time('mysql');
        homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
        return $fetched_data;
    }

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $args = array(
            'headers' => $headers,
        );

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'storage') {
            $fetched_data['storage_capacity'] = $data['ocs']['data']['quota']['quota'];
            $fetched_data['storage_used'] = $data['ocs']['data']['quota']['used'];
            $fetched_data['storage_free'] = $fetched_data['storage_capacity'] - $fetched_data['storage_used'];
        } elseif ($key === 'files') {
            $fetched_data['num_files'] = $data['ocs']['data']['filecount'];
            $fetched_data['num_folders'] = $data['ocs']['data']['foldercount'];
        } elseif ($key === 'activity') {
            $fetched_data['recent_activity'] = array();
            foreach ($data['ocs']['data'] as $activity) {
                $fetched_data['recent_activity'][] = array(
                    'type' => $activity['type'],
                    'file_name' => $activity['object_name'],
                    'timestamp' => $activity['datetime'],
                );
            }
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}