<?php
/******************
 * Calibre-web Data Collection
 * -------------------------------
 * This function collects statistics from the Calibre-web API for dashboard display, using a username and password for authentication.
 * It fetches counts for books, authors, categories, and series.
 *
 * Collected Data:
 * - Number of books
 * - Number of authors
 * - Number of categories
 * - Number of series
 *
 * Data not collected but available for extension:
 * - Detailed book information (title, description, publication date, etc.)
 * - Author details and biographies
 * - Category hierarchies and descriptions
 * - Series metadata and book associations
 * - User activity and reading progress
 * - Library settings and configurations
 *
 * Authentication:
 * The function uses a username and password passed in the request body for authentication.
 *
 * Error Handling:
 * - Captures any errors encountered during the API request process.
 * - Stores error messages and timestamps for troubleshooting.
 *******************/

 function homelab_fetch_calibre_web_data($api_url, $username, $password, $service_id) {
    $login_url = rtrim($api_url, '/') . '/login';
    $books_url = rtrim($api_url, '/') . '/api/v1/books';
    $authors_url = rtrim($api_url, '/') . '/api/v1/authors';
    $categories_url = rtrim($api_url, '/') . '/api/v1/categories';
    $series_url = rtrim($api_url, '/') . '/api/v1/series';

    $login_data = array(
        'username' => $username,
        'password' => $password,
    );

    $login_response = wp_remote_post($login_url, array(
        'body' => $login_data,
    ));

    if (is_wp_error($login_response)) {
        $error_message = "Login failed: " . $login_response->get_error_message();
        $error_timestamp = current_time('mysql');
        return array();
    }

    $cookies = wp_remote_retrieve_cookies($login_response);

    $books_response = wp_remote_get($books_url, array('cookies' => $cookies));
    $authors_response = wp_remote_get($authors_url, array('cookies' => $cookies));
    $categories_response = wp_remote_get($categories_url, array('cookies' => $cookies));
    $series_response = wp_remote_get($series_url, array('cookies' => $cookies));

    $error_message = null;
    $error_timestamp = null;

    if (is_wp_error($books_response) || is_wp_error($authors_response) || is_wp_error($categories_response) || is_wp_error($series_response)) {
        $error_message = "API request failed: " . $books_response->get_error_message() . ", " . $authors_response->get_error_message() . ", " . $categories_response->get_error_message() . ", " . $series_response->get_error_message();
        $error_timestamp = current_time('mysql');
        return array();
    }

    $books_data = json_decode(wp_remote_retrieve_body($books_response), true);
    $authors_data = json_decode(wp_remote_retrieve_body($authors_response), true);
    $categories_data = json_decode(wp_remote_retrieve_body($categories_response), true);
    $series_data = json_decode(wp_remote_retrieve_body($series_response), true);

    $books_count = count($books_data['books']);
    $authors_count = count($authors_data['authors']);
    $categories_count = count($categories_data['categories']);
    $series_count = count($series_data['series']);

    $fetched_data = array(
        'books_count' => $books_count,
        'authors_count' => $authors_count,
        'categories_count' => $categories_count,
        'series_count' => $series_count,
    );

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}