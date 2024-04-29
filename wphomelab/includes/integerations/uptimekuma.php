<?php
/******************
* Uptime Kuma Data Collection
* ----------------------
* This function collects data from Uptime Kuma, a service monitoring tool, for dashboard display.
* It fetches information about the monitored services, including their status, response times, and uptime percentages.
*
* Collected Data:
* - Total number of monitored services
* - Number of services by status (up, down)
* - Average uptime percentage across all services
* - Current incident status (if any)
*
* Data not collected but available for extension:
* - Detailed service information (name, URL, port, protocol)
* - Service-specific response times and uptime percentages
* - Service health check details (last check time, error messages)
* - Alert and notification settings
* - Historical uptime and response time data
* - Uptime Kuma configuration and settings
*
* Requirements:
* - Uptime Kuma status page should be accessible via the provided URL.
* - Status page slug is required to identify the specific status page.
*
* Parameters:
* - $url: The base URL of the Uptime Kuma instance.
* - $slug: The slug of the status page.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the status page request process.
* - Stores error messages and timestamps for troubleshooting.
*
* Example of fetched_data structure:
* {
*   "total_services": 10,
*   "services_up": 8,
*   "services_down": 2,
*   "avg_uptime_percentage": 95.5,
*   "incident_status": false
* }
*******************/

