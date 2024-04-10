<?php
/******************
* Postgres Data Collection
* ----------------------
* This function collects data from a Postgres server for dashboard display.
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
* - Postgres configuration settings
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
* - Postgres server should be accessible with the provided API URL, username, and password.
* - The "pg_stat_statements" extension should be enabled on the Postgres server for query performance metrics.
*
* Parameters:
* - $api_url: The API URL for connecting to the Postgres server.
* - $username: The username for connecting to the Postgres server.
* - $password: The password for connecting to the Postgres server.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the server connection and query execution process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_postgres_data($api_url, $username, $password, $service_id) {
    $fetched_data = array(
        'total_size' => 0,
        'database_count' => 0,
        'databases' => array(),
    );
    $error_message = null;
    $error_timestamp = null;

    $connection = pg_connect("host=$api_url user=$username password=$password");
    if (!$connection) {
        $error_message = "Server connection failed: " . pg_last_error();
        $error_timestamp = current_time('mysql');
        homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
        return $fetched_data;
    }

    // Fetch database names
    $databases = array();
    $result = pg_query("SELECT datname FROM pg_database WHERE datistemplate = false");
    while ($row = pg_fetch_assoc($result)) {
        $databases[] = $row['datname'];
    }
    $fetched_data['database_count'] = count($databases);

    foreach ($databases as $database) {
        pg_query("SET search_path TO $database");

        // Fetch database size
        $result = pg_query("SELECT pg_database_size('$database') AS size");
        $size = pg_fetch_assoc($result)['size'];
        $fetched_data['total_size'] += $size;

        // Fetch number of tables
        $result = pg_query("SELECT COUNT(*) AS table_count FROM information_schema.tables WHERE table_catalog = '$database' AND table_schema NOT IN ('information_schema', 'pg_catalog')");
        $table_count = pg_fetch_assoc($result)['table_count'];

        // Fetch average query execution time
        $result = pg_query("SELECT AVG(total_time) AS avg_query_time FROM pg_stat_statements");
        $avg_query_time = pg_fetch_assoc($result)['avg_query_time'];

        // Fetch number of slow queries
        $result = pg_query("SELECT COUNT(*) AS slow_query_count FROM pg_stat_statements WHERE total_time > 1000");
        $slow_query_count = pg_fetch_assoc($result)['slow_query_count'];

        // Fetch cache hit ratio
        $result = pg_query("SELECT SUM(blks_hit) / (SUM(blks_hit) + SUM(blks_read)) AS cache_hit_ratio FROM pg_statio_user_tables");
        $cache_hit_ratio = pg_fetch_assoc($result)['cache_hit_ratio'];

        $fetched_data['databases'][] = array(
            'name' => $database,
            'size' => $size,
            'table_count' => $table_count,
            'avg_query_time' => $avg_query_time,
            'slow_query_count' => $slow_query_count,
            'cache_hit_ratio' => $cache_hit_ratio,
        );
    }

    pg_close($connection);

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}