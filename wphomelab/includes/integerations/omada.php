<?php
/******************
* Omada Data Collection
* ----------------------
* This function collects data from Omada, an identity and access management solution, for dashboard display.
* It fetches information about users, groups, and authentication activities.
*
* Collected Data:
* - Total number of users
* - Number of active users
* - Number of inactive users
* - Total number of groups
* - Number of authentication attempts
* - Number of successful authentications
* - Number of failed authentications
*
* Data not collected but available for extension:
* - Detailed user information (name, email, roles, last login)
* - Group details (name, description, members)
* - Authentication logs with timestamps and IP addresses
* - Password policy settings
* - Multi-factor authentication (MFA) usage statistics
* - User provisioning and deprovisioning history
* - Access request and approval workflows
*
* Requirements:
* - Omada API should be accessible via the provided API URL.
* - API authentication requires a username, password, and possibly a site name.
*
* Parameters:
* - $api_url: The base URL of the Omada API.
* - $username: The username for authentication.
* - $password: The password for authentication.
* - $site_name: The site name (if required).
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*
* Example of fetched_data structure:
* {
*   "total_users": 500,
*   "active_users": 450,
*   "inactive_users": 50,
*   "total_groups": 20,
*   "auth_attempts": 1000,
*   "auth_successes": 950,
*   "auth_failures": 50
* }
*******************/
function homelab_fetch_omada_data($api_url, $username, $password, $site_name = '', $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'users' => '/api/v1/users',
        'groups' => '/api/v1/groups',
        'auth_logs' => '/api/v1/auth_logs',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    $auth_data = array(
        'username' => $username,
        'password' => $password,
    );
    if (!empty($site_name)) {
        $auth_data['site_name'] = $site_name;
    }

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode($auth_data),
        );

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'users') {
            $fetched_data['total_users'] = count($data);
            $active_users = 0;
            $inactive_users = 0;
            foreach ($data as $user) {
                if ($user['status'] === 'active') {
                    $active_users++;
                } else {
                    $inactive_users++;
                }
            }
            $fetched_data['active_users'] = $active_users;
            $fetched_data['inactive_users'] = $inactive_users;
        } elseif ($key === 'groups') {
            $fetched_data['total_groups'] = count($data);
        } elseif ($key === 'auth_logs') {
            $fetched_data['auth_attempts'] = count($data);
            $auth_successes = 0;
            $auth_failures = 0;
            foreach ($data as $log) {
                if ($log['status'] === 'success') {
                    $auth_successes++;
                } else {
                    $auth_failures++;
                }
            }
            $fetched_data['auth_successes'] = $auth_successes;
            $fetched_data['auth_failures'] = $auth_failures;
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}