<?php
// Get latest service status
function homelab_get_latest_service_status($service_id) {
    global $wpdb;

    $table_name_status_logs = $wpdb->prefix . 'homelab_service_status_logs';
    $status = $wpdb->get_var($wpdb->prepare("SELECT status FROM $table_name_status_logs WHERE service_id = %d ORDER BY check_datetime DESC LIMIT 1", $service_id));

    return $status ? $status : 'UNKNOWN';
}

// Check service status
function homelab_check_service_status($service_id) {
    global $wpdb;

    $table_name_services = $wpdb->prefix . 'homelab_services';
    $table_name_status_logs = $wpdb->prefix . 'homelab_service_status_logs';

    $service = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name_services WHERE id = %d", $service_id));

    if ($service->status_check) {
        $url = $service->status_check_url ? $service->status_check_url : $service->service_url;
        $disable_ssl = $service->disable_ssl;

        $args = array(
            'timeout' => 10,
            'sslverify' => !$disable_ssl,
        );

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            $status = 'RED';
            $response_code = 0;
            $response_message = $response->get_error_message();
        } else {
            $response_code = wp_remote_retrieve_response_code($response);
            $response_message = wp_remote_retrieve_response_message($response);

            if ($response_code >= 200 && $response_code <= 299) {
                $status = 'GREEN';
            } elseif ($response_code >= 100 && $response_code <= 199 || $response_code >= 300 && $response_code <= 399) {
                $status = 'AMBER';
            } else {
                $status = 'RED';
            }

            if ($status === 'RED') {
                $notify_email = $service->notify_email;
                if ($notify_email) {
                    $subject = 'Service Down: ' . $service->name;
                    $message = "The service '{$service->name}' is currently down. Please check the status.";
                    wp_mail($notify_email, $subject, $message);
                }
            }
        }

        // Generate a GUID
        $guid = wp_generate_uuid4();


        $wpdb->insert(
            $table_name_status_logs,
            array(
                'id' => $guid,
                'service_id' => $service_id,
                'check_datetime' => current_time('mysql'),
                'url' => $url,
                'response_code' => $response_code,
                'response_message' => $response_message,
                'status' => $status,
            )
        );
    }
}