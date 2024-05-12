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
function homelab_fetch_immich_data($api_url, $api_key, $service_id)
{
    // Remove trailing slash from the API URL
    $api_url = rtrim($api_url, '/');

    // Define the endpoints to fetch data from
    $endpoints = array(
        'users' => '/api/user',
        'albums' => '/api/album',
        'assets' => '/api/asset',
        'system' => '/api/server-info',
    );

    // Initialize variables to store fetched data, error message, and error timestamp
    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    // Iterate over each endpoint
    foreach ($endpoints as $key => $endpoint) {
        // Construct the full URL for the current endpoint
        $url = $api_url . $endpoint;

        // Set the request headers
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-API-Key' => $api_key,
            ),
        );

        // Send a GET request to the endpoint
        $response = wp_remote_get($url, $args);

        // Store the raw response in fetched_data
        if (!isset($fetched_data[$key])) {
            $fetched_data[$key] = array();
        }
        //$fetched_data['raw_response'][$key] = $response;
        //$fetched_data[$key]['raw_response'] = $response;

        // Check if the request resulted in an error
        if (is_wp_error($response)) {
            // Set the error message and timestamp
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            error_log("Immich Error: " . $error_message); // Log the error
            continue; // Move to the next endpoint
        }

        // Parse the response body as JSON
        $data = json_decode(wp_remote_retrieve_body($response), true);
        $responsecode = wp_remote_retrieve_response_code($response);

        // Check if the response is successful (status code 200)
        if (wp_remote_retrieve_response_code($response) === 200) {
            // Process the data based on the endpoint
            if ($key === 'users') {
                // Check if $data exists and is an array
                if (isset($data) && is_array($data)) {
                    // Store the users data
                    $fetched_data['users'] = $data;
            
                    // Store the total number of users
                    $fetched_data['total_users'] = count($data);
                    if (empty($data)) {
                        error_log("Users data is empty. Response: " . print_r($data, true));
                    }else{
                        error_log($service_id . " - Users data is not empty. Response: " . print_r($data, true));
                    }
                } else {
                    error_log("Users data is not an array or is not set. Response: " . print_r($data, true));
                }
            } elseif ($key === 'albums') {
                // Check if $data exists and is an array
                if (isset($data) && is_array($data)) {
                    // Store the albums data
                    $fetched_data['albums'] = $data;
                    error_log($service_id . " Albums Data -  " . print_r($data, true));
            
                    // Initialize an array to store album counts per user
                    $user_album_counts = array();
            
                    // Iterate over each album
                    foreach ($data as $album) {
                        // Check if 'ownerId' key exists
                        if (isset($album['ownerId'])) {
                            $user_id = $album['ownerId'];
            
                            // Increment the album count for the user
                            if (isset($user_album_counts[$user_id])) {
                                $user_album_counts[$user_id]++;
                            } else {
                                $user_album_counts[$user_id] = 1;
                            }
                        }
                    }
            
                    // Store the album counts per user
                    $fetched_data['albums_per_user'] = $user_album_counts;
                    if (empty($user_album_counts)) {
                        error_log("Albums per user data is empty. Response: " . print_r($data, true));
                    } else {
                        error_log($service_id . " - Albums per user data: " . print_r($user_album_counts, true));
                    }
                } else {
                    error_log("Albums data is not an array or is not set. Response: " . print_r($data, true));
                }
            } elseif ($key === 'assets') {
                // Check if $data exists and is an array
                if (isset($data) && is_array($data)) {
                    // Store the assets data
                    //$fetched_data['assets'] = $data;
            
                    // Store the total number of assets
                    $fetched_data['total_assets'] = count($data);
            
                    // Initialize a variable to store the total size of assets
                    $total_size = 0;
            
                    // Iterate over each asset
                    foreach ($data as $asset) {
                        // Check if 'fileSize' key exists
                        if (isset($asset['fileSize'])) {
                            // Add the file size to the total size
                            $total_size += $asset['fileSize'];
                        }
                    }
            
                    // Calculate and store the average asset size
                    $fetched_data['avg_asset_size'] = $fetched_data['total_assets'] > 0 ? $total_size / $fetched_data['total_assets'] : 0;
            
                    if (empty($data)) {
                        error_log($service_id . "Assets data is empty. Response: " . print_r($data, true));
                    }
                } else {
                    error_log($service_id . "Assets data is not an array or is not set. Response: " . print_r($data, true));
                }
            } elseif ($key === 'system') {
                error_log($service_id . "System " . print_r($data, true));
                // Check if $data exists and is an array
                if (isset($data) && is_array($data)) {
                    // Store the system data
                    $fetched_data['system'] = $data;
                    error_log($service_id . "System for fetched_data" . print_r($data, true));
            
                    // Check if 'storageUsage' key exists
                    if (isset($data['storageUsage'])) {
                        // Store the storage usage
                        $fetched_data['storage_usage'] = $data['storageUsage'];
                    } else {
                        error_log($service_id . "System data does not contain 'storageUsage' key. Response: " . print_r($data, true));
                    }
                } else {
                    error_log($service_id . "System data is not an array or is not set. Response: " . print_r($data, true));
                }
            }
        }else{
            error_log($key . " -- response -- " . $responsecode);
            error_log($key . " -- response -- " . $data);
        }
    }

    // Save the fetched data and error details (if any) to the database
    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    // Return the fetched data
    return $fetched_data;
}