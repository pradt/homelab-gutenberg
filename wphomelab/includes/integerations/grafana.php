<?php
/**********************
* Grafana Data Collection
* ----------------------
* This function collects data from Grafana, a monitoring and observability platform, for dashboard display.
* It fetches information about the dashboards, panels, users, and overall usage statistics.
*
* Collected Data:
* - Total number of dashboards
* - Total number of panels
* - Number of dashboards by tag
* - Number of active users
* - Average dashboard count per user
*
* Data not collected but available for extension:
* - Detailed dashboard information (name, description, tags, URL)
* - Panel details (title, type, query, visualization)
* - User details (name, email, role, last active)
* - Organization and folder structure
* - Data source configuration and status
* - Alert and notification settings
* - Plugin and integration data
*
* Opportunities for additional data collection:
* - Dashboard usage and popularity metrics
* - Panel performance and query execution times
* - User activity and interaction patterns
* - Data source health and connectivity status
* - Alert frequency and resolution times
* - Plugin usage and effectiveness
*
* Requirements:
* - Grafana API should be accessible via the provided API URL.
* - API authentication requires a username and password.
*
* Parameters:
* - $api_url: The base URL of the Grafana API.
* - $username: The username for authentication.
* - $password: The password for authentication.
* - $service_id: The ID of the Grafana service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
***********************/
function homelab_fetch_grafana_data($api_url, $username, $password, $service_id) {
    $api_url = rtrim($api_url, '/');

    $endpoints = array(
        'dashboards' => '/api/search',
        'users' => '/api/users',
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
            'body' => json_encode(array(
                'username' => $username,
                'password' => $password,
            )),
        );

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'dashboards') {
            $fetched_data['total_dashboards'] = count($data);

            $tag_counts = array();
            $total_panels = 0;

            foreach ($data as $dashboard) {
                $tags = $dashboard['tags'];
                foreach ($tags as $tag) {
                    if (isset($tag_counts[$tag])) {
                        $tag_counts[$tag]++;
                    } else {
                        $tag_counts[$tag] = 1;
                    }
                }
                $total_panels += $dashboard['panels'];
            }

            $fetched_data['dashboards_by_tag'] = $tag_counts;
            $fetched_data['total_panels'] = $total_panels;
        } elseif ($key === 'users') {
            $fetched_data['active_users'] = count($data);
            $fetched_data['avg_dashboards_per_user'] = $fetched_data['total_dashboards'] > 0 ? $fetched_data['total_dashboards'] / $fetched_data['active_users'] : 0;
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}