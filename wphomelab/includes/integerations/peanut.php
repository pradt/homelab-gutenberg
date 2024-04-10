<?php
/******************
* PeaNUT

 Data Collection
* ----------------------
* This function collects data from PeaNUT

, a personal home server monitoring tool, for dashboard display.
* It fetches information about the monitored services, devices, and system resources.
*
* Collected Data:
* - Total number of monitored services
* - Number of services by status (online, offline, unknown)
* - Total number of monitored devices
* - Number of devices by status (online, offline, unknown)
* - CPU usage percentage
* - Memory usage percentage
* - Disk usage percentage
*
* Data not collected but available for extension:
* - Detailed service information (name, port, protocol)
* - Detailed device information (name, IP address, MAC address)
* - Network traffic statistics
* - Process-level monitoring data
* - Log file monitoring and analysis
* - Alert and notification settings
* - Historical performance data
* - PeaNUT

 configuration and settings
*
* Opportunities for additional data collection:
* - Service dependency mapping
* - Service response time monitoring
* - Device hardware health monitoring (e.g., disk SMART data)
* - Integration with third-party monitoring tools
* - User activity and authentication logging
*
* Requirements:
* - PeaNUT

 API should be accessible via the provided API URL.
* - API authentication token (API key) is required.
*
* Parameters:
* - $api_url: The base URL of the PeaNUT

 API.
* - $api_key: The API key for authentication.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*
* Example of fetched_data structure:
* {
*   "total_services": 10,
*   "services_online": 8,
*   "services_offline": 1,
*   "services_unknown": 1,
*   "total_devices": 5,
*   "devices_online": 4,
*   "devices_offline": 1,
*   "devices_unknown": 0,
*   "cpu_usage": 45.6,
*   "memory_usage": 60.2,
*   "disk_usage": 75.8
* }
*******************/
function homelab_fetch_peanut_data($api_url, $api_key, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
      'services' => '/api/v1/services',
      'devices' => '/api/v1/devices',
      'system' => '/api/v1/system',
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
  
      if ($key === 'services') {
        $fetched_data['total_services'] = count($data);
        $status_counts = array(
          'online' => 0,
          'offline' => 0,
          'unknown' => 0,
        );
        foreach ($data as $service) {
          $status = strtolower($service['status']);
          if (isset($status_counts[$status])) {
            $status_counts[$status]++;
          } else {
            $status_counts['unknown']++;
          }
        }
        $fetched_data['services_online'] = $status_counts['online'];
        $fetched_data['services_offline'] = $status_counts['offline'];
        $fetched_data['services_unknown'] = $status_counts['unknown'];
      } elseif ($key === 'devices') {
        $fetched_data['total_devices'] = count($data);
        $status_counts = array(
          'online' => 0,
          'offline' => 0,
          'unknown' => 0,
        );
        foreach ($data as $device) {
          $status = strtolower($device['status']);
          if (isset($status_counts[$status])) {
            $status_counts[$status]++;
          } else {
            $status_counts['unknown']++;
          }
        }
        $fetched_data['devices_online'] = $status_counts['online'];
        $fetched_data['devices_offline'] = $status_counts['offline'];
        $fetched_data['devices_unknown'] = $status_counts['unknown'];
      } elseif ($key === 'system') {
        $fetched_data['cpu_usage'] = $data['cpu_usage'];
        $fetched_data['memory_usage'] = $data['memory_usage'];
        $fetched_data['disk_usage'] = $data['disk_usage'];
      }
    }
  
    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
  }