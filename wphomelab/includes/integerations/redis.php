<?php
/******************
* Redis Data Collection
* ----------------------
* This function collects data from a Redis server for dashboard display.
* It fetches information about the Redis server's performance, memory usage, and key statistics.
*
* Collected Data:
* - Total number of keys in the Redis server
* - Memory used by the Redis server
* - CPU usage of the Redis server
* - Number of connected clients
* - Number of commands processed per second
* - Hit ratio (keyspace hits / keyspace misses)
* - Evicted keys due to maxmemory limit
* - Keyspace statistics (number of keys per database)
*
* Data not collected but available for extension:
* - Detailed information about each connected client
* - Slowlog entries for identifying slow commands
* - Replication status and lag
* - Redis configuration settings
* - Persistence (RDB/AOF) status and last save time
* - Pub/Sub statistics (channels, patterns, subscribers)
*
* Example fetched_data structure:
* {
*   "total_keys": 1000,
*   "used_memory": 2097152,
*   "used_cpu_sys": 0.5,
*   "used_cpu_user": 0.8,
*   "connected_clients": 10,
*   "instantaneous_ops_per_sec": 500,
*   "hit_ratio": 0.95,
*   "evicted_keys": 100,
*   "keyspace": {
*     "db0": {
*       "keys": 800
*     },
*     "db1": {
*       "keys": 200
*     }
*   }
* }
*
* Requirements:
* - Redis server should be accessible with the provided API URL.
* - Redis PHP extension (phpredis) should be installed.
*
* Parameters:
* - $api_url: The API URL for connecting to the Redis server, in the format "redis://hostname:port".
* - $password: The password for connecting to the Redis server (optional).
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the Redis connection and command execution process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_redis_data($api_url, $password = null, $service_id) {
    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    try {
        $redis = new Redis();
        $redis->connect($api_url);

        if ($password !== null) {
            $redis->auth($password);
        }

        $server_info = $redis->info();

        $fetched_data['total_keys'] = $redis->dbSize();
        $fetched_data['used_memory'] = $server_info['used_memory'];
        $fetched_data['used_cpu_sys'] = $server_info['used_cpu_sys'];
        $fetched_data['used_cpu_user'] = $server_info['used_cpu_user'];
        $fetched_data['connected_clients'] = $server_info['connected_clients'];
        $fetched_data['instantaneous_ops_per_sec'] = $server_info['instantaneous_ops_per_sec'];

        $keyspace_hits = $server_info['keyspace_hits'];
        $keyspace_misses = $server_info['keyspace_misses'];
        $fetched_data['hit_ratio'] = ($keyspace_hits + $keyspace_misses) > 0 ? ($keyspace_hits / ($keyspace_hits + $keyspace_misses)) : 0;

        $fetched_data['evicted_keys'] = $server_info['evicted_keys'];

        $fetched_data['keyspace'] = array();
        foreach ($server_info as $key => $value) {
            if (strpos($key, 'db') === 0 && strpos($key, 'keys') !== false) {
                $fetched_data['keyspace'][str_replace('keys=', '', $key)] = array(
                    'keys' => intval($value),
                );
            }
        }

        $redis->close();
    } catch (Exception $e) {
        $error_message = "Error collecting Redis data: " . $e->getMessage();
        $error_timestamp = current_time('mysql');
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}