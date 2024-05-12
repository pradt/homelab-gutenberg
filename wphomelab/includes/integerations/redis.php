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

    // Check if the Redis extension is loaded
    if (!extension_loaded('redis')) {
        // Set the error message and log the error
        $error_message = "Redis extension is not available. Please install and enable the Redis extension.";
        $error_timestamp = current_time('mysql');
        error_log("Redis Error: " . $error_message);

        // Add the error message to fetched_data
        $fetched_data['error'] = $error_message;

        // Save the fetched data and error details to the database
        homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

        // Return the fetched data without executing the remaining code
        return $fetched_data;
    }

    try {
        // Create a new Redis instance and connect to the specified URL
        $redis = new Redis();
        #$redis->connect($api_url);

        // Split the API URL into IP address and port
        list($host, $port) = explode(':', $api_url, 2);
        
        // Connect to Redis using the extracted host and port
        $redis->connect($host, $port);

        // Authenticate with the Redis server if a password is provided
        if ($password !== null) {
            $redis->auth($password);
        }

        // Fetch the Redis server information
        $server_info = $redis->info();
        $fetched_data['raw_response'] = $server_info;

        // Extract and store the total number of keys if available
        if (isset($server_info['db0'])) {
            $fetched_data['total_keys'] = intval($server_info['db0']);
        }

        // Extract and store the used memory if available
        if (isset($server_info['used_memory'])) {
            $fetched_data['used_memory'] = $server_info['used_memory'];
        }

        // Extract and store the used CPU system time if available
        if (isset($server_info['used_cpu_sys'])) {
            $fetched_data['used_cpu_sys'] = $server_info['used_cpu_sys'];
        }

        // Extract and store the used CPU user time if available
        if (isset($server_info['used_cpu_user'])) {
            $fetched_data['used_cpu_user'] = $server_info['used_cpu_user'];
        }

        // Extract and store the number of connected clients if available
        if (isset($server_info['connected_clients'])) {
            $fetched_data['connected_clients'] = $server_info['connected_clients'];
        }

        // Extract and store the instantaneous operations per second if available
        if (isset($server_info['instantaneous_ops_per_sec'])) {
            $fetched_data['instantaneous_ops_per_sec'] = $server_info['instantaneous_ops_per_sec'];
        }

        // Calculate the hit ratio based on keyspace hits and misses
        $keyspace_hits = isset($server_info['keyspace_hits']) ? $server_info['keyspace_hits'] : 0;
        $keyspace_misses = isset($server_info['keyspace_misses']) ? $server_info['keyspace_misses'] : 0;
        $fetched_data['hit_ratio'] = ($keyspace_hits + $keyspace_misses) > 0 ? ($keyspace_hits / ($keyspace_hits + $keyspace_misses)) : 0;

        // Extract and store the number of evicted keys if available
        if (isset($server_info['evicted_keys'])) {
            $fetched_data['evicted_keys'] = $server_info['evicted_keys'];
        }

        // Extract and store the keyspace information for each database
        $fetched_data['keyspace'] = array();
        foreach ($server_info as $key => $value) {
            if (strpos($key, 'db') === 0 && strpos($key, 'keys') !== false) {
                $db_index = str_replace('db', '', $key);
                $db_index = str_replace('keys', '', $db_index);
                $fetched_data['keyspace'][$db_index] = array(
                    'keys' => intval($value),
                );
            }
        }

        // Close the Redis connection
        $redis->close();
    } catch (Exception $e) {
        // Set the error message and log the error if an exception occurs
        $error_message = "Error collecting Redis data: " . $e->getMessage();
        $error_timestamp = current_time('mysql');
        error_log("Redis Error: " . $error_message);
    }

    // Save the fetched data and error details (if any) to the database
    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    // Return the fetched data
    return $fetched_data;
}