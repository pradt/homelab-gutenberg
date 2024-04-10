<?php
/******************
* Mylar3 Data Collection
* ----------------------
* This function collects data from Mylar3, a comic book management tool, for dashboard display.
* It fetches information about the user's comic book collection, including the total number of comics,
* the number of comics by status (wanted, snatched, downloaded), and the number of comics by file type.
*
* Collected Data:
* - Total number of comics in the user's collection
* - Number of comics by status (wanted, snatched, downloaded)
* - Number of comics by file type (CBR, CBZ, PDF)
*
* Data not collected but available for extension:
* - Detailed comic information (title, issue number, publisher, release date)
* - Comic book cover images
* - User's pull list and subscriptions
* - Reading progress and history
* - User's reading lists and custom collections
* - Mylar3 configuration and settings
*
* Potential additional data points:
* - Total size of the comic book library
* - Most popular publishers and series in the user's collection
* - Recent additions to the library
* - Upcoming releases based on the user's pull list
*
* Example of fetched_data structure:
* {
*     "total_comics": 1000,
*     "wanted_comics": 50,
*     "snatched_comics": 20,
*     "downloaded_comics": 930,
*     "cbr_comics": 600,
*     "cbz_comics": 350,
*     "pdf_comics": 50
* }
*
* Requirements:
* - Mylar3 API should be accessible via the provided API URL.
* - API key is required for authentication.
*
* Parameters:
* - $api_url: The base URL of the Mylar3 API.
* - $api_key: The API key for authentication.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_mylar3_data($api_url, $api_key, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'comics' => '/api/v1/comics',
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
        if ($key === 'comics') {
            $fetched_data['total_comics'] = count($data);
            $status_counts = array(
                'wanted' => 0,
                'snatched' => 0,
                'downloaded' => 0,
            );
            $file_type_counts = array(
                'cbr' => 0,
                'cbz' => 0,
                'pdf' => 0,
            );
            foreach ($data as $comic) {
                $status = strtolower($comic['status']);
                if (isset($status_counts[$status])) {
                    $status_counts[$status]++;
                }
                $file_type = strtolower($comic['file_type']);
                if (isset($file_type_counts[$file_type])) {
                    $file_type_counts[$file_type]++;
                }
            }
            $fetched_data['wanted_comics'] = $status_counts['wanted'];
            $fetched_data['snatched_comics'] = $status_counts['snatched'];
            $fetched_data['downloaded_comics'] = $status_counts['downloaded'];
            $fetched_data['cbr_comics'] = $file_type_counts['cbr'];
            $fetched_data['cbz_comics'] = $file_type_counts['cbz'];
            $fetched_data['pdf_comics'] = $file_type_counts['pdf'];
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}