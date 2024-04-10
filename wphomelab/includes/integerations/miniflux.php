<?php
/******************
* Miniflux 2 Data Collection
* ----------------------
* This function collects data from Miniflux 2, a self-hosted RSS reader, for dashboard display.
* It fetches information about the user's feeds, entries, and reading stats.
*
* Collected Data:
* - Total number of feeds
* - Total number of unread entries
* - Total number of read entries
* - Total number of starred entries
* - Recent entries (configurable limit)
*
* Data not collected but available for extension:
* - Detailed feed information (title, URL, site URL, last update)
* - Entry details (title, URL, content, author, publication date)
* - Feed and entry categories/tags
* - User preferences and settings
* - Feed refresh status and error details
* - API usage statistics
*
* Opportunities for additional data:
* - Feed update frequency and latency
* - Most active/popular feeds based on entry count
* - Reading trends and patterns over time
* - Personalized feed recommendations
* - Integration with external services (e.g., bookmarking, sharing)
*
* Requirements:
* - Miniflux 2 API should be accessible via the provided API URL.
* - API authentication token (API key) is required for accessing the API.
*
* Parameters:
* - $api_url: The base URL of the Miniflux 2 API.
* - $api_key: The API key for authentication.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*
* Example of fetched_data structure:
* {
*   "total_feeds": 25,
*   "unread_entries": 100,
*   "read_entries": 500,
*   "starred_entries": 50,
*   "recent_entries": [
*     {
*       "id": 1234,
*       "title": "Example Entry",
*       "url": "https://example.com/entry",
*       "published_at": "2023-06-01T12:00:00Z"
*     },
*     ...
*   ]
* }
*******************/
function homelab_fetch_miniflux_data($api_url, $api_key, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'feeds' => '/v1/feeds',
        'entries' => '/v1/entries',
        'entries_unread' => '/v1/entries?status=unread',
        'entries_read' => '/v1/entries?status=read',
        'entries_starred' => '/v1/entries?starred=true',
    );

    $fetched_data = array(
        'total_feeds' => 0,
        'unread_entries' => 0,
        'read_entries' => 0,
        'starred_entries' => 0,
        'recent_entries' => array(),
    );
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-Auth-Token' => $api_key,
            ),
        );

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'feeds') {
            $fetched_data['total_feeds'] = count($data);
        } elseif ($key === 'entries_unread') {
            $fetched_data['unread_entries'] = count($data['entries']);
        } elseif ($key === 'entries_read') {
            $fetched_data['read_entries'] = count($data['entries']);
        } elseif ($key === 'entries_starred') {
            $fetched_data['starred_entries'] = count($data['entries']);
        } elseif ($key === 'entries') {
            $recent_entries_limit = 5; // Adjust the limit as needed
            $fetched_data['recent_entries'] = array_slice($data['entries'], 0, $recent_entries_limit);
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}