<?php
/**********************
* JDownloader Data Collection
* ----------------------
* This function collects data from JDownloader, a download management tool, for dashboard display.
* It fetches information about the downloads, download status, and overall system status.
*
* Collected Data:
* - Total number of downloads
* - Number of downloads by status (running, finished, failed)
* - Average download speed
* - Total downloaded data
* - System resource utilization (CPU, memory)
*
* Data not collected but available for extension:
* - Detailed download information (filename, URL, size, timestamp)
* - Download source and category
* - Package and file structure
* - Captcha solving and account management
* - Bandwidth and traffic limits
* - Plugin and extension data
*
* Opportunities for additional data collection:
* - Download success rate and error analysis
* - Peak download times and activity patterns
* - File type and category distribution
* - Network and server performance metrics
* - User behavior and preferences
*
* Requirements:
* - JDownloader API should be accessible via the provided API URL.
* - API authentication requires a username and password.
* - Client identification may be required for API access.
*
* Parameters:
* - $api_url: The base URL of the JDownloader API.
* - $username: The username for authentication.
* - $password: The password for authentication.
* - $client_id: The client ID for API access (optional).
* - $service_id: The ID of the JDownloader service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
***********************/
function homelab_fetch_jdownloader_data($api_url, $username, $password, $client_id = '', $service_id) {
    $api_url = rtrim($api_url, '/');

    $endpoints = array(
        'downloads' => '/downloads',
        'system' => '/system',
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
            'body' => json_encode(array(
                'username' => $username,
                'password' => $password,
                'client' => $client_id,
            )),
        );

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'downloads') {
            $fetched_data['total_downloads'] = count($data);

            $status_counts = array(
                'running' => 0,
                'finished' => 0,
                'failed' => 0,
            );

            $total_speed = 0;
            $total_size = 0;

            foreach ($data as $download) {
                $status = strtolower($download['status']);
                if (isset($status_counts[$status])) {
                    $status_counts[$status]++;
                }

                $total_speed += $download['speed'];
                $total_size += $download['bytesLoaded'];
            }

            $fetched_data['downloads_running'] = $status_counts['running'];
            $fetched_data['downloads_finished'] = $status_counts['finished'];
            $fetched_data['downloads_failed'] = $status_counts['failed'];
            $fetched_data['avg_download_speed'] = $fetched_data['total_downloads'] > 0 ? $total_speed / $fetched_data['total_downloads'] : 0;
            $fetched_data['total_downloaded_data'] = $total_size;
        } elseif ($key === 'system') {
            $fetched_data['cpu_usage'] = $data['cpu'];
            $fetched_data['memory_usage'] = $data['memory'];
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}