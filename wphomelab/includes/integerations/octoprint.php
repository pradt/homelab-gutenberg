<?php
/******************
* OctoPrint Data Collection
* -------------------------
* This function collects data from OctoPrint, a 3D printer control software, for dashboard display.
* It fetches information about the connected printers, their status, print progress, and temperature data.
*
* Collected Data:
* - Total number of connected printers
* - Printer status (operational, offline, error)
* - Current print job progress percentage
* - Hotend and bed temperatures
* - Filament usage (length and volume)
*
* Data not collected but available for extension:
* - Detailed printer information (name, model, firmware version)
* - Print job details (file name, estimated time, time elapsed)
* - Printer connection details (port, baud rate)
* - Printer settings and configuration
* - Historical print job data and statistics
* - Webcam feed and timelapse settings
*
* Requirements:
* - OctoPrint API should be accessible via the provided API URL.
* - API authentication key (API key) is required for accessing the OctoPrint API.
*
* Parameters:
* - $api_url: The base URL of the OctoPrint API.
* - $api_key: The API key for authentication.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*
* Example fetched_data structure:
* {
*   "total_printers": 1,
*   "printer_status": "operational",
*   "print_progress": 75.8,
*   "hotend_temp": 205.3,
*   "bed_temp": 60.2,
*   "filament_length": 123.45,
*   "filament_volume": 67.89
* }
*******************/
function homelab_fetch_octoprint_data($api_url, $api_key, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
      'printer' => '/api/printer',
      'job' => '/api/job',
    );
    
    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;
    
    $args = array(
      'headers' => array(
        'Content-Type' => 'application/json',
        'X-Api-Key' => $api_key,
      ),
    );
    
    foreach ($endpoints as $key => $endpoint) {
      $url = $api_url . $endpoint;
      $response = wp_remote_get($url, $args);
      
      if (is_wp_error($response)) {
        $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
        $error_timestamp = current_time('mysql');
        continue;
      }
      
      $data = json_decode(wp_remote_retrieve_body($response), true);
      
      if ($key === 'printer') {
        $fetched_data['total_printers'] = 1; // Assuming OctoPrint manages a single printer
        $fetched_data['printer_status'] = $data['state']['text'];
        $fetched_data['hotend_temp'] = $data['temperature']['tool0']['actual'];
        $fetched_data['bed_temp'] = $data['temperature']['bed']['actual'];
      } elseif ($key === 'job') {
        $fetched_data['print_progress'] = $data['progress']['completion'];
        $fetched_data['filament_length'] = $data['filament']['tool0']['length'];
        $fetched_data['filament_volume'] = $data['filament']['tool0']['volume'];
      }
    }
    
    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
  }