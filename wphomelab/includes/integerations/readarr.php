<?php
/******************
* Readarr Data Collection
* ----------------------
* This function collects data from Readarr, a book management tool, for dashboard display.
* It fetches information about the books, authors, and reading progress.
*
* Collected Data:
* - Total number of books
* - Number of books by status (wanted, have, reading, completed)
* - Total number of authors
* - Recently added books
* - Upcoming book releases
*
* Data not collected but available for extension:
* - Detailed book information (title, author, publisher, publication date, genres)
* - Book ratings and reviews
* - Reading history and statistics
* - User-specific reading lists and preferences
* - Readarr configuration and settings
*
* Requirements:
* - Readarr API should be accessible via the provided API URL.
* - API authentication token (API key) may be required depending on Readarr configuration.
*
* Parameters:
* - $api_url: The base URL of the Readarr API.
* - $api_key: The API key for authentication (if required).
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*
* Example of fetched_data structure:
* {
*   "total_books": 100,
*   "books_wanted": 20,
*   "books_have": 50,
*   "books_reading": 10,
*   "books_completed": 20,
*   "total_authors": 50,
*   "recent_books": [
*     {
*       "title": "Book Title 1",
*       "author": "Author Name 1",
*       "added_date": "2023-06-01"
*     },
*     {
*       "title": "Book Title 2",
*       "author": "Author Name 2",
*       "added_date": "2023-06-02"
*     }
*   ],
*   "upcoming_releases": [
*     {
*       "title": "Upcoming Book 1",
*       "author": "Author Name 3",
*       "release_date": "2023-07-15"
*     },
*     {
*       "title": "Upcoming Book 2",
*       "author": "Author Name 4",
*       "release_date": "2023-08-01"
*     }
*   ]
* }
*******************/
function homelab_fetch_readarr_data($api_url, $api_key = '', $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'books' => '/api/v1/book',
        'authors' => '/api/v1/author',
        'wanted' => '/api/v1/wanted/missing',
        'recent' => '/api/v1/history',
        'upcoming' => '/api/v1/calendar',
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

        if ($key === 'books') {
            $fetched_data['total_books'] = count($data);
            $status_counts = array(
                'wanted' => 0,
                'have' => 0,
                'reading' => 0,
                'completed' => 0,
            );
            foreach ($data as $book) {
                $status = strtolower($book['status']);
                if (isset($status_counts[$status])) {
                    $status_counts[$status]++;
                }
            }
            $fetched_data['books_wanted'] = $status_counts['wanted'];
            $fetched_data['books_have'] = $status_counts['have'];
            $fetched_data['books_reading'] = $status_counts['reading'];
            $fetched_data['books_completed'] = $status_counts['completed'];
        } elseif ($key === 'authors') {
            $fetched_data['total_authors'] = count($data);
        } elseif ($key === 'recent') {
            $recent_books = array_slice($data, 0, 5); // Fetch the 5 most recent books
            $fetched_data['recent_books'] = array_map(function ($book) {
                return array(
                    'title' => $book['book']['title'],
                    'author' => $book['book']['author']['authorName'],
                    'added_date' => date('Y-m-d', strtotime($book['date'])),
                );
            }, $recent_books);
        } elseif ($key === 'upcoming') {
            $upcoming_releases = array_slice($data, 0, 5); // Fetch the next 5 upcoming releases
            $fetched_data['upcoming_releases'] = array_map(function ($book) {
                return array(
                    'title' => $book['book']['title'],
                    'author' => $book['book']['author']['authorName'],
                    'release_date' => date('Y-m-d', strtotime($book['releaseDate'])),
                );
            }, $upcoming_releases);
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}