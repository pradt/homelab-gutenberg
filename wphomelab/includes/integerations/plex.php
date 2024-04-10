<?php
/******************
* Plex Data Collection
* ----------------------
* This function collects data from Plex, a media server software, for dashboard display.
* It fetches information about the Plex server, libraries, and recently added media.
*
* Collected Data:
* - Server information (name, version, platform, uptime)
* - Total number of libraries
* - Library counts by type (movie, show, music, photo)
* - Recently added media (movies, shows, albums, photos)
*
* Data not collected but available for extension:
* - Detailed library information (name, size, item counts)
* - User and device information
* - Currently playing media and sessions
* - Media metadata (titles, descriptions, ratings, genres)
* - Transcoding and streaming performance metrics
* - Plex server settings and configurations
*
* Requirements:
* - Plex server should be accessible via the provided API URL.
* - API authentication token (API key) is required for accessing the Plex API.
*
* Parameters:
* - $api_url: The base URL of the Plex API.
* - $api_key: The API key for authentication.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*
* Example JSON structure of fetched_data:
* {
*   "server_name": "My Plex Server",
*   "server_version": "1.24.5.5173",
*   "server_platform": "Linux",
*   "server_uptime": 3600,
*   "total_libraries": 4,
*   "library_counts": {
*     "movie": 1,
*     "show": 1,
*     "music": 1,
*     "photo": 1
*   },
*   "recently_added": {
*     "movies": [
*       {
*         "title": "Movie 1",
*         "year": 2021,
*         "rating": 7.8
*       },
*       ...
*     ],
*     "shows": [
*       {
*         "title": "TV Show 1",
*         "season": 2,
*         "episode": 5
*       },
*       ...
*     ],
*     "albums": [
*       {
*         "title": "Album 1",
*         "artist": "Artist 1",
*         "year": 2020
*       },
*       ...
*     ],
*     "photos": [
*       {
*         "title": "Photo 1",
*         "album": "Album 1",
*         "timestamp": 1621234567
*       },
*       ...
*     ]
*   }
* }
*******************/
function homelab_fetch_plex_data($api_url, $api_key, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'server_info' => '/status/sessions',
        'libraries' => '/library/sections',
        'recently_added' => '/library/recentlyAdded',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $args = array(
            'headers' => array(
                'Accept' => 'application/json',
                'X-Plex-Token' => $api_key,
            ),
        );

        $response = wp_remote_get($url, $args);
        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'server_info') {
            $fetched_data['server_name'] = $data['MediaContainer']['friendlyName'];
            $fetched_data['server_version'] = $data['MediaContainer']['version'];
            $fetched_data['server_platform'] = $data['MediaContainer']['platform'];
            $fetched_data['server_uptime'] = $data['MediaContainer']['machineIdentifier'];
        } elseif ($key === 'libraries') {
            $fetched_data['total_libraries'] = count($data['MediaContainer']['Directory']);
            $library_counts = array(
                'movie' => 0,
                'show' => 0,
                'music' => 0,
                'photo' => 0,
            );
            foreach ($data['MediaContainer']['Directory'] as $library) {
                $type = strtolower($library['type']);
                if (isset($library_counts[$type])) {
                    $library_counts[$type]++;
                }
            }
            $fetched_data['library_counts'] = $library_counts;
        } elseif ($key === 'recently_added') {
            $recently_added = array(
                'movies' => array(),
                'shows' => array(),
                'albums' => array(),
                'photos' => array(),
            );
            foreach ($data['MediaContainer']['Metadata'] as $item) {
                if ($item['type'] === 'movie') {
                    $recently_added['movies'][] = array(
                        'title' => $item['title'],
                        'year' => $item['year'],
                        'rating' => $item['rating'],
                    );
                } elseif ($item['type'] === 'episode') {
                    $recently_added['shows'][] = array(
                        'title' => $item['grandparentTitle'],
                        'season' => $item['parentIndex'],
                        'episode' => $item['index'],
                    );
                } elseif ($item['type'] === 'track') {
                    $recently_added['albums'][] = array(
                        'title' => $item['parentTitle'],
                        'artist' => $item['grandparentTitle'],
                        'year' => $item['parentYear'],
                    );
                } elseif ($item['type'] === 'photo') {
                    $recently_added['photos'][] = array(
                        'title' => $item['title'],
                        'album' => $item['parentTitle'],
                        'timestamp' => $item['addedAt'],
                    );
                }
            }
            $fetched_data['recently_added'] = $recently_added;
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}