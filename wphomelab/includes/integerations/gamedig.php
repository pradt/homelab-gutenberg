<?php
/******************
 * GameDig Data Collection
 * -----------------------
 * This function collects data from a game server using the GameDig library for dashboard display.
 * It fetches information about the server status, player count, map, and other server-specific data.
 *
 * Collected Data:
 * - Server status (online/offline)
 * - Number of players online
 * - Maximum number of players allowed
 * - Current map name
 * - Server name
 * - Game type
 *
 * Data not collected but available for extension:
 * - Detailed player information (names, scores, duration, etc.)
 * - Server configuration and settings
 * - Server mods and plugins
 * - Server latency and performance metrics
 *
 * Requirements:
 * - The GameDig library must be installed and loaded.
 *
 * Parameters:
 * - $server_type: The type of the game server (e.g., "minecraft", "csgo", "tf2").
 * - $server_host: The hostname or IP address of the game server.
 * - $server_port: The port number of the game server.
 * - $service_id: The ID of the service being monitored.
 *
 * Error Handling:
 * - Captures any errors encountered during the server query process.
 * - Stores error messages and timestamps for troubleshooting.
 *******************/

 function homelab_fetch_gamedig_data($server_type, $server_host, $server_port, $service_id) {
    require_once 'path/to/GameDig/Browser.php';

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    try {
        $gamedig = new \GameQ\GameQ();
        $gamedig->addServer([
            'type' => $server_type,
            'host' => $server_host,
            'port' => $server_port,
        ]);

        $gamedig->setOption('timeout', 5);
        $results = $gamedig->process();

        if (isset($results[$server_host . ':' . $server_port])) {
            $server_data = $results[$server_host . ':' . $server_port];

            $fetched_data['server_status'] = $server_data['gq_online'] ? 'online' : 'offline';
            $fetched_data['num_players'] = $server_data['gq_numplayers'];
            $fetched_data['max_players'] = $server_data['gq_maxplayers'];
            $fetched_data['map_name'] = $server_data['gq_mapname'];
            $fetched_data['server_name'] = $server_data['gq_hostname'];
            $fetched_data['game_type'] = $server_data['gq_gametype'];
        } else {
            $error_message = "Server data not found for {$server_host}:{$server_port}";
            $error_timestamp = current_time('mysql');
        }
    } catch (Exception $e) {
        $error_message = "Error querying server: " . $e->getMessage();
        $error_timestamp = current_time('mysql');
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}