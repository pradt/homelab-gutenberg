<?php

/**********
 * Audiobookshelf Data Collection
 * -------------------------------
 * This function collects statistics from the Audiobookshelf API for dashboard display, using an API Key for authentication.
 * It fetches counts and total durations for both podcasts and books.
 * 
 * Collected Data:
 * - Number of podcasts
 * - Total duration of all podcasts
 * - Number of books
 * - Total duration of all books
 * 
 * Data not collected but available for extension:
 * - Number of users and their activity
 * - Detailed genre or category statistics for books and podcasts
 * - Recent additions to the library
 * - Data on most listened books or podcasts
 * - Storage usage statistics
 * 
 * Authentication:
 * The function uses an API Key passed in the header for authentication.
 * 
 * Error Handling:
 * - Captures any errors encountered during the API request process.
 * - Stores error messages and timestamps for troubleshooting.
 ***********/

 function homelab_fetch_audiobookshelf_stats($api_url, $api_key, $service_id) {
    $headers = array(
        'headers' => array(
            'X-API-KEY' => $api_key,
        ),
    );

    $error_message = null;
    $error_timestamp = null;

    $stats = [
        'podcasts_count' => 0,
        'podcasts_duration' => 0,
        'books_count' => 0,
        'books_duration' => 0,
    ];

    // Fetch podcasts statistics
    $podcasts_response = wp_remote_get(rtrim($api_url, '/') . '/api/podcasts', $headers);
    if (is_wp_error($podcasts_response)) {
        $error_message = $podcasts_response->get_error_message();
        $error_timestamp = current_time('mysql');
    } elseif (wp_remote_retrieve_response_code($podcasts_response) !== 200) {
        $error_message = "API request for podcasts failed with status code: " . wp_remote_retrieve_response_code($podcasts_response);
        $error_timestamp = current_time('mysql');
    } else {
        $podcasts_data = json_decode(wp_remote_retrieve_body($podcasts_response), true);
        $stats['podcasts_count'] = count($podcasts_data);
        foreach ($podcasts_data as $podcast) {
            $stats['podcasts_duration'] += $podcast['duration'];
        }
    }

    // If there was an error with podcasts, don't proceed to fetch books
    if ($error_message === null) {
        // Fetch books statistics
        $books_response = wp_remote_get(rtrim($api_url, '/') . '/api/books', $headers);
        if (is_wp_error($books_response)) {
            $error_message = $books_response->get_error_message();
            $error_timestamp = current_time('mysql');
        } elseif (wp_remote_retrieve_response_code($books_response) !== 200) {
            $error_message = "API request for books failed with status code: " . wp_remote_retrieve_response_code($books_response);
            $error_timestamp = current_time('mysql');
        } else {
            $books_data = json_decode(wp_remote_retrieve_body($books_response), true);
            $stats['books_count'] = count($books_data);
            foreach ($books_data as $book) {
                $stats['books_duration'] += $book['duration'];
            }
        }
    }

    // Save the collected data, along with any error message or timestamp
    homelab_save_service_data($service_id, $stats, $error_message, $error_timestamp);

    return $stats;
}