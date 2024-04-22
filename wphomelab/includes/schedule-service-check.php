<?php
// Add service check schedule
function homelab_add_service_check_schedule($service_id, $polling_interval) {
    $hook = 'homelab_check_service_status_' . $service_id;
    $recurrence = 'homelab_service_check_interval_' . $service_id;

    if (!wp_next_scheduled($hook)) {
        wp_schedule_event(time(), $recurrence, $hook);
    }

    add_filter('cron_schedules', function ($schedules) use ($recurrence, $polling_interval) {
        $schedules[$recurrence] = array(
            'interval' => $polling_interval,
            'display' => 'Every ' . $polling_interval . ' seconds',
        );
        return $schedules;
    });

    add_action($hook, function () use ($service_id) {
        homelab_check_service_status($service_id);
    });
}

// Schedule service checks
function homelab_schedule_service_checks() {
    global $wpdb;

    $table_name_services = $wpdb->prefix . 'homelab_services';
    $services = $wpdb->get_results("SELECT * FROM $table_name_services WHERE status_check = 1");

    foreach ($services as $service) {
        if (!wp_next_scheduled('homelab_check_service_status_' . $service->id)) {
            wp_schedule_event(time(), 'homelab_service_check_interval_' . $service->id, 'homelab_check_service_status_' . $service->id);
        }
    }
}
add_action('init', 'homelab_schedule_service_checks');