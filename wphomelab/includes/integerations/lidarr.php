<?php
/******************
* Lidarr Data Collection
* ----------------------
* This function collects data from Lidarr, a music collection manager, for dashboard display.
* It fetches information about the monitored artists, albums, and tracks, including their status, release dates, and file formats.
*
* Collected Data:
* - Total number of monitored artists
* - Total number of monitored albums
* - Total number of monitored tracks
* - Number of artists by status (active, ended, deleted)
* - Number of albums by status (released, unreleased, deleted)
* - Number of tracks by file format (MP3, FLAC, AAC, etc.)
*
* Data not collected but available for extension:
* - Detailed artist information (name, overview, genres, images)
* - Detailed album information (title, release date, tracks, cover art)
* - Detailed track information (title, duration, bit rate, size)
* - Artist and album metadata (MusicBrainz IDs, last.fm URLs)
* - Album and track file paths and sizes
* - Lidarr configuration and settings
*
* Example of fetched_data structure:
* {
*   "total_artists": 50,
*   "total_albums": 200,
*   "total_tracks": 2500,
*   "artists_active": 45,
*   "artists_ended": 5,
*   "artists_deleted": 0,
*   "albums_released": 180,
*   "albums_unreleased": 20,
*   "albums_deleted": 0,
*   "tracks_mp3": 1800,
*   "tracks_flac": 500,
*   "tracks_aac": 200
* }
*
* Requirements:
* - Lidarr API should be accessible via the provided API URL.
* - API authentication token (API key) may be required depending on Lidarr configuration.
*
* Parameters:
* - $api_url: The base URL of the Lidarr API.
* - $api_key: The API key for authentication (if required).
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_lidarr_data($api_url, $api_key = '', $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'artists' => '/api/v1/artist',
        'albums' => '/api/v1/album',
        'tracks' => '/api/v1/track',
    );
    
    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;
    
    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
        );
        
        if (!empty($api_key)) {
            $args['headers']['X-Api-Key'] = $api_key;
        }
        
        $response = wp_remote_get($url, $args);
        
        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($key === 'artists') {
            $fetched_data['total_artists'] = count($data);
            
            $status_counts = array(
                'active' => 0,
                'ended' => 0,
                'deleted' => 0,
            );
            
            foreach ($data as $artist) {
                $status = strtolower($artist['status']);
                if (isset($status_counts[$status])) {
                    $status_counts[$status]++;
                }
            }
            
            $fetched_data['artists_active'] = $status_counts['active'];
            $fetched_data['artists_ended'] = $status_counts['ended'];
            $fetched_data['artists_deleted'] = $status_counts['deleted'];
        } elseif ($key === 'albums') {
            $fetched_data['total_albums'] = count($data);
            
            $status_counts = array(
                'released' => 0,
                'unreleased' => 0,
                'deleted' => 0,
            );
            
            foreach ($data as $album) {
                $status = strtolower($album['status']);
                if (isset($status_counts[$status])) {
                    $status_counts[$status]++;
                }
            }
            
            $fetched_data['albums_released'] = $status_counts['released'];
            $fetched_data['albums_unreleased'] = $status_counts['unreleased'];
            $fetched_data['albums_deleted'] = $status_counts['deleted'];
        } elseif ($key === 'tracks') {
            $fetched_data['total_tracks'] = count($data);
            
            $format_counts = array(
                'mp3' => 0,
                'flac' => 0,
                'aac' => 0,
            );
            
            foreach ($data as $track) {
                $format = strtolower($track['media_format']);
                if (isset($format_counts[$format])) {
                    $format_counts[$format]++;
                }
            }
            
            $fetched_data['tracks_mp3'] = $format_counts['mp3'];
            $fetched_data['tracks_flac'] = $format_counts['flac'];
            $fetched_data['tracks_aac'] = $format_counts['aac'];
        }
    }
    
    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    
    return $fetched_data;
}