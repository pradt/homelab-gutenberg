<?php
/******************
* Synology Download Station Data Collection
* -----------------------------------------
* This function collects data from Synology Download Station, a download management tool, for dashboard display.
* It fetches information about the download tasks, including their status, progress, and download speeds.
*
* Collected Data:
* - Total number of download tasks
* - Number of tasks by status (waiting, downloading, paused, finished, error)
* - Average download speed across all active tasks
* - Total downloaded size
* - Total upload size
*
* Data not collected but available for extension:
* - Detailed task information (file name, size, added time, completed time)
* - Task-specific download and upload speeds
* - Peer and seeder counts for BitTorrent tasks
* - Bandwidth usage and limits
* - Download Station settings and configuration
*
* Other opportunities for data collection:
* - Categorization of tasks by file type or category
* - Historical download and upload statistics
* - Disk usage and storage capacity
* - Scheduled or automated tasks
* - RSS feed monitoring and auto-downloading
*
* Example of fetched_data JSON structure:
* {
*   "total_tasks": 10,
*   "tasks_waiting": 2,
*   "tasks_downloading": 3,
*   "tasks_paused": 1,
*   "tasks_finished": 4,
*   "tasks_error": 0,
*   "avg_download_speed": 5.2,
*   "total_downloaded_size": 12345678,
*   "total_upload_size": 1234567
* }
*
* Requirements:
* - Synology Download Station API should be accessible via the provided API URL.
* - API authentication (username and password) is required to access the API.
*
* Parameters:
* - $api_url: The base URL of the Synology Download Station API.
* - $username: The username for API authentication.
* - $password: The password for API authentication.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_synology_download_station_data($api_url, $username, $password, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'tasks' => '/webapi/DownloadStation/task.cgi',
    );
    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $args = array(
            'method' => 'GET',
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode(array(
                'api' => 'SYNO.DownloadStation.Task',
                'version' => 1,
                'method' => 'list',
                'additional' => json_encode(array('detail')),
            )),
        );

        $response = wp_remote_post($url, $args);
        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        if ($key === 'tasks' && isset($data['data']['tasks'])) {
            $tasks = $data['data']['tasks'];
            $fetched_data['total_tasks'] = count($tasks);
            $status_counts = array(
                'waiting' => 0,
                'downloading' => 0,
                'paused' => 0,
                'finished' => 0,
                'error' => 0,
            );
            $total_download_speed = 0;
            $total_downloaded_size = 0;
            $total_upload_size = 0;

            foreach ($tasks as $task) {
                $status = strtolower($task['status']);
                if (isset($status_counts[$status])) {
                    $status_counts[$status]++;
                }
                if ($status === 'downloading') {
                    $total_download_speed += $task['additional']['transfer']['speed_download'];
                }
                $total_downloaded_size += $task['additional']['transfer']['size_downloaded'];
                $total_upload_size += $task['additional']['transfer']['size_uploaded'];
            }

            $fetched_data['tasks_waiting'] = $status_counts['waiting'];
            $fetched_data['tasks_downloading'] = $status_counts['downloading'];
            $fetched_data['tasks_paused'] = $status_counts['paused'];
            $fetched_data['tasks_finished'] = $status_counts['finished'];
            $fetched_data['tasks_error'] = $status_counts['error'];
            $fetched_data['avg_download_speed'] = $status_counts['downloading'] > 0 ? $total_download_speed / $status_counts['downloading'] : 0;
            $fetched_data['total_downloaded_size'] = $total_downloaded_size;
            $fetched_data['total_upload_size'] = $total_upload_size;
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}