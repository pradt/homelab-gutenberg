<?php
/******************
* MySQL Data Collection
* ----------------------
* This function collects data from a MySQL server for dashboard display.
* It fetches information about all databases on the server, including their sizes, table counts, and performance metrics.
*
* Collected Data:
* - Total size of all databases in bytes
* - Number of databases on the server
* - Per-database metrics:
*   - Database name
*   - Database size in bytes
*   - Number of tables in the database
*   - Average query execution time
*   - Number of slow queries
*   - Cache hit ratio
*
* Data not collected but available for extension:
* - Detailed table information for each database
* - Index usage and performance statistics per database
* - User and connection statistics
* - Replication status and lag
* - MySQL configuration settings
*
* Example fetched_data structure:
* {
*   "total_size": 1073741824,
*   "database_count": 5,
*   "databases": [
*     {
*       "name": "database1",
*       "size": 524288000,
*       "table_count": 10,
*       "avg_query_time": 0.5,
*       "slow_query_count": 5,
*       "cache_hit_ratio": 0.95
*     },
*     {
*       "name": "database2",
*       "size": 262144000,
*       "table_count": 8,
*       "avg_query_time": 0.3,
*       "slow_query_count": 2,
*       "cache_hit_ratio": 0.98
*     }
*   ]
* }
*
* Requirements:
* - MySQL server should be accessible with the provided API URL, username, and password.
*
* Parameters:
* - $api_url: The API URL for connecting to the MySQL server.
* - $username: The username for connecting to the MySQL server.
* - $password: The password for connecting to the MySQL server.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the server connection and query execution process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_mysql_data($api_url, $username, $password, $service_id) {
    $fetched_data = array(
        'total_size' => 0,
        'database_count' => 0,
        'databases' => array(),
    );
    $error_message = null;
    $error_timestamp = null;

    $connection = new mysqli($api_url, $username, $password);
    if ($connection->connect_error) {
        $error_message = "Server connection failed: " . $connection->connect_error;
        $error_timestamp = current_time('mysql');
        homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
        return $fetched_data;
    }

    // Fetch database names
    $databases = array();
    $result = $connection->query("SELECT schema_name FROM information_schema.schemata WHERE schema_name NOT IN ('information_schema', 'mysql', 'performance_schema')");
    while ($row = $result->fetch_assoc()) {
        $databases[] = $row['schema_name'];
    }
    $fetched_data['database_count'] = count($databases);

    foreach ($databases as $database) {
        $connection->select_db($database);

        // Fetch database size
        $result = $connection->query("SELECT SUM(data_length + index_length) AS size FROM information_schema.tables WHERE table_schema = '$database'");
        $row = $result->fetch_assoc();
        $size = $row['size'];
        $fetched_data['total_size'] += $size;

        // Fetch number of tables
        $result = $connection->query("SELECT COUNT(*) AS table_count FROM information_schema.tables WHERE table_schema = '$database'");
        $row = $result->fetch_assoc();
        $table_count = $row['table_count'];

        // Fetch average query execution time
        $result = $connection->query("SHOW STATUS LIKE 'Queries'");
        $queries = $result->fetch_assoc()['Value'];
        $result = $connection->query("SHOW STATUS LIKE 'Uptime'");
        $uptime = $result->fetch_assoc()['Value'];
        $avg_query_time = $uptime > 0 ? $queries / $uptime : 0;

        // Fetch number of slow queries
        $result = $connection->query("SHOW STATUS LIKE 'Slow_queries'");
        $slow_query_count = $result->fetch_assoc()['Value'];

        // Fetch cache hit ratio
        $result = $connection->query("SHOW STATUS LIKE 'Qcache_hits'");
        $qcache_hits = $result->fetch_assoc()['Value'];
        $result = $connection->query("SHOW STATUS LIKE 'Qcache_inserts'");
        $qcache_inserts = $result->fetch_assoc()['Value'];
        $cache_hit_ratio = $qcache_inserts > 0 ? $qcache_hits / $qcache_inserts : 0;

        $fetched_data['databases'][] = array(
            'name' => $database,
            'size' => $size,
            'table_count' => $table_count,
            'avg_query_time' => $avg_query_time,
            'slow_query_count' => $slow_query_count,
            'cache_hit_ratio' => $cache_hit_ratio,
        );
    }

    $connection->close();

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}