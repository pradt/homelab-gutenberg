<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
// View service page
function homelab_view_service_page()
{
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
            jQuery(document).ready(function ($) {
                $('#selectServiceBtn').click(function () {
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
        <h1>
            <?php echo esc_html($service->name); ?>
        </h1>
        <div class="service-actions">
            <?php if ($service->get_data): ?>
                <button id="startDataFetchBtn" class="button button-primary" data-service-id="<?php echo $service_id; ?>">Start
                    Data Fetch</button>
                <button id="stopDataFetchBtn" class="button button-secondary" data-service-id="<?php echo $service_id; ?>">Stop
                    Data Fetch</button>
            <?php endif; ?>
        </div>
        <div class="service-details">
            <div class="service-image">
                <?php if ($service->image_id): ?>
                    <?php $image_url = wp_get_attachment_url($service->image_id); ?>
                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($service->name); ?>">
                <?php else: ?>
                    <img src="<?php echo esc_url(plugins_url('default-service-image.png', __FILE__)); ?>"
                        alt="Default Service Image">
                <?php endif; ?>
            </div>
            <div class="service-info">
                <p><strong>Service/Application:</strong>
                    <?php echo esc_html($service->service_type); ?>
                </p>
                <p><strong>Description:</strong>
                    <?php echo esc_html($service->description); ?>
                </p>
                <p><strong>Icon:</strong>
                    <?php echo esc_html($service->icon); ?>
                </p>
                <p><strong>Tags:</strong>
                    <?php echo esc_html($service->tags); ?>
                </p>
                <p><strong>Category:</strong>
                    <?php echo esc_html(get_cat_name($service->category_id)); ?>
                </p>
                <p><strong>Color:</strong> <span
                        style="display: inline-block; width: 20px; height: 20px; background-color: <?php echo esc_attr($service->color); ?>;"></span>
                </p>
                <p><strong>Parent:</strong>
                    <?php
                    if ($service->parent_id) {
                        $parent_service = $wpdb->get_row($wpdb->prepare("SELECT name FROM $table_name_services WHERE id = %d", $service->parent_id));
                        echo esc_html($parent_service->name);
                    } else {
                        echo 'None';
                    }
                    ?>
                </p>
                <p><strong>Service URL:</strong> <a href="<?php echo esc_url($service->service_url); ?>" target="_blank">
                        <?php echo esc_html($service->service_url); ?>
                    </a></p>
                <p><strong>Alternative Page URL:</strong> <a href="<?php echo esc_url($service->alt_page_url); ?>"
                        target="_blank">
                        <?php echo esc_html($service->alt_page_url); ?>
                    </a></p>
                <p><strong>Status Check:</strong>
                    <?php echo $service->status_check ? 'Enabled' : 'Disabled'; ?>
                </p>
                <?php if ($service->status_check): ?>
                    <p><strong>Status Check URL:</strong>
                        <?php echo esc_html($service->status_check_url); ?>
                    </p>
                    <p><strong>Disable SSL:</strong>
                        <?php echo $service->disable_ssl ? 'Yes' : 'No'; ?>
                    </p>
                    <p><strong>Accepted HTTP Response:</strong>
                        <?php echo esc_html($service->accepted_response); ?>
                    </p>
                    <p><strong>Polling Interval:</strong>
                        <?php echo esc_html($service->polling_interval); ?> seconds
                    </p>
                    <p><strong>Notify Email:</strong>
                        <?php echo esc_html($service->notify_email); ?>
                    </p>
                <?php endif; ?>
            </div>

            <p><strong>Get Data:</strong>
                <?php echo $service->get_data ? 'Yes' : 'No'; ?>
            </p>
            <p><strong>Fetch Interval:</strong>
                <?php echo esc_html($service->fetch_interval); ?> seconds
            </p>
            <p><strong>API URL:</strong>
                <?php echo esc_html($service->api_url); ?>
            </p>
            <p><strong>API Key:</strong>
                <?php echo esc_html($service->api_key); ?>
            </p>
            <p><strong>Username:</strong>
                <?php echo esc_html($service->username); ?>
            </p>
            <p><strong>Password:</strong>
                <?php echo esc_html($service->password); ?>
            </p>
            <p><strong>Tunnel ID:</strong>
                <?php echo esc_html($service->tunnel_id); ?>
            </p>
            <p><strong>Account ID:</strong>
                <?php echo esc_html($service->account_id); ?>
            </p>
            <p><strong>Enable Now Playing:</strong>
                <?php echo $service->enable_now_playing ? 'Yes' : 'No'; ?>
            </p>
            <p><strong>Enable Blocks:</strong>
                <?php echo $service->enable_blocks ? 'Yes' : 'No'; ?>
            </p>
            <p><strong>Check UUID:</strong>
                <?php echo esc_html($service->check_uuid); ?>
            </p>
            <p><strong>Client ID:</strong>
                <?php echo esc_html($service->client_id); ?>
            </p>
            <p><strong>Toots Limit:</strong>
                <?php echo esc_html($service->toots_limit); ?>
            </p>
            <p><strong>Server Port:</strong>
                <?php echo esc_html($service->server_port); ?>
            </p>
            <p><strong>Volume:</strong>
                <?php echo esc_html($service->volume); ?>
            </p>


        </div>
        <div class="service-timers">
            <?php if ($service->status_check): ?>
                <div class="timer">
                    <h3>Next Status Check:</h3>
                    <div id="status-check-timer"
                        data-timestamp="<?php echo wp_next_scheduled('homelab_check_service_status_' . $service_id); ?>"></div>
                </div>
            <?php endif; ?>
            <?php if ($service->get_data): ?>
                <div class="timer">
                    <h3>Next Data Fetch:</h3>
                    <div id="data-fetch-timer"
                        data-timestamp="<?php echo wp_next_scheduled('homelab_fetch_service_data', array($service_id)); ?>">
                    </div>
                </div>
            <?php endif; ?>
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
                                    <div class="note-avatar">
                                        <?php echo $user_avatar; ?>
                                    </div>
                                    <div class="note-author">
                                        <?php echo esc_html($user->display_name); ?>
                                    </div>
                                    <div class="note-date">
                                        <?php echo esc_html($created_at); ?>
                                    </div>
                                </div>
                                <div class="note-content">
                                    <?php echo esc_html($note->note); ?>
                                </div>
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
                            jQuery(document).ready(function ($) {
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
                <div class="tab-pane" id="history">
                    <?php
                    $records_per_page = 10;
                    $current_page = isset($_GET['page_num']) ? intval($_GET['page_num']) : 1;
                    $offset = ($current_page - 1) * $records_per_page;

                    $history = homelab_get_service_data_history($service_id, $records_per_page, $offset);
                    $total_records = homelab_get_service_data_history_count($service_id);
                    $total_pages = ceil($total_records / $records_per_page);

                    if (!empty($history)) {
                        echo '<ul class="service-data-history">';
                        foreach ($history as $data) {
                            $fetched_at = date('Y-m-d H:i:s', strtotime($data->fetched_at));
                            $json_data = json_decode($data->data, true);
                            $error_class = $data->error_message ? 'error' : '';
                            ?>
                            <li class="<?php echo $error_class; ?>">
                                <div class="data-header">
                                    <div class="data-timestamp">
                                        <?php echo esc_html($fetched_at); ?>
                                    </div>
                                    <?php if ($data->error_message): ?>
                                        <div class="data-error">
                                            <?php echo esc_html($data->error_message); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="data-content">
                                    <pre><code><?php echo json_encode($json_data, JSON_PRETTY_PRINT); ?></code></pre>
                                </div>
                            </li>
                            <?php
                        }
                        echo '</ul>';

                        // Pagination links
                        if ($total_pages > 1) {
                            echo '<div class="pagination">';
                            for ($i = 1; $i <= $total_pages; $i++) {
                                $active_class = ($i == $current_page) ? 'active' : '';
                                echo '<a href="?page=homelab-view-service&service_id=' . $service_id . '&page_num=' . $i . '" class="' . $active_class . '">' . $i . '</a>';
                            }
                            echo '</div>';
                        }
                    } else {
                        echo '<p>No service data history found.</p>';
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

        .service-timers {
            display: flex;
            margin-bottom: 20px;
        }

        .timer {
            flex: 1;
            text-align: center;
            padding: 10px;
            background-color: #f1f1f1;
            border-radius: 5px;
            margin-right: 10px;
        }

        .timer:last-child {
            margin-right: 0;
        }

        .service-data-history {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .service-data-history li {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .service-data-history li.error {
            background-color: #ffebeb;
            border-color: #ff0000;
        }

        .data-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .data-timestamp {
            font-weight: bold;
        }

        .data-error {
            color: #ff0000;
        }

        .data-content pre {
            margin: 0;
            padding: 10px;
            background-color: #f1f1f1;
            border-radius: 5px;
        }

        .pagination {
            margin-top: 20px;
            text-align: center;
        }

        .pagination a {
            display: inline-block;
            padding: 5px 10px;
            margin: 0 5px;
            background-color: #f1f1f1;
            border-radius: 5px;
            text-decoration: none;
            color: #333;
        }

        .pagination a.active {
            background-color: #333;
            color: #fff;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/jquery.json-viewer@1.4.0/json-viewer/jquery.json-viewer.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery.json-viewer@1.4.0/json-viewer/jquery.json-viewer.css">
    <script>
        jQuery(document).ready(function ($) {
            // Tab functionality
            $('.nav-tab').click(function () {
                var tab = $(this).data('tab');
                $('.nav-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                $('.tab-pane').removeClass('active');
                $('#' + tab).addClass('active');
            });

            // Countdown timer functionality
            function startTimer(elementId, timestamp) {
                var countDownDate = new Date(timestamp * 1000).getTime();

                var x = setInterval(function () {
                    var now = new Date().getTime();
                    var distance = countDownDate - now;

                    var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    $('#' + elementId).html(days + "d " + hours + "h " + minutes + "m " + seconds + "s ");

                    if (distance < 0) {
                        clearInterval(x);
                        $('#' + elementId).html("Expired");
                    }
                }, 1000);
            }

            var statusCheckTimestamp = $('#status-check-timer').data('timestamp');
            if (statusCheckTimestamp) {
                startTimer('status-check-timer', statusCheckTimestamp);
            }

            var dataFetchTimestamp = $('#data-fetch-timer').data('timestamp');
            if (dataFetchTimestamp) {
                startTimer('data-fetch-timer', dataFetchTimestamp);
            }

            $('.data-content pre code').each(function () {
                var jsonData = JSON.parse($(this).text());
                $(this).jsonViewer(jsonData, {collapsed: true});
            });

            // Start data fetch button click event
            $('#startDataFetchBtn').click(function () {
                var serviceId = $(this).data('service-id');
                var fetchInterval = <?php echo $service->fetch_interval; ?>;

                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'homelab_start_data_fetch',
                        service_id: serviceId,
                        fetch_interval: fetchInterval
                    },
                    success: function (response) {
                        if (response.success) {
                            // Display success toast notification
                            // You can use a toast library or custom implementation here
                            alert('Data fetch started successfully.');
                        } else {
                            // Display error toast notification
                            alert('Failed to start data fetch.');
                        }
                    }
                });
            });

            // Stop data fetch button click event
            $('#stopDataFetchBtn').click(function () {
                var serviceId = $(this).data('service-id');

                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'homelab_stop_data_fetch',
                        service_id: serviceId
                    },
                    success: function (response) {
                        if (response.success) {
                            // Display success toast notification
                            // You can use a toast library or custom implementation here
                            alert('Data fetch stopped successfully.');
                        } else {
                            // Display error toast notification
                            alert('Failed to stop data fetch.');
                        }
                    }
                });
            });
        });
    </script>
    <?php
}

// Get service notes
function homelab_get_service_notes($service_id)
{
    global $wpdb;

    $table_name_notes = $wpdb->prefix . 'homelab_service_notes';
    $notes = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name_notes WHERE service_id = %d ORDER BY created_at DESC", $service_id));

    return $notes;
}

// Get service check history
function homelab_get_service_check_history($service_id)
{
    global $wpdb;

    $table_name_status_logs = $wpdb->prefix . 'homelab_service_status_logs';
    $history = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name_status_logs WHERE service_id = %d ORDER BY check_datetime DESC", $service_id));

    return $history;
}

function homelab_get_service_data_history($service_id, $limit, $offset)
{
    global $wpdb;
    $table_name_service_data = $wpdb->prefix . 'homelab_service_data';
    $history = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name_service_data WHERE service_id = %d ORDER BY fetched_at DESC LIMIT %d OFFSET %d", $service_id, $limit, $offset));
    return $history;
}

function homelab_get_service_data_history_count($service_id)
{
    global $wpdb;
    $table_name_service_data = $wpdb->prefix . 'homelab_service_data';
    $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name_service_data WHERE service_id = %d", $service_id));
    return $count;
}

