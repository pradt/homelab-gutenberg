<?php
// Helper function to get the service status class
function homelab_get_service_status_class($service_id) {
    $status = homelab_get_latest_service_status($service_id);
    switch ($status) {
        case 'GREEN':
            return 'status-green';
        case 'AMBER':
            return 'status-amber';
        case 'RED':
            return 'status-red';
        default:
            return '';
    }
}