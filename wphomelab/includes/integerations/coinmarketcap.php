<?php
/******************
 * Coin Market Cap Data Collection
 * --------------------------------
 * This function collects cryptocurrency data from the Coin Market Cap API for dashboard display, using an API key for authentication.
 * It fetches the latest price, market cap, 24h volume, and 24h percent change for the specified cryptocurrencies.
 *
 * Collected Data:
 * - Latest price of each specified cryptocurrency
 * - Market cap of each specified cryptocurrency
 * - 24-hour trading volume of each specified cryptocurrency
 * - 24-hour percent change in price of each specified cryptocurrency
 *
 * Data not collected but available for extension:
 * - Historical price and market data
 * - Detailed cryptocurrency information (circulating supply, total supply, max supply, etc.)
 * - Global cryptocurrency market data
 * - Exchange and trading pair data
 * - News and social media sentiment data
 *
 * Authentication:
 * The function uses an API key passed in the header for authentication.
 *
 * Parameters:
 * - $api_url: The base URL of the Coin Market Cap API.
 * - $api_key: The API key for authentication.
 * - $service_id: The ID of the service being monitored.
 * - $currency: (Optional) The target currency for price conversion (default: USD).
 * - $symbols: (Optional) An array of cryptocurrency symbols (e.g., BTC, ETH, LTC) to fetch data for. If not provided, data will be fetched for all cryptocurrencies.
 * - $slug_symbols: (Optional) An array of cryptocurrency slug symbols (e.g., bitcoin, ethereum, litecoin) to fetch data for. If not provided, data will be fetched for all cryptocurrencies.
 *
 * Error Handling:
 * - Captures any errors encountered during the API request process.
 * - Stores error messages and timestamps for troubleshooting.
 *******************/

 function homelab_fetch_coinmarketcap_data($api_url, $api_key, $service_id, $currency = 'USD', $symbols = array(), $slug_symbols = array()) {
    $api_url = rtrim($api_url, '/');

    $headers = array(
        'Accepts' => 'application/json',
        'X-CMC_PRO_API_KEY' => $api_key,
    );

    $params = array(
        'convert' => $currency,
    );

    if (!empty($symbols)) {
        $params['symbol'] = implode(',', $symbols);
    }

    if (!empty($slug_symbols)) {
        $params['slug'] = implode(',', $slug_symbols);
    }

    $url = $api_url . '/v1/cryptocurrency/quotes/latest?' . http_build_query($params);

    $response = wp_remote_get($url, array('headers' => $headers));

    $error_message = null;
    $error_timestamp = null;

    if (is_wp_error($response)) {
        $error_message = "API request failed: " . $response->get_error_message();
        $error_timestamp = current_time('mysql');
        return array();
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($data['status']['error_code']) && $data['status']['error_code'] !== 0) {
        $error_message = "API request failed with error code: " . $data['status']['error_code'] . ", error message: " . $data['status']['error_message'];
        $error_timestamp = current_time('mysql');
        return array();
    }

    $fetched_data = array();

    foreach ($data['data'] as $cryptocurrency) {
        $symbol = $cryptocurrency['symbol'];
        $fetched_data[$symbol] = array(
            'price' => $cryptocurrency['quote'][$currency]['price'],
            'market_cap' => $cryptocurrency['quote'][$currency]['market_cap'],
            'volume_24h' => $cryptocurrency['quote'][$currency]['volume_24h'],
            'percent_change_24h' => $cryptocurrency['quote'][$currency]['percent_change_24h'],
        );
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);

    return $fetched_data;
}