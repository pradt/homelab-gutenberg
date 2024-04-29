<?php
/******************
* Prowlarr Data Collection
* ------------------------
* This function collects data from Prowlarr, an indexer manager for Usenet and BitTorrent, for dashboard display.
* It fetches information about the indexers managed by Prowlarr, including their status, number of releases, and download statistics.
*
* Collected Data:
* - Total number of indexers
* - Number of enabled indexers
* - Number of disabled indexers
* - Total number of releases across all indexers
* - Total number of downloads (grab count) across all indexers
*
* Example of fetched_data structure:
* {
*   "total_indexers": 10,
*   "enabled_indexers": 8,
*   "disabled_indexers": 2,
*   "total_releases": 5000,
*   "total_downloads": 1000
* }
*
* Data not collected but available for extension:
* - Detailed information about each indexer (name, URL, type)
* - Indexer-specific release and download statistics
* - Search performance metrics (success rate, average response time)
* - Indexer health status (last check time, error messages)
* - Prowlarr configuration and settings
*
* Other opportunities for data collection:
* - Top indexed categories or tags
* - Trend analysis of release and download activity over time
* - Integration with download clients for end-to-end monitoring
*
* Requirements:
* - Prowlarr API should be accessible via the provided API URL.
* - API authentication token (API key) is required for accessing the Prowlarr API.
*
* Parameters:
* - $api_url: The base URL of the Prowlarr API.
* - $api_key: The API key for authentication.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_prowlarr_data($api_url, $api_key, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'indexers' => '/api/v1/indexer',
        'indexerstats' => '/api/v1/indexerstats',
        'indexerstatus' => '/api/v1/indexerstatus',
    );
    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-Api-Key' => $api_key,
            ),
        );

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'indexers') {
            $fetched_data['total_indexers'] = count($data);
            $fetched_data['enabled_indexers'] = count(array_filter($data, function ($indexer) {
                return $indexer['enable'] === true;
            }));
            $fetched_data['disabled_indexers'] = count(array_filter($data, function ($indexer) {
                return $indexer['enable'] === false;
            }));
        }

        if ($key === 'indexerstats') {
            $total_queries = array_sum(array_column($data, 'queries'));
            $fetched_data['total_queries'] = $total_queries;
        }

        if ($key === 'indexerstatus') {
            $fetched_data['indexer_errors'] = array_sum(array_column($data, 'errorCount'));
            $fetched_data['indexer_warnings'] = array_sum(array_column($data, 'warningCount'));
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}