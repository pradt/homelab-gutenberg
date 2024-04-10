<?php
/******************
* Pterodactyl Data Collection
* ---------------------------
* This function collects data from Pterodactyl, a game server management panel, for dashboard display.
* It fetches information about the servers, including their status, resource usage, and player counts.
*
* Collected Data:
* - Total number of servers
* - Number of servers by status (running, stopped, starting, stopping)
* - Total CPU usage across all servers (percentage)
* - Total memory usage across all servers (percentage)
* - Total disk usage across all servers (percentage)
* - Total number of players online across all servers
*
* Data not collected but available for extension:
* - Detailed server information (name, description, game, location)
* - Server-specific resource usage (CPU, memory, disk)
* - Server configuration and settings
* - Player information (usernames, playtime)
* - Server backups and schedules
* - Pterodactyl user and permission management
*
* Requirements:
* - Pterodactyl API should be accessible via the provided API URL.
* - API authentication token (API key) is required for accessing the Pterodactyl API.
*
* Parameters:
* - $api_url: The base URL of the Pterodactyl API.
* - $api_key: The API key for authentication.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*
* Example of fetched_data structure:
* {
*   "total_servers": 10,
*   "servers_running": 8,
*   "servers_stopped": 2,
*   "servers_starting": 0,
*   "servers_stopping": 0,
*   "total_cpu_usage": 60.5,
*   "total_memory_usage": 70.2,
*   "total_disk_usage": 80.1,
*   "total_players_online": 100
* }
*******************/
function homelab_fetch_pterodactyl_data($api_url, $api_key, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'servers' => '/api/application/servers',
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

        if ($key === 'servers') {
            $fetched_data['total_servers'] = count($data['data']);
            $status_counts = array(
                'running' => 0,
                'stopped' => 0,
                'starting' => 0,
                'stopping' => 0,
            );
            $total_cpu_usage = 0;
            $total_memory_usage = 0;
            $total_disk_usage = 0;
            $total_players_online = 0;

            foreach ($data['data'] as $server) {
                $status = strtolower($server['attributes']['status']);
                if (isset($status_counts[$status])) {
                    $status_counts[$status]++;
                }
                $total_cpu_usage += $server['attributes']['resources']['cpu_absolute'];
                $total_memory_usage += $server['attributes']['resources']['memory_bytes'] / $server['attributes']['limits']['memory'];
                $total_disk_usage += $server['attributes']['resources']['disk_bytes'] / $server['attributes']['limits']['disk'];
                $total_players_online += count($server['attributes']['relationships']['users']['data']);
            }

            $fetched_data['servers_running'] = $status_counts['running'];
            $fetched_data['servers_stopped'] = $status_counts['stopped'];
            $fetched_data['servers_starting'] = $status_counts['starting'];
            $fetched_data['servers_stopping'] = $status_counts['stopping'];
            $fetched_data['total_cpu_usage'] = $fetched_data['total_servers'] > 0 ? $total_cpu_usage / $fetched_data['total_servers'] : 0;
            $fetched_data['total_memory_usage'] = $fetched_data['total_servers'] > 0 ? $total_memory_usage / $fetched_data['total_servers'] * 100 : 0;
            $fetched_data['total_disk_usage'] = $fetched_data['total_servers'] > 0 ? $total_disk_usage / $fetched_data['total_servers'] * 100 : 0;
            $fetched_data['total_players_online'] = $total_players_online;
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}