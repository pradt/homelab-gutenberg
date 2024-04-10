<?php
/******************
* Medusa Data Collection
* ----------------------
* This function collects data from Medusa, a headless e-commerce platform, for dashboard display.
* It fetches information about orders, products, customers, and sales performance.
*
* Collected Data:
* - Total number of orders
* - Total revenue
* - Average order value
* - Number of products
* - Number of customers
*
* Data not collected but available for extension:
* - Order status breakdown (pending, processing, shipped, delivered, cancelled)
* - Top-selling products
* - Customer acquisition channels
* - Inventory levels
* - Discount and promotion usage
* - Shipping and fulfillment metrics
* - Product categories and tags
* - Customer segments and demographics
*
* Requirements:
* - Medusa API should be accessible via the provided API URL.
* - API authentication token (API key) is required for accessing the Medusa API.
*
* Parameters:
* - $api_url: The base URL of the Medusa API.
* - $api_key: The API key for authentication.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*
* Example fetched_data structure:
* {
*   "total_orders": 500,
*   "total_revenue": 50000,
*   "avg_order_value": 100,
*   "total_products": 200,
*   "total_customers": 300
* }
*******************/
function homelab_fetch_medusa_data($api_url, $api_key, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'orders' => '/store/orders',
        'products' => '/store/products',
        'customers' => '/store/customers',
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

        switch ($key) {
            case 'orders':
                $fetched_data['total_orders'] = count($data);
                $total_revenue = array_sum(array_column($data, 'total'));
                $fetched_data['total_revenue'] = $total_revenue;
                $fetched_data['avg_order_value'] = $fetched_data['total_orders'] > 0 ? $total_revenue / $fetched_data['total_orders'] : 0;
                break;
            case 'products':
                $fetched_data['total_products'] = count($data);
                break;
            case 'customers':
                $fetched_data['total_customers'] = count($data);
                break;
        }
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}