<?php
/**********************
 * MongoDB Data Collection
 * ----------------------
 * This function collects data from a MongoDB server for dashboard display.
 * It fetches information about the databases, collections, and their sizes.
 *
 * Collected Data:
 * - Total size of all databases
 * - Total number of databases
 * - Per-database statistics:
 *   - Database name
 *   - Database size
 *   - Number of collections
 *   - Average number of documents per collection
 *   - Average object size per collection
 *   - Average index size per collection
 *
 * Data not collected but available for extension:
 * - Detailed collection information (name, options, indexes)
 * - Document-level data and statistics
 * - User and role management details
 * - Replication and sharding configuration
 * - Query performance and optimization metrics
 *
 * Opportunities for additional data:
 * - Monitoring of slow queries and long-running operations
 * - Analysis of data growth and capacity planning
 * - Detection of schema changes and data anomalies
 * - Integration with backup and recovery processes
 * - Correlation with application-level metrics and logs
 *
 * Requirements:
 * - MongoDB server should be accessible with the provided API URL.
 * - MongoDB driver for PHP (mongodb extension) should be installed.
 * - Username and password for authentication (if required).
 *
 * Parameters:
 * - $api_url: The API URL for connecting to the MongoDB server.
 * - $username: The username for authentication (if required).
 * - $password: The password for authentication (if required).
 * - $service_id: The ID of the service being monitored.
 *
 * Error Handling:
 * - Captures any errors encountered during the MongoDB operations.
 * - Stores error messages and timestamps for troubleshooting.
 *
 * Example of fetched_data structure:
 * {
 *   "total_size": 1000000,
 *   "database_count": 3,
 *   "databases": [
 *     {
 *       "name": "database1",
 *       "size": 500000,
 *       "collection_count": 10,
 *       "avg_document_count": 1000,
 *       "avg_object_size": 1024,
 *       "avg_index_size": 2048
 *     },
 *     ...
 *   ]
 * }
 **********************/
function homelab_fetch_mongodb_data($api_url, $username = '', $password = '', $service_id) {
    $fetched_data = array(
        'total_size' => 0,
        'database_count' => 0,
        'databases' => array(),
    );
    $error_message = null;
    $error_timestamp = null;

    try {
        $options = array();
        if (!empty($username) && !empty($password)) {
            $options['username'] = $username;
            $options['password'] = $password;
        }

        $client = new MongoDB\Client($api_url, $options);
        $databases = $client->listDatabases();

        $fetched_data['database_count'] = count($databases);

        foreach ($databases as $database) {
            $db_name = $database->getName();
            $db = $client->selectDatabase($db_name);
            $stats = $db->command(['dbStats' => 1]);

            $size = $stats['dataSize'] + $stats['indexSize'];
            $fetched_data['total_size'] += $size;

            $collections = $db->listCollections();
            $collection_count = count($collections);

            $total_document_count = 0;
            $total_object_size = 0;
            $total_index_size = 0;

            foreach ($collections as $collection) {
                $stats = $db->command(['collStats' => $collection->getName()]);
                $total_document_count += $stats['count'];
                $total_object_size += $stats['size'];
                $total_index_size += $stats['totalIndexSize'];
            }

            $avg_document_count = $collection_count > 0 ? $total_document_count / $collection_count : 0;
            $avg_object_size = $collection_count > 0 ? $total_object_size / $collection_count : 0;
            $avg_index_size = $collection_count > 0 ? $total_index_size / $collection_count : 0;

            $fetched_data['databases'][] = array(
                'name' => $db_name,
                'size' => $size,
                'collection_count' => $collection_count,
                'avg_document_count' => $avg_document_count,
                'avg_object_size' => $avg_object_size,
                'avg_index_size' => $avg_index_size,
            );
        }
    } catch (Exception $e) {
        $error_message = "MongoDB data collection failed: " . $e->getMessage();
        $error_timestamp = current_time('mysql');
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}