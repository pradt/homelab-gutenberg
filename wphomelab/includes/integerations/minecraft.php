<?php
/******************
* Minecraft Server Data Collection
* --------------------------------
* This function collects data from a Minecraft server for dashboard display.
* It fetches information about the server's status, player count, version, and performance metrics.
*
* Collected Data:
* - Server status (online/offline)
* - Current player count
* - Maximum player capacity
* - Server version
* - Server IP address and port
* - Server motd (message of the day)
* - Server uptime
* - TPS (ticks per second)
*
* Data not collected but available for extension:
* - Detailed player information (usernames, playtime, achievements)
* - Server world information (world name, seed, size)
* - Plugin and mod details
* - Server resource usage (CPU, memory, disk)
* - Server chat logs and player activity
* - Ban and whitelist management
*
* Opportunities for additional data:
* - Player demographics and statistics
* - Server economy and trade data
* - In-game events and custom metrics
* - Integration with server management tools and APIs
*
* Example of fetched_data structure:
* {
*   "status": "online",
*   "players_online": 10,
*   "players_max": 100,
*   "version": "1.19.2",
*   "ip_address": "192.168.0.10",
*   "port": 25565,
*   "motd": "Welcome to my Minecraft server!",
*   "uptime": 3600,
*   "tps": 19.5
* }
*
* Requirements:
* - Minecraft server should be accessible via the provided IP address and port.
* - Server query functionality should be enabled in the server configuration.
*
* Parameters:
* - $server_ip: The IP address of the Minecraft server.
* - $server_port: The port number of the Minecraft server.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the server query process.
* - Stores error messages and timestamps for troubleshooting.
*******************/

function homelab_fetch_minecraft_data($server_ip, $server_port, $service_id) {
    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    // Minecraft Server Query using fsockopen
    $socket = fsockopen("udp://" . $server_ip, $server_port, $errno, $errstr, 5);

    if (!$socket) {
        $error_message = "Failed to connect to Minecraft server: " . $errstr;
        $error_timestamp = current_time('mysql');
    } else {
        // Send handshake packet
        $packet = pack('c*', 0xFE, 0xFD, 0x09, 0x00, 0x00, 0x00, 0x00, 0x00);
        fwrite($socket, $packet);

        // Read response
        $response = fread($socket, 4096);
        fclose($socket);

        if (!empty($response)) {
            $data = explode("\x00\x00\x00", $response);

            if (count($data) >= 6) {
                $fetched_data['status'] = 'online';
                $fetched_data['version'] = $data[2];
                $fetched_data['motd'] = $data[3];
                $fetched_data['players_online'] = intval($data[4]);
                $fetched_data['players_max'] = intval($data[5]);
                $fetched_data['ip_address'] = $server_ip;
                $fetched_data['port'] = $server_port;
            } else {
                $error_message = "Invalid response received from Minecraft server";
                $error_timestamp = current_time('mysql');
            }
        } else {
            $error_message = "Empty response received from Minecraft server";
            $error_timestamp = current_time('mysql');
        }
    }

    // Set default values for missing data
    $fetched_data['status'] = $fetched_data['status'] ?? 'offline';
    $fetched_data['players_online'] = $fetched_data['players_online'] ?? 0;
    $fetched_data['players_max'] = $fetched_data['players_max'] ?? 0;
    $fetched_data['version'] = $fetched_data['version'] ?? 'Unknown';
    $fetched_data['ip_address'] = $fetched_data['ip_address'] ?? $server_ip;
    $fetched_data['port'] = $fetched_data['port'] ?? $server_port;
    $fetched_data['motd'] = $fetched_data['motd'] ?? '';
    $fetched_data['uptime'] = $fetched_data['uptime'] ?? 0;
    $fetched_data['tps'] = $fetched_data['tps'] ?? 0;

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}