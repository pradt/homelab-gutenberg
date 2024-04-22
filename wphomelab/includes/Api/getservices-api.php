<?php
// Register the REST API endpoint for retrieving services
//Endpoint is used in the Gutenberg Block
function homelab_register_services_endpoint() {
    register_rest_route('homelab/v1', '/services', array(
        'methods' => 'GET',
        'callback' => 'homelab_get_services',
        'permission_callback' => '__return_true',
    ));
}
add_action('rest_api_init', 'homelab_register_services_endpoint');


// Callback function for the services endpoint
function homelab_get_services() {
    global $wpdb;

    $table_name_services = $wpdb->prefix . 'homelab_services';
    $services = $wpdb->get_results("SELECT * FROM $table_name_services");

    $formatted_services = array();
    foreach ($services as $service) {
        $formatted_service = array(
            'id' => $service->id,
            'name' => $service->name,
            'icon' => $service->icon,
            'image_url' => wp_get_attachment_url($service->image_id),
            'status_check' => (bool) $service->status_check,
            'status_class' => homelab_get_service_status_class($service->id),
            'description' => $service->description,
            'service_url' => $service->service_url,
            'tags' => $service->tags,
            'category_id' => $service->category_id,
        );
        $formatted_services[] = $formatted_service;
    }

    return $formatted_services;
}