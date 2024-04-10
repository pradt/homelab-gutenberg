<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
// View service page
function homelab_view_service_page() {
    global $wpdb;

    $table_name_services = $wpdb->prefix . 'homelab_services';

    if (!isset($_GET['service_id'])) {
        // Button to open modal
        echo '<button id="selectServiceBtn">Select Service to View</button>';
        
        // Modal placeholder
        echo '<div id="serviceSelectionModal" style="display:none;">
                <h2>Select a Service</h2>
                <ul id="serviceList">';
        
        // Query to get services created by the user
        $services = $wpdb->get_results("SELECT * FROM $table_name_services ORDER BY name ASC");
        foreach ($services as $service) {
            echo '<li><a href="?page=homelab-view-service&service_id=' . $service->id . '">' . esc_html($service->name) . '</a></li>';
        }
    
        echo '</ul></div>';
        
        // Include JavaScript for modal functionality
        // This script depends on jQuery, which is typically available in WordPress admin pages
        ?>
        <script>
        jQuery(document).ready(function($) {
            $('#selectServiceBtn').click(function() {
                $('#serviceSelectionModal').show();
            });
        });
        </script>
        <?php
        return; // Prevent displaying the rest of the form when service_id is not set
    }

    if (isset($_GET['service_id'])) {
        $service_id = intval($_GET['service_id']);
        $service = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name_services WHERE id = %d", $service_id));

        if (!$service) {
            echo '<div class="notice notice-error"><p>Service not found.</p></div>';
            return;
        }
    } else {
        echo '<div class="notice notice-error"><p>Invalid service ID.</p></div>';
        return;
    }

    ?>
    <div class="wrap">
        <h1><?php echo esc_html($service->name); ?></h1>
        <div class="service-details">
            <!-- ... (service details HTML remains the same) -->
        </div>
        <div class="service-actions">
            <a href="<?php echo admin_url('admin.php?page=homelab-edit-service&service_id=' . $service_id); ?>" class="button button-primary">Edit Service</a>
        </div>
        <div class="service-tabs">
            <ul class="nav-tab-wrapper">
                <li class="nav-tab nav-tab-active" data-tab="notes">Notes</li>
                <li class="nav-tab" data-tab="history">Service Check History</li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="notes">
                    <?php
                    // Display notes
                    $notes = homelab_get_service_notes($service_id);
                    if (!empty($notes)) {
                        echo '<ul class="service-notes">';
                        foreach ($notes as $note) {
                            $user = get_userdata($note->user_id);
                            $user_avatar = get_avatar($note->user_id, 32);
                            $created_at = date('Y-m-d H:i:s', strtotime($note->created_at));
                            ?>
                            <li>
                                <div class="note-header">
                                    <div class="note-avatar"><?php echo $user_avatar; ?></div>
                                    <div class="note-author"><?php echo esc_html($user->display_name); ?></div>
                                    <div class="note-date"><?php echo esc_html($created_at); ?></div>
                                </div>
                                <div class="note-content"><?php echo esc_html($note->note); ?></div>
                            </li>
                            <?php
                        }
                        echo '</ul>';
                    } else {
                        echo '<p>No notes found.</p>';
                    }
                    ?>
                </div>
                <div class="tab-pane" id="history">
                    <?php
                    // Display service check history
                    $history = homelab_get_service_check_history($service_id);
                    if (!empty($history)) {
                        echo '<canvas id="service-history-chart"></canvas>';
                        // Prepare the data for the chart
                        $labels = [];
                        $response_codes = [];
                        $statuses = [];
                        foreach ($history as $check) {
                            $labels[] = $check->check_datetime;
                            $response_codes[] = $check->response_code;
                            $statuses[] = $check->status;
                        }
                        ?>
                        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                        <script>
                        jQuery(document).ready(function($) {
                            var ctx = document.getElementById('service-history-chart').getContext('2d');
                            var chart = new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: <?php echo json_encode($labels); ?>,
                                    datasets: [{
                                        label: 'Response Code',
                                        data: <?php echo json_encode($response_codes); ?>,
                                        borderColor: 'rgba(75, 192, 192, 1)',
                                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                        borderWidth: 1
                                    }]
                                },
                                options: {
                                    scales: {
                                        y: {
                                            beginAtZero: true
                                        }
                                    }
                                }
                            });
                        });
                        </script>
                        <?php
                    } else {
                        echo '<p>No service check history found.</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <style>
    .service-details {
        display: flex;
        margin-bottom: 20px;
    }
    .service-image {
        flex: 0 0 200px;
        margin-right: 20px;
    }
    .service-image img {
        max-width: 100%;
        height: auto;
    }
    .service-info {
        flex: 1;
    }
    .service-info p {
        margin: 0 0 10px;
    }
    .service-actions {
        margin-bottom: 20px;
    }
    .service-tabs .nav-tab-wrapper {
        margin-bottom: 20px;
    }
    .service-tabs .tab-content {
        padding: 20px;
        border: 1px solid #ccc;
        border-top: none;
    }
    .service-notes {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .service-notes li {
        margin-bottom: 20px;
    }
    .note-header {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }
    .note-avatar {
        margin-right: 10px;
    }
    .note-author {
        font-weight: bold;
        margin-right: 10px;
    }
    .note-date {
        color: #888;
    }
    .note-content {
        margin-left: 42px;
    }
    </style>
    <script>
    jQuery(document).ready(function($) {
        // Tab functionality
        $('.nav-tab').click(function() {
            var tab = $(this).data('tab');
            $('.nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            $('.tab-pane').removeClass('active');
            $('#' + tab).addClass('active');
        });
    });
    </script>
    <?php
}

// Get service notes
function homelab_get_service_notes($service_id) {
    global $wpdb;

    $table_name_notes = $wpdb->prefix . 'homelab_service_notes';
    $notes = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name_notes WHERE service_id = %d ORDER BY created_at DESC", $service_id));

    return $notes;
}

// Get service check history
function homelab_get_service_check_history($service_id) {
    global $wpdb;

    $table_name_status_logs = $wpdb->prefix . 'homelab_service_status_logs';
    $history = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name_status_logs WHERE service_id = %d ORDER BY check_datetime DESC", $service_id));

    return $history;
}