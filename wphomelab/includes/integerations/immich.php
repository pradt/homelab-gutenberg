<?php
/**********************
* Immich Data Collection
* ----------------------
* This function collects data from Immich, a self-hosted photo and video backup solution, for dashboard display.
* It fetches information about the user, albums, assets, and overall system status.
*
* Collected Data:
* - Total number of users
* - Number of albums per user
* - Total number of assets (photos and videos)
* - Average asset size
* - System storage utilization
*
* Data not collected but available for extension:
* - Detailed user information (name, email, registration date)
* - Album details (name, description, creation date)
* - Asset metadata (filename, timestamp, location, tags)
* - Sharing and collaboration settings
* - Backup and synchronization status
* - Server configuration and performance metrics
*
* Opportunities for additional data collection:
* - User activity and engagement metrics
* - Album access and view counts
* - Asset popularity and sharing statistics
* - Storage trends and growth projections
* - System resource utilization and optimization
*
* Requirements:
* - Immich API should be accessible via the provided API URL.
* - API authentication requires an API key.
*
* Parameters:
* - $api_url: The base URL of the Immich API.
* - $api_key: The API key for authentication.
* - $service_id: The ID of the Immich service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
***********************/
function homelab_fetch_immich_data($api_url, $api_key, $service_id) {
    $api_url = rtrim($api_url, '/');

    $endpoints = array(
        'users' => '/api/user',
        'albums' => '/api/album',
        'assets' => '/api/asset',
        'system' => '/api/server/info',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;

        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key,
            ),
        );

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'users') {
            $fetched_data['total_users'] = count($data);
        } elseif ($key === 'albums') {
            $user_album_counts = array();

            foreach ($data as $album) {
                $user_id = $album['ownerId'];
                if (isset($user_album_counts[$user_id])) {
                    $user_album_counts[$user_id]++;
                } else {
                    $user_album_counts[$user_id] = 1;
                }
            }

            $fetched_data['albums_per_user'] = $user_album_counts;
        } elseif ($key === 'assets') {
            $fetched_data['total_assets'] = count($data);

            $total_size = 0;
            foreach ($data as $asset) {
                $total_size += $asset['fileSize'];
            }

            $fetched_data['avg_asset_size'] = $fetched_data['total_assets'] > 0 ? $total_size / $fetched_data['total_assets'] : 0;
        } elseif ($key === 'system') {
            $fetched_data['storage_usage'] = $data['storageUsage'];
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}