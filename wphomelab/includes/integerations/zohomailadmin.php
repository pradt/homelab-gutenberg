<?php
/******************
* Zoho Mail Admin Data Collection
* -------------------------------
* This function collects data from the Zoho Mail Admin API for dashboard display.
* It fetches information about the organization's domains, users, and groups.
*
* Collected Data:
* - Total number of domains
* - Total number of users
* - Total number of groups
* - Domain details (name, status, is_primary)
* - User details (name, email, status, last_login_time)
* - Group details (name, description, member_count)
*
* Data not collected but available for extension:
* - Detailed domain settings (DKIM, SPF, DMARC)
* - User roles and permissions
* - Group permissions and access levels
* - Mail retention policies
* - Spam and quarantine settings
* - Mail server configuration
* - API usage statistics
*
* Example of fetched_data structure:
* {
*   "total_domains": 2,
*   "total_users": 100,
*   "total_groups": 10,
*   "domains": [
*     {
*       "name": "example.com",
*       "status": "active",
*       "is_primary": true
*     },
*     {
*       "name": "example.net",
*       "status": "active",
*       "is_primary": false
*     }
*   ],
*   "users": [
*     {
*       "name": "John Doe",
*       "email": "john@example.com",
*       "status": "active",
*       "last_login_time": "2023-06-01T10:30:00Z"
*     },
*     ...
*   ],
*   "groups": [
*     {
*       "name": "Sales Team",
*       "description": "Group for sales department",
*       "member_count": 20
*     },
*     ...
*   ]
* }
*
* Requirements:
* - Zoho Mail Admin API should be accessible via the provided API URL.
* - API authentication token (OAuth token) is required for accessing the API.
*
* Parameters:
* - $api_url: The base URL of the Zoho Mail Admin API.
* - $access_token: The OAuth access token for authentication.
* - $account_id: The ID of the Zoho Mail account.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_zoho_mail_admin_data($api_url, $access_token, $account_id, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'domains' => '/api/domains',
        'users' => '/api/accounts/{accountId}/users',
        'groups' => '/api/accounts/{accountId}/groups',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . str_replace('{accountId}', $account_id, $endpoint);
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Zoho-oauthtoken ' . $access_token,
            ),
        );

        $response = wp_remote_get($url, $args);
        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        if ($key === 'domains') {
            $fetched_data['total_domains'] = count($data);
            $fetched_data['domains'] = array_map(function ($domain) {
                return array(
                    'name' => $domain['domainName'],
                    'status' => $domain['status'],
                    'is_primary' => $domain['isPrimary'],
                );
            }, $data);
        } elseif ($key === 'users') {
            $fetched_data['total_users'] = count($data);
            $fetched_data['users'] = array_map(function ($user) {
                return array(
                    'name' => $user['fullName'],
                    'email' => $user['email'],
                    'status' => $user['status'],
                    'last_login_time' => $user['lastLoginTime'],
                );
            }, $data);
        } elseif ($key === 'groups') {
            $fetched_data['total_groups'] = count($data);
            $fetched_data['groups'] = array_map(function ($group) {
                return array(
                    'name' => $group['groupName'],
                    'description' => $group['description'],
                    'member_count' => $group['memberCount'],
                );
            }, $data);
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}