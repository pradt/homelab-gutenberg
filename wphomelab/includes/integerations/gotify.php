<?php
/**********************
* Gotify Data Collection
* ----------------------
* This function collects data from Gotify, a self-hosted notification service, for dashboard display.
* It fetches information about the applications, messages, and overall usage statistics.
*
* Collected Data:
* - Total number of applications
* - Number of messages by priority (low, moderate, high)
* - Total number of messages
* - Average message count per application
*
* Data not collected but available for extension:
* - Detailed application information (name, description, token)
* - Message details (title, content, date, priority)
* - Client and user management data
* - Plugin and integration settings
* - Notification and delivery statistics
* - Message deletion and acknowledgment status
*
* Opportunities for additional data collection:
* - Application-specific message statistics
* - User engagement and interaction metrics
* - Notification delivery success rates
* - Plugin usage and performance data
* - System health and resource utilization
*
* Requirements:
* - Gotify API should be accessible via the provided API URL.
* - API authentication token (API key or client token) is required for accessing the Gotify API.
*
* Parameters:
* - $api_url: The base URL of the Gotify API.
* - $api_key: The API key or client token for authentication.
* - $service_id: The ID of the Gotify service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
***********************/
function homelab_fetch_gotify_data($api_url, $api_key, $service_id) {
    $api_url = rtrim($api_url, '/');

    $endpoints = array(
        'applications' => '/application',
        'messages' => '/message',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;

        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-Gotify-Key' => $api_key,
            ),
        );

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'applications') {
            $fetched_data['total_applications'] = count($data);
        } elseif ($key === 'messages') {
            $priority_counts = array(
                'low' => 0,
                'moderate' => 0,
                'high' => 0,
            );

            foreach ($data as $message) {
                $priority = strtolower($message['priority']);
                if (isset($priority_counts[$priority])) {
                    $priority_counts[$priority]++;
                }
            }

            $fetched_data['messages_low'] = $priority_counts['low'];
            $fetched_data['messages_moderate'] = $priority_counts['moderate'];
            $fetched_data['messages_high'] = $priority_counts['high'];
            $fetched_data['total_messages'] = count($data);
            $fetched_data['avg_messages_per_app'] = $fetched_data['total_applications'] > 0 ? $fetched_data['total_messages'] / $fetched_data['total_applications'] : 0;
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}