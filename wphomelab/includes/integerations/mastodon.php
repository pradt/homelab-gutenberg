<?php
/******************
* Mastodon Data Collection
* -------------------------
* This function collects data from Mastodon, a decentralized social media platform, for dashboard display.
* It fetches information about the user's account, followers, following, and toots (posts).
*
* Collected Data:
* - User account information (username, display name, avatar URL, header URL, bio, website, created_at)
* - Number of followers
* - Number of accounts the user is following
* - Total number of toots (posts)
* - Latest toots (up to a specified limit)
*
* Data not collected but available for extension:
* - Detailed follower and following information (account names, avatars, bios)
* - Toot details (favorites, reblogs, replies, mentions)
* - Notifications (mentions, follows, favorites, reblogs)
* - Lists and list memberships
* - Mutes and blocks
* - Conversations and direct messages
* - Trends and hashtags
*
* Other data opportunities:
* - User engagement metrics (likes, reblogs, replies)
* - Toot sentiment analysis
* - User activity patterns and trends
* - Network analysis of follower/following relationships
* - Integration with other Fediverse platforms
*
* Requirements:
* - Mastodon API should be accessible via the provided API URL.
* - API authentication token (access token) is required.
*
* Parameters:
* - $api_url: The base URL of the Mastodon API.
* - $access_token: The access token for authentication.
* - $toots_limit: The maximum number of latest toots to fetch (default: 5).
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*
* Example of fetched_data structure:
* {
*     "account": {
*         "username": "johndoe",
*         "display_name": "John Doe",
*         "avatar_url": "https://example.com/avatars/johndoe.png",
*         "header_url": "https://example.com/headers/johndoe.png",
*         "bio": "Lorem ipsum dolor sit amet.",
*         "website": "https://johndoe.com",
*         "created_at": "2022-01-01T00:00:00.000Z"
*     },
*     "followers_count": 1000,
*     "following_count": 500,
*     "toots_count": 2500,
*     "latest_toots": [
*         {
*             "id": "123456789",
*             "content": "Hello, world!",
*             "created_at": "2023-05-20T12:34:56.000Z"
*         },
*         ...
*     ]
* }
*******************/
function homelab_fetch_mastodon_data($api_url, $access_token, $toots_limit = 5,$service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'account' => '/api/v1/accounts/verify_credentials',
        'followers' => '/api/v1/accounts/verify_credentials/followers',
        'following' => '/api/v1/accounts/verify_credentials/following',
        'toots' => '/api/v1/accounts/verify_credentials/statuses',
    );

    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    foreach ($endpoints as $key => $endpoint) {
        $url = $api_url . $endpoint;
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
            ),
        );

        if ($key === 'toots') {
            $args['query'] = array('limit' => $toots_limit);
        }

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
            $error_timestamp = current_time('mysql');
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($key === 'account') {
            $fetched_data['account'] = array(
                'username' => $data['username'],
                'display_name' => $data['display_name'],
                'avatar_url' => $data['avatar'],
                'header_url' => $data['header'],
                'bio' => $data['note'],
                'website' => $data['website'],
                'created_at' => $data['created_at'],
            );
        } elseif ($key === 'followers') {
            $fetched_data['followers_count'] = count($data);
        } elseif ($key === 'following') {
            $fetched_data['following_count'] = count($data);
        } elseif ($key === 'toots') {
            $fetched_data['toots_count'] = $data['total'];
            $fetched_data['latest_toots'] = array_map(function ($toot) {
                return array(
                    'id' => $toot['id'],
                    'content' => $toot['content'],
                    'created_at' => $toot['created_at'],
                );
            }, $data['items']);
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}