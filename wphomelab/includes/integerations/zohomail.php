<?php
/******************
* Zoho Mail Data Collection
* -------------------------
* This function collects data from Zoho Mail, an email hosting and management service, for dashboard display.
* It fetches information about the user's mailbox, including the total number of emails, unread emails, and storage usage.
*
* Collected Data:
* - Total number of emails in the mailbox
* - Number of unread emails
* - Storage usage (in bytes)
* - Storage usage percentage
*
* Data not collected but available for extension:
* - Detailed email information (sender, subject, date, size)
* - Email folders and labels
* - Email attachments and their details
* - Email search and filtering options
* - User preferences and settings
* - Email composing and sending statistics
* - Collaboration features (shared mailboxes, delegation)
*
* Example of fetched_data structure:
* {
*   "total_emails": 1000,
*   "unread_emails": 50,
*   "storage_usage": 1073741824,
*   "storage_usage_percentage": 25.5
* }
*
* Requirements:
* - Zoho Mail API should be accessible via the provided API URL.
* - API authentication token (OAuth token) is required for accessing the Zoho Mail API.
*
* Parameters:
* - $api_url: The base URL of the Zoho Mail API.
* - $access_token: The OAuth access token for authentication.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/

function homelab_fetch_zoho_mail_data($api_url, $access_token, $service_id, $account_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'mailbox' => '/api/accounts/{accountId}/messages/view',
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
        if ($key === 'mailbox') {
            $fetched_data['total_emails'] = $data['data']['total'];
            $fetched_data['unread_emails'] = $data['data']['unread'];
            $fetched_data['storage_usage'] = $data['data']['storage_usage'];
            $fetched_data['storage_usage_percentage'] = $data['data']['storage_usage_percentage'];
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}