function homelab_fetch_uptimekuma_data($url, $service_id) {
    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;
    
    // Extract the base URL from the provided URL
    $base_url = rtrim(preg_replace('/\/status\/[^\/]+$/', '', $url), '/');
    
    // Extract the slug from the provided URL
    $slug = basename(parse_url($url, PHP_URL_PATH));

    // Fetch status page data
    $status_url = $base_url . '/api/status-page/' . $slug;
    $status_response = wp_remote_get($status_url);

    if (is_wp_error($status_response)) {
        $error_message = "Status page request failed: " . $status_response->get_error_message();
        $error_timestamp = current_time('mysql');
    } else {
        $status_data = json_decode(wp_remote_retrieve_body($status_response), true);
        
        // Process the status page data
        if (!empty($status_data)) {
            $fetched_data['incident_status'] = !empty($status_data['incident']);
            
            // Calculate incident time in hours
            if ($fetched_data['incident_status']) {
                $incident_created_date = strtotime($status_data['incident']['createdDate']);
                $incident_time = abs(time() - $incident_created_date) / 3600;
                $fetched_data['incident_time'] = round($incident_time, 2);
                
                // Capture incident details
                $fetched_data['incident_message'] = !empty($status_data['incident']['message']) ? $status_data['incident']['message'] : '';
                $fetched_data['incident_status'] = !empty($status_data['incident']['status']) ? $status_data['incident']['status'] : '';
                $fetched_data['incident_updated_at'] = !empty($status_data['incident']['updatedAt']) ? $status_data['incident']['updatedAt'] : '';
            }
            
            // Extract additional information from the status page data
            $fetched_data['page_title'] = !empty($status_data['config']['title']) ? $status_data['config']['title'] : '';
            $fetched_data['page_description'] = !empty($status_data['config']['description']) ? $status_data['config']['description'] : '';
            
            // Extract monitor data
            $fetched_data['total_monitors'] = 0;
            $fetched_data['monitors'] = array();
            $fetched_data['monitor_groups'] = array();
            
            foreach ($status_data['publicGroupList'] as $group) {
                $group_id = $group['id'];
                $group_name = $group['name'];
                
                $fetched_data['monitor_groups'][$group_id] = array(
                    'name' => $group_name,
                    'monitors' => array(),
                    'uptime_percentage' => 0,
                );
                
                foreach ($group['monitorList'] as $monitor) {
                    $fetched_data['monitors'][] = array(
                        'id' => $monitor['id'],
                        'name' => $monitor['name'],
                        'group_id' => $group_id,
                    );
                    $fetched_data['total_monitors']++;
                    $fetched_data['monitor_groups'][$group_id]['monitors'][] = $monitor['id'];
                }
            }
        } else {
            $error_message = "Empty status page data received";
            $error_timestamp = current_time('mysql');
        }
    }

    // Fetch heartbeat data
    $heartbeat_url = $base_url . '/api/status-page/heartbeat/' . $slug;
    $heartbeat_response = wp_remote_get($heartbeat_url);

    if (is_wp_error($heartbeat_response)) {
        $error_message = "Heartbeat request failed: " . $heartbeat_response->get_error_message();
        $error_timestamp = current_time('mysql');
    } else {
        $heartbeat_data = json_decode(wp_remote_retrieve_body($heartbeat_response), true);
        
        // Process the heartbeat data
        if (!empty($heartbeat_data)) {
            $sites_up = 0;
            $sites_down = 0;
            $total_services = 0;
            
            foreach ($heartbeat_data['heartbeatList'] as $monitor_id => $site_list) {
                $last_heartbeat = end($site_list);
                if ($last_heartbeat && $last_heartbeat['status'] === 1) {
                    $sites_up++;
                } else {
                    $sites_down++;
                }
                $total_services++;
                
                // Update monitor-specific data
                foreach ($fetched_data['monitors'] as &$monitor) {
                    if ($monitor['id'] == $monitor_id) {
                        $monitor['status'] = $last_heartbeat['status'];
                        $monitor['ping'] = $last_heartbeat['ping'];
                        break;
                    }
                }
            }
            
            $fetched_data['sites_up'] = $sites_up;
            $fetched_data['sites_down'] = $sites_down;
            $fetched_data['total_services'] = $total_services;
            
            // Calculate uptime percentage for each monitor and group
            foreach ($heartbeat_data['uptimeList'] as $monitor_id => $uptime) {
                foreach ($fetched_data['monitors'] as &$monitor) {
                    if ($monitor['id'] == $monitor_id) {
                        $monitor['uptime_percentage'] = $uptime;
                        break;
                    }
                }
                
                foreach ($fetched_data['monitor_groups'] as &$group) {
                    if (in_array($monitor_id, $group['monitors'])) {
                        $group['uptime_percentage'] += $uptime;
                    }
                }
            }
            
            // Calculate average uptime percentage for each group
            foreach ($fetched_data['monitor_groups'] as &$group) {
                $group['uptime_percentage'] = count($group['monitors']) > 0 ? $group['uptime_percentage'] / count($group['monitors']) : 0;
            }
        } else {
            $error_message = "Empty heartbeat data received";
            $error_timestamp = current_time('mysql');
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}


/* function homelab_fetch_uptimekuma_data($url, $slug, $service_id) {
    $status_url = $url; //rtrim($url, '/') . '/status/' . $slug;
    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    $response = wp_remote_get($status_url);
    if (is_wp_error($response)) {
        $error_message = "Status page request failed: " . $response->get_error_message();
        $error_timestamp = current_time('mysql');
        homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
        return $fetched_data;
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);
    if (isset($data['monitors'])) {
        $fetched_data['total_services'] = count($data['monitors']);
        $status_counts = array(
            'up' => 0,
            'down' => 0,
        );
        $total_uptime_percentage = 0;

        foreach ($data['monitors'] as $monitor) {
            $status = strtolower($monitor['status']);
            if (isset($status_counts[$status])) {
                $status_counts[$status]++;
            }
            $total_uptime_percentage += $monitor['uptime'];
        }

        $fetched_data['services_up'] = $status_counts['up'];
        $fetched_data['services_down'] = $status_counts['down'];
        $fetched_data['avg_uptime_percentage'] = $fetched_data['total_services'] > 0 ? $total_uptime_percentage / $fetched_data['total_services'] : 0;
    }

    $fetched_data['incident_status'] = isset($data['incident']) && $data['incident'] !== null;

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
} */