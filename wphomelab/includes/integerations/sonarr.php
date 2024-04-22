<?
/**
 * Sonarr Data Collection
 * ----------------------
 * This function collects data from Sonarr, a media management and automation tool, for dashboard display.
 * It fetches information about the monitored TV shows, including their status, episode counts, and quality profiles.
 *
 * Collected Data:
 * - Total number of monitored TV shows
 * - Number of shows by status (continuing, ended, upcoming)
 * - Total number of episodes
 * - Number of downloaded episodes
 * - Number of missing episodes
 * - Quality profile distribution
 *
 * Data not collected but available for extension:
 * - Detailed show information (name, overview, network, genre, rating)
 * - Episode-specific details (title, air date, file size, download status)
 * - Calendar data (upcoming episodes, air dates)
 * - Disk space usage and availability
 * - Download client integration and status
 * - Sonarr configuration and settings
 *
 * Example of fetched_data structure:
 * {
 *   "total_shows": 50,
 *   "shows_continuing": 30,
 *   "shows_ended": 15,
 *   "shows_upcoming": 5,
 *   "total_episodes": 1000,
 *   "episodes_downloaded": 800,
 *   "episodes_missing": 150,
 *   "quality_profiles": {
 *     "1": 25,
 *     "2": 20,
 *     "3": 5
 *   }
 * }
 *
 * Requirements:
 * - Sonarr API should be accessible via the provided API URL.
 * - API authentication token (API key) is required for accessing the Sonarr API.
 *
 * Parameters:
 * - $api_url: The base URL of the Sonarr API.
 * - $api_key: The API key for authentication.
 * - $service_id: The ID of the service being monitored.
 *
 * Error Handling:
 * - Captures any errors encountered during the API request process.
 * - Stores error messages and timestamps for troubleshooting.
 */
function homelab_fetch_sonarr_data($api_url, $api_key, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'series' => '/api/v3/series',
        'wanted' => '/api/v3/wanted/missing',
    );
    
    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;
    
    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $args = array(
            'headers' => array(
                'X-Api-Key' => $api_key,
            ),
        );
        
        $response = wp_remote_get($url, $args);
        
        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            error_log($error_message);
            continue;
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);

        if (!is_array($data)) {
            $error_message = "Invalid response data for endpoint '{$key}'. Expected an array.";
            $error_timestamp = current_time('mysql');
            error_log($error_message . ' Response data: ' . print_r($data, true));
            continue;
        }
        
        if ($key === 'series') {
            $fetched_data['total_shows'] = count($data);
            
            $status_counts = array(
                'continuing' => 0,
                'ended' => 0,
                'upcoming' => 0,
            );
            
            $quality_profiles = array();

            $fetched_data['total_episodes'] = 0;
            $fetched_data['episodes_downloaded'] = 0;
            
            foreach ($data as $show) {
                $status = strtolower($show['status']);
                if (isset($status_counts[$status])) {
                    $status_counts[$status]++;
                }
                
                $quality_profile = $show['qualityProfileId'];
                if (isset($quality_profiles[$quality_profile])) {
                    $quality_profiles[$quality_profile]++;
                } else {
                    $quality_profiles[$quality_profile] = 1;
                }
            }
            
            $fetched_data['shows_continuing'] = $status_counts['continuing'];
            $fetched_data['shows_ended'] = $status_counts['ended'];
            $fetched_data['shows_upcoming'] = $status_counts['upcoming'];
            $fetched_data['quality_profiles'] = $quality_profiles;

            $fetched_data['total_episodes'] = 0;
            $fetched_data['episodes_downloaded'] = 0;

            foreach ($data as $show) {
                if (isset($show['statistics']['totalEpisodeCount'])) {
                    $fetched_data['total_episodes'] += $show['statistics']['totalEpisodeCount'];
                }
                
                if (isset($show['statistics']['episodeFileCount'])) {
                    $fetched_data['episodes_downloaded'] += $show['statistics']['episodeFileCount'];
                }
            }
            
            /* // Calculate total episodes and downloaded episodes
            $fetched_data['total_episodes'] = array_sum(array_column($data, 'statistics')['totalEpisodeCount']);
            $fetched_data['episodes_downloaded'] = array_sum(array_column($data, 'statistics')['episodeFileCount']); */
        }
        
        if ($key === 'wanted') {
            $fetched_data['episodes_missing'] = count($data['records']);
        }
    }
    
    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    
    return $fetched_data;
}