<?php
/******************
* Moonraker Data Collection
* ----------------------
* This function collects data from Moonraker, a 3D printer web interface, for dashboard display.
* It fetches information about the connected printer, including its status, temperatures, print progress, and more.
*
* Collected Data:
* - Printer status (operational, paused, error, etc.)
* - Current print job information (file name, progress percentage, time elapsed, time remaining)
* - Hotend and bed temperatures (actual and target)
* - Printer metadata (name, model, firmware version)
*
* Data not collected but available for extension:
* - Detailed printer settings and configuration
* - Filament usage and estimation
* - Print history and statistics
* - Webcam feed and timelapse functionality
* - Moonraker API version and server information
*
* Example of fetched_data structure:
* {
*   "printer_status": "operational",
*   "current_print_job": {
*     "file_name": "example.gcode",
*     "progress_percentage": 50.0,
*     "time_elapsed": "1h 30m",
*     "time_remaining": "1h 30m"
*   },
*   "temperatures": {
*     "hotend": {
*       "actual": 200.0,
*       "target": 200.0
*     },
*     "bed": {
*       "actual": 60.0,
*       "target": 60.0
*     }
*   },
*   "printer_metadata": {
*     "name": "My 3D Printer",
*     "model": "Prusa i3 MK3S",
*     "firmware_version": "1.0.0"
*   }
* }
*
* Requirements:
* - Moonraker API should be accessible via the provided API URL.
* - API key may be required depending on Moonraker configuration.
*
* Parameters:
* - $api_url: The base URL of the Moonraker API.
* - $api_key: The API key for authentication (if required).
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_moonraker_data($api_url, $api_key = '', $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'printer_info' => '/printer/info',
        'printer_objects' => '/printer/objects/query?print_stats&extruder&heater_bed&toolhead&virtual_sdcard',
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
        );
        if (!empty($api_key)) {
            $args['headers']['X-Api-Key'] = $api_key;
        }
        
        $response = wp_remote_get($url, $args);
        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($key === 'printer_info') {
            $fetched_data['printer_status'] = $data['state']['text'];
            $fetched_data['printer_metadata'] = array(
                'name' => $data['name'],
                'model' => $data['model'],
                'firmware_version' => $data['software_version'],
            );
        } elseif ($key === 'printer_objects') {
            $print_stats = $data['result']['status']['print_stats'];
            $fetched_data['current_print_job'] = array(
                'file_name' => $print_stats['filename'],
                'progress_percentage' => $print_stats['completion'] * 100,
                'time_elapsed' => $print_stats['print_duration'],
                'time_remaining' => $print_stats['print_duration'] - $print_stats['total_duration'],
            );
            
            $fetched_data['temperatures'] = array(
                'hotend' => array(
                    'actual' => $data['result']['status']['extruder']['temperature'],
                    'target' => $data['result']['status']['extruder']['target'],
                ),
                'bed' => array(
                    'actual' => $data['result']['status']['heater_bed']['temperature'],
                    'target' => $data['result']['status']['heater_bed']['target'],
                ),
            );
        }
    }
    
    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}