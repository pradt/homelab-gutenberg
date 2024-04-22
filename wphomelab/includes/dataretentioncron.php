<?php
/**
 * Cron job to delete old service data
 */
add_action('homelab_delete_old_service_data', 'homelab_cron_delete_old_service_data');
function homelab_cron_delete_old_service_data() {
    global $wpdb;
    
    // Get settings
    $settings = get_option('homelab_settings', []);
    $retention_days = isset($settings['data_retention_days']) ? intval($settings['data_retention_days']) : 60;
    
    // Calculate threshold date
    $threshold_date = date('Y-m-d H:i:s', strtotime("-$retention_days days"));
    
    // Delete old data from service_data table
    $table_name_service_data = $wpdb->prefix . 'homelab_service_data';
    $wpdb->query($wpdb->prepare("DELETE FROM $table_name_service_data WHERE fetched_at < %s", $threshold_date));
}

/**
 * Cron job to delete old status logs
 */
add_action('homelab_delete_old_status_logs', 'homelab_cron_delete_old_status_logs');
function homelab_cron_delete_old_status_logs() {
    global $wpdb;
    
    // Get settings
    $settings = get_option('homelab_settings', []);
    $retention_days = isset($settings['status_log_retention_days']) ? intval($settings['status_log_retention_days']) : 60;
    
    // Calculate threshold date
    $threshold_date = date('Y-m-d H:i:s', strtotime("-$retention_days days"));
    
    // Delete old data from status_logs table
    $table_name_status_logs = $wpdb->prefix . 'homelab_service_status_logs';
    $wpdb->query($wpdb->prepare("DELETE FROM $table_name_status_logs WHERE check_datetime < %s", $threshold_date));
}