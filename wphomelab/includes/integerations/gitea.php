<?php
/******************
 * Gitea Data Collection
 * ---------------------
 * This function collects data from Gitea, a self-hosted Git service, for dashboard display.
 * It fetches information about the user's repositories, organizations, and activity statistics.
 *
 * Collected Data:
 * - Total number of repositories owned by the user
 * - Number of private repositories
 * - Number of public repositories
 * - Number of organizations the user belongs to
 * - Number of pull requests created by the user
 * - Number of issues created by the user
 *
 * Data not collected but available for extension:
 * - Detailed repository information (name, description, URL, stars, forks)
 * - Detailed organization information (name, description, URL, members)
 * - User profile information (name, email, avatar, bio)
 * - Detailed activity statistics (commits, issues, pull requests)
 * - Repository collaboration and access control settings
 *
 * Requirements:
 * - Gitea API should be accessible via the provided API URL.
 * - API authentication token (API key) is required.
 *
 * Parameters:
 * - $api_url: The base URL of the Gitea API.
 * - $api_key: The API key for authentication.
 * - $service_id: The ID of the service being monitored.
 *
 * Error Handling:
 * - Captures any errors encountered during the API request process.
 * - Stores error messages and timestamps for troubleshooting.
 *******************/

 function homelab_fetch_gitea_data($api_url, $api_key, $service_id) {
    $api_url = rtrim($api_url, '/');

    $endpoints = array(
        'repos' => '/api/v1/user/repos',
        'orgs' => '/api/v1/user/orgs',
        'activity' => '/api/v1/user/stats',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $args = array(
            'headers' => array(
                'Authorization' => 'token ' . $api_key,
                'Content-Type' => 'application/json',
            ),
        );
        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'repos') {
            $fetched_data['total_repos'] = count($data);

            $private_repos = 0;
            $public_repos = 0;
            foreach ($data as $repo) {
                if ($repo['private']) {
                    $private_repos++;
                } else {
                    $public_repos++;
                }
            }
            $fetched_data['private_repos'] = $private_repos;
            $fetched_data['public_repos'] = $public_repos;
        } elseif ($key === 'orgs') {
            $fetched_data['num_orgs'] = count($data);
        } elseif ($key === 'activity') {
            $fetched_data['pull_requests_created'] = $data['pull_requests_created'];
            $fetched_data['issues_created'] = $data['issues_created'];
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}