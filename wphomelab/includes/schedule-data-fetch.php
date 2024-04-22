<?php


//Schedules a data fetch on the service with an interval defined by fetch_interval
function homelab_schedule_data_fetch($service_id, $fetch_interval) {
    // Log the start of the function
    error_log("homelab_schedule_data_fetch: Starting scheduling for service ID $service_id with fetch interval $fetch_interval");

    // Check if the fetch interval is valid
    if (!empty($fetch_interval) && $fetch_interval > 0) {
        // Generate the interval name based on the fetch_interval
        $interval_name = 'every_' . $fetch_interval . '_seconds';

        // Log the interval name
        error_log("homelab_schedule_data_fetch: Interval name: $interval_name");

        // Check if the interval already exists
        if (!wp_get_schedule('homelab_fetch_service_data', array($service_id))) {
            // Schedule the recurring data fetch event
            $result = wp_schedule_event(time(), $interval_name, 'homelab_fetch_service_data', array($service_id));

            // Log the scheduling result
            if ($result === false) {
                error_log("homelab_schedule_data_fetch: Failed to schedule data fetch for service ID $service_id");
            } else {
                error_log("homelab_schedule_data_fetch: Data fetch scheduled successfully for service ID $service_id");
            }
        } else {
            error_log("homelab_schedule_data_fetch: Data fetch is already scheduled for service ID $service_id");
        }
    } else {
        // If the fetch interval is not valid, unschedule any existing event for the service
        $result = wp_clear_scheduled_hook('homelab_fetch_service_data', array($service_id));

        // Log the unscheduling result
        if ($result === false) {
            error_log("homelab_schedule_data_fetch: Failed to unschedule data fetch for service ID $service_id");
        } else {
            error_log("homelab_schedule_data_fetch: Data fetch unscheduled successfully for service ID $service_id");
        }
    }

    // Log the completion of the function
    error_log("homelab_schedule_data_fetch: Scheduling completed for service ID $service_id");
}

//Stops the data fetch on the service with an interval defined by the fetch_interval
function homelab_stop_data_fetch($service_id) {
    // Unschedule the data fetch event for the specific service
    wp_clear_scheduled_hook('homelab_fetch_service_data', array($service_id));
}

// Register the custom interval
function homelab_add_custom_interval($schedules) {
    $services = homelab_get_all_services();
    
    foreach ($services as $service) {
        $fetch_interval = $service->fetch_interval;
        
        // Check if the fetch interval is valid
        if (!empty($fetch_interval) && $fetch_interval > 0) {
            $interval_name = 'every_' . $fetch_interval . '_seconds';
            
            if (!isset($schedules[$interval_name])) {
                $schedules[$interval_name] = array(
                    'interval' => $fetch_interval,
                    'display' => __('Every ' . $fetch_interval . ' seconds')
                );
            }
        }
    }
    
    return $schedules;
}
add_filter('cron_schedules', 'homelab_add_custom_interval');

function homelab_get_all_services() {
    global $wpdb;
    $table_name_services = $wpdb->prefix . 'homelab_services';
    $services = $wpdb->get_results("SELECT * FROM $table_name_services");
    return $services;
}

// AJAX handler for starting data fetch
add_action('wp_ajax_homelab_start_data_fetch', 'homelab_start_data_fetch_ajax_handler');
function homelab_start_data_fetch_ajax_handler()
{
    $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
    $fetch_interval = isset($_POST['fetch_interval']) ? intval($_POST['fetch_interval']) : 0;

    homelab_schedule_data_fetch($service_id, $fetch_interval);

    wp_send_json_success();
}

// AJAX handler for stopping data fetch
add_action('wp_ajax_homelab_stop_data_fetch', 'homelab_stop_data_fetch_ajax_handler');
function homelab_stop_data_fetch_ajax_handler()
{
    $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;

    homelab_stop_data_fetch($service_id);

    wp_send_json_success();
}