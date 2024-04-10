<?php
/******************
* Proxmox Backup Server Data Collection
* --------------------------------------
* This function collects data from Proxmox Backup Server, a backup solution for virtual machines and containers.
* It fetches information about the backup jobs, including their status, last run time, and storage usage.
*
* Collected Data:
* - Total number of backup jobs
* - Number of backup jobs by status (success, error, running)
* - Total storage usage across all backup jobs
* - Average duration of successful backup jobs
*
* Data not collected but available for extension:
* - Detailed backup job information (name, schedule, retention policy)
* - Backup job-specific storage usage and duration
* - Backup job history and logs
* - Backup storage details (datastore, free space, total space)
* - Proxmox Backup Server configuration and settings
*
* Other opportunities for data collection:
* - Backup task progress and estimated completion time
* - Backup verification and integrity check results
* - Backup restore history and performance metrics
* - Proxmox Backup Server system health and resource utilization
*
* Requirements:
* - Proxmox Backup Server API should be accessible via the provided API URL.
* - API authentication using username and password is required.
*
* Parameters:
* - $api_url: The base URL of the Proxmox Backup Server API.
* - $username: The username for authentication.
* - $password: The password for authentication.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*
* Example of fetched_data structure:
* {
*   "total_jobs": 10,
*   "jobs_success": 8,
*   "jobs_error": 1,
*   "jobs_running": 1,
*   "total_storage_usage": 1500000000000,
*   "avg_backup_duration": 3600
* }
*******************/
function homelab_fetch_proxmox_backup_data($api_url, $username, $password, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'jobs' => '/api2/json/admin/datastore/prune',
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
            )),
        );
        
        $response = wp_remote_post($url, $args);
        
        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($key === 'jobs') {
            $fetched_data['total_jobs'] = count($data['data']);
            
            $status_counts = array(
                'success' => 0,
                'error' => 0,
                'running' => 0,
            );
            
            $total_storage_usage = 0;
            $total_backup_duration = 0;
            $successful_backups = 0;
            
            foreach ($data['data'] as $job) {
                $status = strtolower($job['status']);
                if (isset($status_counts[$status])) {
                    $status_counts[$status]++;
                }
                
                $total_storage_usage += $job['storage_usage'];
                
                if ($status === 'success') {
                    $total_backup_duration += $job['duration'];
                    $successful_backups++;
                }
            }
            
            $fetched_data['jobs_success'] = $status_counts['success'];
            $fetched_data['jobs_error'] = $status_counts['error'];
            $fetched_data['jobs_running'] = $status_counts['running'];
            $fetched_data['total_storage_usage'] = $total_storage_usage;
            $fetched_data['avg_backup_duration'] = $successful_backups > 0 ? $total_backup_duration / $successful_backups : 0;
        }
    }
    
    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}