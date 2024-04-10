<?php
/******************
* Tautulli Data Collection
* ----------------------
* This function collects data from Tautulli, a media server monitoring and analytics tool, for dashboard display.
* It fetches information about the media server, including current activity, library statistics, and user details.
*
* Collected Data:
* - Server status (up or down)
* - Current number of active streams
* - Total number of movies and TV shows in the library
* - Total number of users
* - Most popular movie and TV show
* - Most active user
*
* Data not collected but available for extension:
* - Detailed information about active streams (user, media, progress, transcode status)
* - Recently added media
* - Library growth over time
* - User watch statistics and history
* - Transcode performance and quality settings
* - Tautulli configuration and settings
*
* Example of fetched_data structure:
* {
*   "server_status": "up",
*   "active_streams": 2,
*   "total_movies": 500,
*   "total_tv_shows": 100,
*   "total_users": 20,
*   "most_popular_movie": "The Avengers",
*   "most_popular_tv_show": "Game of Thrones",
*   "most_active_user": "john_doe"
* }
*
* Requirements:
* - Tautulli API should be accessible via the provided API URL.
* - API key is required for authentication.
*
* Parameters:
* - $api_url: The base URL of the Tautulli API.
* - $api_key: The API key for authentication.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_tautulli_data($api_url, $api_key, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'server_status' => '/api/v2?apikey=' . $api_key . '&cmd=status',
        'library_stats' => '/api/v2?apikey=' . $api_key . '&cmd=get_library_stats',
        'user_stats' => '/api/v2?apikey=' . $api_key . '&cmd=get_user_stats',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'server_status') {
            $fetched_data['server_status'] = $data['response']['result'] === 'success' ? 'up' : 'down';
            $fetched_data['active_streams'] = $data['response']['data']['stream_count'];
        } elseif ($key === 'library_stats') {
            $fetched_data['total_movies'] = $data['response']['data']['movie_count'];
            $fetched_data['total_tv_shows'] = $data['response']['data']['show_count'];
            $fetched_data['most_popular_movie'] = $data['response']['data']['most_played_movie']['title'];
            $fetched_data['most_popular_tv_show'] = $data['response']['data']['most_played_tv']['title'];
        } elseif ($key === 'user_stats') {
            $fetched_data['total_users'] = count($data['response']['data']);
            $fetched_data['most_active_user'] = $data['response']['data'][0]['friendly_name'];
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}