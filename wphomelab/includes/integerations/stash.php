<?php
/******************
* Stash Data Collection
* ----------------------
* This function collects data from Stash, a version control and collaboration platform, for dashboard display.
* It fetches information about the repositories, pull requests, and user activities.
*
* Collected Data:
* - Total number of repositories
* - Number of repositories by type (personal, project)
* - Total number of pull requests
* - Number of pull requests by status (open, merged, declined)
* - Total number of users
* - User activity stats (commits, pull requests opened, pull requests merged)
*
* Data not collected but available for extension:
* - Detailed repository information (name, description, creation date)
* - Repository-specific stats (commits, branches, contributors)
* - Pull request details (title, description, reviewers, comments)
* - User information (name, email, avatar)
* - Commit history and file changes
* - Issue tracking and task management data
* - Stash configuration and settings
*
* Example of fetched_data structure:
* {
*   "total_repositories": 10,
*   "personal_repositories": 5,
*   "project_repositories": 5,
*   "total_pull_requests": 20,
*   "open_pull_requests": 5,
*   "merged_pull_requests": 12,
*   "declined_pull_requests": 3,
*   "total_users": 15,
*   "user_activity_stats": {
*     "commits": 150,
*     "pull_requests_opened": 20,
*     "pull_requests_merged": 12
*   }
* }
*
* Requirements:
* - Stash API should be accessible via the provided API URL.
* - API authentication token (API key) is required for accessing the Stash API.
*
* Parameters:
* - $api_url: The base URL of the Stash API.
* - $api_key: The API key for authentication.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_stash_data($api_url, $api_key, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'repositories' => '/rest/api/1.0/projects/PROJECT_KEY/repos',
        'pull_requests' => '/rest/api/1.0/projects/PROJECT_KEY/repos/REPO_SLUG/pull-requests',
        'users' => '/rest/api/1.0/users',
    );
    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;
    
    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key,
            ),
        );
        
        $response = wp_remote_get($url, $args);
        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($key === 'repositories') {
            $fetched_data['total_repositories'] = count($data['values']);
            $fetched_data['personal_repositories'] = 0;
            $fetched_data['project_repositories'] = 0;
            foreach ($data['values'] as $repo) {
                if ($repo['project']['type'] === 'PERSONAL') {
                    $fetched_data['personal_repositories']++;
                } else {
                    $fetched_data['project_repositories']++;
                }
            }
        } elseif ($key === 'pull_requests') {
            $fetched_data['total_pull_requests'] = count($data['values']);
            $fetched_data['open_pull_requests'] = 0;
            $fetched_data['merged_pull_requests'] = 0;
            $fetched_data['declined_pull_requests'] = 0;
            foreach ($data['values'] as $pr) {
                $state = strtolower($pr['state']);
                if ($state === 'open') {
                    $fetched_data['open_pull_requests']++;
                } elseif ($state === 'merged') {
                    $fetched_data['merged_pull_requests']++;
                } elseif ($state === 'declined') {
                    $fetched_data['declined_pull_requests']++;
                }
            }
        } elseif ($key === 'users') {
            $fetched_data['total_users'] = count($data['values']);
            $fetched_data['user_activity_stats'] = array(
                'commits' => 0,
                'pull_requests_opened' => 0,
                'pull_requests_merged' => 0,
            );
            foreach ($data['values'] as $user) {
                $fetched_data['user_activity_stats']['commits'] += $user['statistics']['commits'];
                $fetched_data['user_activity_stats']['pull_requests_opened'] += $user['statistics']['pullRequestsOpened'];
                $fetched_data['user_activity_stats']['pull_requests_merged'] += $user['statistics']['pullRequestsMerged'];
            }
        }
    }
    
    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}