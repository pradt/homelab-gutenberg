<?php
/******************
* PhotoPrism Data Collection
* ---------------------------
* This function collects data from PhotoPrism, a self-hosted photo management application, for dashboard display.
* It fetches information about the photo library, including the total number of photos, albums, and labels.
*
* Collected Data:
* - Total number of photos
* - Total number of albums
* - Total number of labels
* - Storage usage (in bytes)
*
* Data not collected but available for extension:
* - Detailed photo information (title, description, date, location, camera, lens)
* - Album details (name, description, cover photo)
* - Label details (name, category, photo count)
* - User and account information
* - Photo search and filtering options
* - PhotoPrism configuration and settings
*
* Opportunities for additional data:
* - Recent or featured photos
* - Most viewed or liked photos
* - Photo distribution by date, location, or camera
* - AI-powered photo analysis and tagging
*
* Example of fetched_data structure:
* {
*   "total_photos": 5000,
*   "total_albums": 50,
*   "total_labels": 200,
*   "storage_usage": 10737418240
* }
*
* Requirements:
* - PhotoPrism API should be accessible via the provided API URL.
* - API authentication requires a username and password.
*
* Parameters:
* - $api_url: The base URL of the PhotoPrism API.
* - $username: The username for authentication.
* - $password: The password for authentication.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_photoprism_data($api_url, $username, $password, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'photos' => '/api/v1/photos',
        'albums' => '/api/v1/albums',
        'labels' => '/api/v1/labels',
        'storage' => '/api/v1/storage',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'auth' => array($username, $password),
        );

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        switch ($key) {
            case 'photos':
                $fetched_data['total_photos'] = count($data);
                break;
            case 'albums':
                $fetched_data['total_albums'] = count($data);
                break;
            case 'labels':
                $fetched_data['total_labels'] = count($data);
                break;
            case 'storage':
                $fetched_data['storage_usage'] = $data['used'];
                break;
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}