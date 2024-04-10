<?php
/******************
* Mealie Data Collection
* ----------------------
* This function collects data from Mealie, a recipe management tool, for dashboard display.
* It fetches information about recipes, categories, tags, and user interactions.
*
* Collected Data:
* - Total number of recipes
* - Number of recipes by category
* - Number of recipes by tag
* - Most recently added recipes
* - Most popular recipes (based on user interactions)
*
* Data not collected but available for extension:
* - Detailed recipe information (ingredients, instructions, notes)
* - User-specific data (favorites, meal plans, shopping lists)
* - Recipe ratings and reviews
* - Recipe nutrition information
* - Mealie configuration and settings
*
* Opportunities for additional data:
* - Recipe search and filtering options
* - User activity and engagement metrics
* - Ingredient usage and inventory tracking
* - Recipe recommendation engine
*
* Requirements:
* - Mealie API should be accessible via the provided API URL.
* - API authentication token (API key) is required for accessing the Mealie API.
*
* Parameters:
* - $api_url: The base URL of the Mealie API.
* - $api_key: The API key for authentication.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*
* Example Data Structure:
* $fetched_data = array(
*   'total_recipes' => 100,
*   'recipes_by_category' => array(
*     'Main Dish' => 40,
*     'Dessert' => 20,
*     'Appetizer' => 15,
*     ...
*   ),
*   'recipes_by_tag' => array(
*     'Vegetarian' => 25,
*     'Gluten-free' => 10,
*     'Quick & Easy' => 30,
*     ...
*   ),
*   'recent_recipes' => array(
*     array(
*       'id' => 123,
*       'name' => 'Spaghetti Bolognese',
*       'image_url' => 'https://example.com/recipe-images/123.jpg',
*       'created_at' => '2023-06-01 10:30:00',
*     ),
*     ...
*   ),
*   'popular_recipes' => array(
*     array(
*       'id' => 456,
*       'name' => 'Chocolate Chip Cookies',
*       'image_url' => 'https://example.com/recipe-images/456.jpg',
*       'views' => 1000,
*       'favorites' => 250,
*     ),
*     ...
*   ),
* );
*******************/
function homelab_fetch_mealie_data($api_url, $api_key, $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
        'recipes' => '/api/recipes',
        'categories' => '/api/categories',
        'tags' => '/api/tags',
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
        
        if ($key === 'recipes') {
            $fetched_data['total_recipes'] = count($data);
            
            $recent_recipes = array_slice($data, -5);
            $fetched_data['recent_recipes'] = array_map(function($recipe) {
                return array(
                    'id' => $recipe['id'],
                    'name' => $recipe['name'],
                    'image_url' => $recipe['image'],
                    'created_at' => $recipe['dateAdded'],
                );
            }, $recent_recipes);
            
            $popular_recipes = array_slice($data, 0, 5); // Assuming recipes are sorted by popularity
            $fetched_data['popular_recipes'] = array_map(function($recipe) {
                return array(
                    'id' => $recipe['id'],
                    'name' => $recipe['name'],
                    'image_url' => $recipe['image'],
                    'views' => $recipe['views'],
                    'favorites' => $recipe['favorites'],
                );
            }, $popular_recipes);
        }
        
        if ($key === 'categories') {
            $fetched_data['recipes_by_category'] = array();
            foreach ($data as $category) {
                $fetched_data['recipes_by_category'][$category['name']] = $category['recipeCount'];
            }
        }
        
        if ($key === 'tags') {
            $fetched_data['recipes_by_tag'] = array();
            foreach ($data as $tag) {
                $fetched_data['recipes_by_tag'][$tag['name']] = $tag['recipeCount'];
            }
        }
    }
    
    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    
    return $fetched_data;
}