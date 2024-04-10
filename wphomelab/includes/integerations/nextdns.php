<?php
/******************
* NextDNS Data Collection
* ----------------------
* This function collects data from NextDNS, a DNS-based content filtering and security service, for dashboard display.
* It fetches information about the DNS queries, blocked domains, and threat categories.
*
* Collected Data:
* - Total number of DNS queries
* - Number of blocked queries
* - Number of allowed queries
* - Top blocked categories
* - Top blocked domains
*
* Data Structure Example (fetched_data):
* {
*   "total_queries": 1000,
*   "blocked_queries": 150,
*   "allowed_queries": 850,
*   "top_blocked_categories": [
*     {
*       "category": "Advertising",
*       "count": 50
*     },
*     {
*       "category": "Malware",
*       "count": 30
*     }
*   ],
*   "top_blocked_domains": [
*     {
*       "domain": "example.com",
*       "count": 20
*     },
*     {
*       "domain": "ads.example.org",
*       "count": 15
*     }
*   ]
* }
*
* Data not collected but available for extension:
* - Detailed query logs with timestamps and client IP addresses
* - DNS response times and latency
* - Geographical distribution of queries
* - DoH (DNS-over-HTTPS) and DoT (DNS-over-TLS) usage statistics
* - Threat intelligence and domain reputation data
* - NextDNS configuration and settings
*
* Requirements:
* - NextDNS API should be accessible via the provided API URL.
* - API authentication using an API key.
*
* Parameters:
* - $api_url: The base URL of the NextDNS API.
* - $api_key: The API key for authentication.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_nextdns_data($api_url, $api_key, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'analytics' => '/analytics',
    );
    
    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;
    
    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $args = array(
            'headers' => array(
                'X-Api-Key' => $api_key,
            ),
        );
        
        $response = wp_remote_get($url, $args);
        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($key === 'analytics') {
            $fetched_data['total_queries'] = $data['total_queries'];
            $fetched_data['blocked_queries'] = $data['blocked_queries'];
            $fetched_data['allowed_queries'] = $data['allowed_queries'];
            $fetched_data['top_blocked_categories'] = $data['top_blocked_categories'];
            $fetched_data['top_blocked_domains'] = $data['top_blocked_domains'];
        }
    }
    
    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}
