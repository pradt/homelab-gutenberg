<?php
/******************
* Navidrome Data Collection
* -------------------------
* This function collects data from Navidrome, a music streaming server, for dashboard display.
* It fetches information about the user's music library, including the total number of artists, albums, tracks, and playlists.
*
* Collected Data:
* - Total number of artists
* - Total number of albums
* - Total number of tracks
* - Total number of playlists
*
* Data not collected but available for extension:
* - Detailed artist information (name, genre, bio)
* - Detailed album information (title, artist, year, genre)
* - Detailed track information (title, artist, album, duration)
* - Detailed playlist information (name, owner, track count)
* - User listening statistics (most played artists, albums, tracks)
* - User favorites (favorite artists, albums, tracks)
* - Recently added artists, albums, and tracks
* - Navidrome server status and health metrics
*
* Example of fetched_data structure:
* {
*   "total_artists": 150,
*   "total_albums": 500,
*   "total_tracks": 5000,
*   "total_playlists": 20
* }
*
* Requirements:
* - Navidrome API should be accessible via the provided API URL.
* - API authentication token (API key), username, and salt are required for authentication.
*
* Parameters:
* - $api_url: The base URL of the Navidrome API.
* - $api_key: The API key for authentication.
* - $username: The username for authentication.
* - $salt: The salt value for authentication.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_navidrome_data($api_url, $api_key, $username, $salt, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'artists' => '/rest/getArtists',
        'albums' => '/rest/getAlbums',
        'tracks' => '/rest/getTracks',
        'playlists' => '/rest/getPlaylists',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    $token = md5($username . $salt);
    $auth_header = 'Basic ' . base64_encode($username . ':' . $token);

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-ND-APIKey' => $api_key,
                'Authorization' => $auth_header,
            ),
        );

        $response = wp_remote_get($url, $args);
        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        if ($key === 'artists') {
            $fetched_data['total_artists'] = count($data);
        } elseif ($key === 'albums') {
            $fetched_data['total_albums'] = count($data);
        } elseif ($key === 'tracks') {
            $fetched_data['total_tracks'] = count($data);
        } elseif ($key === 'playlists') {
            $fetched_data['total_playlists'] = count($data);
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}