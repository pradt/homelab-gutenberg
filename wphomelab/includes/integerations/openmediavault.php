<?php
/******************
* OpenMediaVault Data Collection
* -------------------------------
* This function collects data from OpenMediaVault (OMV), a network-attached storage (NAS) solution,
* for dashboard display. It fetches information about the system, storage, and services.
*
* Collected Data:
* - System information (hostname, version, CPU usage, memory usage)
* - Storage information (total capacity, used space, available space, storage devices)
* - Service status (SMB/CIFS, NFS, FTP, SSH, Rsync)
*
* Data Structure Example (fetched_data):
* {
*   "system_info": {
*     "hostname": "omv-nas",
*     "version": "5.6.7",
*     "cpu_usage": 25.3,
*     "memory_usage": 45.8
*   },
*   "storage_info": {
*     "total_capacity": 4000,
*     "used_space": 2500,
*     "available_space": 1500,
*     "storage_devices": [
*       {
*         "name": "sda",
*         "size": 2000,
*         "used": 1500,
*         "available": 500
*       },
*       {
*         "name": "sdb",
*         "size": 2000,
*         "used": 1000,
*         "available": 1000
*       }
*     ]
*   },
*   "service_status": {
*     "smb": true,
*     "nfs": false,
*     "ftp": true,
*     "ssh": true,
*     "rsync": false
*   }
* }
*
* Data not collected but available for extension:
* - Detailed service configuration (SMB/CIFS shares, NFS exports, FTP settings)
* - Network interface information (IP addresses, network usage)
* - Disk health and SMART data
* - Installed plugins and their status
* - System logs and events
* - Backup and snapshot information
*
* Requirements:
* - OpenMediaVault API should be accessible via the provided API URL.
* - API authentication using username and password is required.
*
* Parameters:
* - $api_url: The base URL of the OpenMediaVault API.
* - $username: The username for API authentication.
* - $password: The password for API authentication.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_omv_data($api_url, $username, $password, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'system_info' => '/rpc.php?service=System&method=getInformation',
        'storage_info' => '/rpc.php?service=FileSystemMgmt&method=enumerateFilesystems',
        'service_status' => '/rpc.php?service=Services&method=getStatus',
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

        if ($key === 'system_info') {
            $fetched_data['system_info'] = array(
                'hostname' => $data['response']['hostname'],
                'version' => $data['response']['version'],
                'cpu_usage' => $data['response']['cpuUsage'],
                'memory_usage' => $data['response']['memoryUsage'],
            );
        } elseif ($key === 'storage_info') {
            $total_capacity = 0;
            $used_space = 0;
            $available_space = 0;
            $storage_devices = array();

            foreach ($data['response'] as $filesystem) {
                $total_capacity += $filesystem['size'];
                $used_space += $filesystem['used'];
                $available_space += $filesystem['available'];

                $storage_devices[] = array(
                    'name' => $filesystem['devicefile'],
                    'size' => $filesystem['size'],
                    'used' => $filesystem['used'],
                    'available' => $filesystem['available'],
                );
            }

            $fetched_data['storage_info'] = array(
                'total_capacity' => $total_capacity,
                'used_space' => $used_space,
                'available_space' => $available_space,
                'storage_devices' => $storage_devices,
            );
        } elseif ($key === 'service_status') {
            $fetched_data['service_status'] = array(
                'smb' => $data['response']['smb'],
                'nfs' => $data['response']['nfs'],
                'ftp' => $data['response']['ftp'],
                'ssh' => $data['response']['ssh'],
                'rsync' => $data['response']['rsync'],
            );
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}