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
                    Data Fetch</button> |
            <?php endif; ?>

            <button class="button add-note-btn">Add Note</button>
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
                <li class="nav-tab" data-tab="history">Service Check</li>
                <li class="nav-tab" data-tab="data-fetch">Data Fetchs</li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="notes">
                    <div class="notes-content"></div>
                    <div class="notes-pagination"></div>
                    <button class="button refresh-notes">Refresh Notes</button>
                </div>
                <div class="tab-pane" id="history">
                    <div class="history-content"></div>
                    <div class="history-pagination"></div>
                    <button class="button refresh-history">Refresh Service Check</button>
                </div>
                <div class="tab-pane" id="data-fetch">
                    <div class="data-fetch-content"></div>
                    <div class="data-fetch-pagination"></div>
                    <button class="button refresh-data-fetch">Refresh Data Fetchs</button>
                </div>
            </div>
        </div>
    </div>

    <!--Modal -->
    <div id="add-note-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Add Note</h2>
            <textarea id="note-text" rows="4" cols="50"></textarea>
            <button class="button save-note-btn">Save Note</button>
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

        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
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
                $(this).jsonViewer(jsonData, { collapsed: true });
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

            // Load tab content on page load
            loadNotes(1);
            loadHistory();
            loadDataFetch(1);

            // Refresh buttons
            $('.refresh-notes').click(function () {
                loadNotes(1);
            });

            $('.refresh-history').click(function () {
                loadHistory();
            });

            $('.refresh-data-fetch').click(function () {
                loadDataFetch(1);
            });

            // Add note modal
            var modal = $('#add-note-modal');
            var addNoteBtn = $('.add-note-btn');
            var closeBtn = $('.close');
            var saveNoteBtn = $('.save-note-btn');

            addNoteBtn.click(function () {
                modal.show();
            });

            closeBtn.click(function () {
                modal.hide();
            });

            saveNoteBtn.click(function () {
                var noteText = $('#note-text').val();
                var serviceId = <?php echo $service_id; ?>;

                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'homelab_add_service_note',
                        service_id: serviceId,
                        note: noteText
                    },
                    success: function (response) {
                        if (response.success) {
                            // Clear note text
                            $('#note-text').val('');
                            // Hide modal
                            modal.hide();
                            // Refresh notes
                            loadNotes(1);
                        } else {
                            // Display error toast notification
                            alert('Failed to add note.');
                        }
                    }
                });
            });

            // Load notes via AJAX
            function loadNotes(page) {
                var serviceId = <?php echo $service_id; ?>;

                $.ajax({
                    url: ajaxurl,
                    method: 'GET',
                    data: {
                        action: 'homelab_get_service_notes',
                        service_id: serviceId,
                        page: page
                    },
                    success: function (response) {
                        var notesHtml = '';
                        if (response.notes.length > 0) {
                            notesHtml = '<ul class="service-notes">';
                            $.each(response.notes, function (index, note) {
                                notesHtml += '<li>' +
                                    '<div class="note-header">' +
                                    '<div class="note-avatar">' + note.user_avatar + '</div>' +
                                    '<div class="note-author">' + note.user_name + '</div>' +
                                    '<div class="note-date">' + note.created_at + '</div>' +
                                    '</div>' +
                                    '<div class="note-content">' + note.note + '</div>' +
                                    '</li>';
                            });
                            notesHtml += '</ul>';
                        } else {
                            notesHtml = '<p>No notes found.</p>';
                        }

                        $('.notes-content').html(notesHtml);

                        var paginationHtml = '';
                        if (response.total_pages > 1) {
                            paginationHtml = '<div class="pagination">';
                            for (var i = 1; i <= response.total_pages; i++) {
                                var activeClass = (i == page) ? 'active' : '';
                                paginationHtml += '<a href="#" class="' + activeClass + '" data-page="' + i + '">' + i + '</a>';
                            }
                            paginationHtml += '</div>';
                        }
                        $('.notes-pagination').html(paginationHtml);
                    }
                });

            }

            // Load service check history via AJAX
            function loadHistory() {
                var serviceId = <?php echo $service_id; ?>;

                $.ajax({
                    url: ajaxurl,
                    method: 'GET',
                    data: {
                        action: 'homelab_get_service_check_history',
                        service_id: serviceId
                    },
                    success: function (response) {
                        var historyHtml = '';
                        if (response.history.length > 0) {
                            var labels = [];
                            var responseCodes = [];
                            var statuses = [];

                            $.each(response.history, function (index, check) {
                                labels.push(check.check_datetime);
                                responseCodes.push(check.response_code);
                                statuses.push(check.status);
                            });

                            historyHtml = '<canvas id="service-history-chart"></canvas>';

                            $('.history-content').html(historyHtml);

                            var ctx = document.getElementById('service-history-chart').getContext('2d');
                            var chart = new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: labels,
                                    datasets: [{
                                        label: 'Response Code',
                                        data: responseCodes,
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

                            //$('.history-pagination').html(paginationHtml);

                        } else {
                            historyHtml = '<p>No service check history found.</p>';
                            $('.history-content').html(historyHtml);
                        }
                    }
                });
            }

            // Load data fetch history via AJAX
            function loadDataFetch(page) {
                var serviceId = <?php echo $service_id; ?>;

                $.ajax({
                    url: ajaxurl,
                    method: 'GET',
                    data: {
                        action: 'homelab_get_service_data_history',
                        service_id: serviceId,
                        page: page
                    },
                    success: function (response) {
                        var dataFetchHtml = '';
                        if (response.history.length > 0) {
                            dataFetchHtml = '<ul class="service-data-history">';
                            $.each(response.history, function (index, data) {
                                var errorClass = data.error_message ? 'error' : '';
                                dataFetchHtml += '<li class="' + errorClass + '">' +
                                    '<div class="data-header">' +
                                    '<div class="data-timestamp">' + data.fetched_at + '</div>' +
                                    (data.error_message ? '<div class="data-error">' + data.error_message + '</div>' : '') +
                                    '</div>' +
                                    '<div class="data-content">' +
                                    '<pre><code>' + JSON.stringify(data.data, null, 2) + '</code></pre>' +
                                    '</div>' +
                                    '</li>';
                            });
                            dataFetchHtml += '</ul>';
                        } else {
                            dataFetchHtml = '<p>No service data history found.</p>';
                        }

                        $('.data-fetch-content').html(dataFetchHtml);

                        var paginationHtml = '';
                        if (response.total_pages > 1) {
                            paginationHtml = '<div class="pagination">';
                            for (var i = 1; i <= response.total_pages; i++) {
                                var activeClass = (i == page) ? 'active' : '';
                                paginationHtml += '<a href="#" class="' + activeClass + '" data-page="' + i + '">' + i + '</a>';
                            }
                            paginationHtml += '</div>';
                        }

                        $('.data-fetch-pagination').html(paginationHtml);

                        $('.data-content pre code').each(function () {
                            var jsonData = JSON.parse($(this).text());
                            $(this).jsonViewer(jsonData, { collapsed: true });
                        });
                    }
                });
            }

            // Pagination click event
            $(document).on('click', '.pagination a', function (e) {
                e.preventDefault();
                var page = $(this).data('page');
                var tab = $('.nav-tab-active').data('tab');

                if (tab === 'notes') {
                    loadNotes(page);
                } else if (tab === 'data-fetch') {
                    loadDataFetch(page);
                }
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

// AJAX action to get service notes
add_action('wp_ajax_homelab_get_service_notes', 'homelab_ajax_get_service_notes');
function homelab_ajax_get_service_notes()
{
    $service_id = intval($_GET['service_id']);
    $page = intval($_GET['page']);
    $notes_per_page = 10;
    $offset = ($page - 1) * $notes_per_page;
    $notes = homelab_get_service_notes($service_id, $notes_per_page, $offset);
    $total_notes = homelab_get_service_notes_count($service_id);
    $total_pages = ceil($total_notes / $notes_per_page);

    $notes_data = array();
    foreach ($notes as $note) {
        $user = get_userdata($note->user_id);
        $notes_data[] = array(
            'user_avatar' => get_avatar($note->user_id, 32),
            'user_name' => $user->display_name,
            'created_at' => date('Y-m-d H:i:s', strtotime($note->created_at)),
            'note' => $note->note
        );
    }

    wp_send_json(
        array(
            'notes' => $notes_data,
            'total_pages' => $total_pages
        )
    );
}

function homelab_get_service_notes_count($service_id) {
    global $wpdb;
    $table_name_notes = $wpdb->prefix . 'homelab_service_notes';
    $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name_notes WHERE service_id = %d", $service_id));
    return intval($count);
}

// AJAX action to get service check history
add_action('wp_ajax_homelab_get_service_check_history', 'homelab_ajax_get_service_check_history');
function homelab_ajax_get_service_check_history()
{
    $service_id = intval($_GET['service_id']);
    $history = homelab_get_service_check_history($service_id);
    $history_data = array();
    foreach ($history as $check) {
        $history_data[] = array(
            'check_datetime' => $check->check_datetime,
            'response_code' => $check->response_code,
            'status' => $check->status
        );
    }

    wp_send_json(
        array(
            'history' => $history_data
        )
    );
}

// AJAX action to get service data history
add_action('wp_ajax_homelab_get_service_data_history', 'homelab_ajax_get_service_data_history');
function homelab_ajax_get_service_data_history()
{
    $service_id = intval($_GET['service_id']);
    $page = intval($_GET['page']);
    $records_per_page = 10;
    $offset = ($page - 1) * $records_per_page;
    $history = homelab_get_service_data_history($service_id, $records_per_page, $offset);
    $total_records = homelab_get_service_data_history_count($service_id);
    $total_pages = ceil($total_records / $records_per_page);

    $history_data = array();
    foreach ($history as $data) {
        $history_data[] = array(
            'fetched_at' => date('Y-m-d H:i:s', strtotime($data->fetched_at)),
            'data' => json_decode($data->data, true),
            'error_message' => $data->error_message
        );
    }

    wp_send_json(
        array(
            'history' => $history_data,
            'total_pages' => $total_pages
        )
    );
}

// AJAX action to add a service note
add_action('wp_ajax_homelab_add_service_note', 'homelab_ajax_add_service_note');
function homelab_ajax_add_service_note()
{
    $service_id = intval($_POST['service_id']);
    $note = sanitize_text_field($_POST['note']);
    $user_id = get_current_user_id();

    global $wpdb;
    $table_name_notes = $wpdb->prefix . 'homelab_service_notes';
    $result = $wpdb->insert($table_name_notes, array(
        'service_id' => $service_id,
        'user_id' => $user_id,
        'note' => $note,
        'created_at' => current_time('mysql')
    )
    );

    if ($result) {
        wp_send_json_success();
    } else {
        wp_send_json_error();
    }

}