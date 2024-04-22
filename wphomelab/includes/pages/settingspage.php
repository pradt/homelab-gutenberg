<?php
/****
 * 1. Setting to enable encryption
 *
 * 2. Enable/Deactivate API - done
 * 3. Enable Debug Pages
 * 4. Constrain Service Data table (sechedule jobs daily to limit it to x days) - done
 * 5. Stop all fetchs (pause)
 * 6. Start all fetchs (schedule ) 
 */


/**
 * Render the settings page
 */
function homelab_render_settings_page()
{
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }

    // Get settings from the database
    $settings = get_option('homelab_settings', [
        'api_access' => 'disabled',
        'api_key' => '',
        'data_retention_days' => 60,
        'status_log_retention_days' => 60,
        'service_data_cron_active' => 1,
        'status_log_cron_active' => 1,
    ]);
    ?>
    <div class="wrap">
        <h1>
            <?php echo esc_html(get_admin_page_title()); ?>
        </h1>

        <div id="homelab-settings-notice" class="notice" style="display: none;"></div>

        <form id="homelab-settings-form">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="api_access">API Access</label></th>
                    <td>
                        <label class="switch">
                            <input type="checkbox" id="api_access" name="api_access" <?php checked($settings['api_access'], 'enabled'); ?>>
                            <span class="slider round"></span>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="api_key">API Key</label></th>
                    <td>
                        <input type="text" id="api_key" name="api_key" value="<?php echo esc_attr($settings['api_key']); ?>"
                            class="regular-text" readonly>
                        <button type="button" id="regenerate_api_key" class="button">Regenerate API Key</button>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="data_retention_days">Service Data Retention (Days)</label></th>
                    <td>
                        <input type="number" id="data_retention_days" name="data_retention_days"
                            value="<?php echo esc_attr($settings['data_retention_days']); ?>" min="1">
                        <button type="button" id="set_data_retention_days" class="button">Set</button>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Service Data Cron Job</th>
                    <td>
                        <button type="button" id="activate_service_data_cron" class="button" <?php disabled($settings['service_data_cron_active']); ?>>Activate</button>
                        <button type="button" id="deactivate_service_data_cron" class="button" <?php disabled(!$settings['service_data_cron_active']); ?>>Deactivate</button>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="status_log_retention_days">Status Log Retention (Days)</label></th>
                    <td>
                        <input type="number" id="status_log_retention_days" name="status_log_retention_days"
                            value="<?php echo esc_attr($settings['status_log_retention_days']); ?>" min="1">
                        <button type="button" id="set_status_log_retention_days" class="button">Set</button>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Status Log Cron Job</th>
                    <td>
                        <button type="button" id="activate_status_log_cron" class="button" <?php disabled($settings['status_log_cron_active']); ?>>Activate</button>
                        <button type="button" id="deactivate_status_log_cron" class="button" <?php disabled(!$settings['status_log_cron_active']); ?>>Deactivate</button>
                    </td>
                </tr>
            </table>
        </form>
    </div>
    <script>

        (function ($) {
            $(document).ready(function () {
                // Toggle API access
                $('#api_access').change(function () {
                    var apiAccess = $(this).is(':checked') ? 'enabled' : 'disabled';
                    updateSettings({ api_access: apiAccess });
                });

                // Regenerate API key
                $('#regenerate_api_key').click(function () {
                    var data = {
                        action: 'homelab_regenerate_api_key',
                        nonce: '<?php echo wp_create_nonce("homelab_settings_nonce"); ?>'
                    };
                    $.post(ajaxurl, data, function (response) {
                        if (response.success) {
                            $('#api_key').val(response.data.api_key);
                            showNotice('API key regenerated successfully.', 'success');
                        } else {
                            showNotice('Failed to regenerate API key.', 'error');
                        }
                    });
                });

                // Set service data retention days
                $('#set_data_retention_days').click(function () {
                    var dataRetentionDays = $('#data_retention_days').val();
                    updateSettings({ data_retention_days: dataRetentionDays });
                });

                // Set status log retention days
                $('#set_status_log_retention_days').click(function () {
                    var statusLogRetentionDays = $('#status_log_retention_days').val();
                    updateSettings({ status_log_retention_days: statusLogRetentionDays });
                });

                // Activate/Deactivate service data cron job
                $('#activate_service_data_cron, #deactivate_service_data_cron').click(function () {
                    var action = $(this).attr('id') === 'activate_service_data_cron' ? 'activate' : 'deactivate';
                    var data = {
                        action: 'homelab_toggle_service_data_cron',
                        nonce: '<?php echo wp_create_nonce("homelab_settings_nonce"); ?>',
                        service_data_cron_active: action === 'activate' ? 1 : 0
                    };
                    $.post(ajaxurl, data, function (response) {
                        if (response.success) {
                            $('#activate_service_data_cron').prop('disabled', response.data.service_data_cron_active);
                            $('#deactivate_service_data_cron').prop('disabled', !response.data.service_data_cron_active);
                            showNotice('Service data cron job ' + action + 'd successfully.', 'success');
                        } else {
                            showNotice('Failed to ' + action + ' service data cron job.', 'error');
                        }
                    });
                });

                // Activate/Deactivate status log cron job
                $('#activate_status_log_cron, #deactivate_status_log_cron').click(function () {
                    var action = $(this).attr('id') === 'activate_status_log_cron' ? 'activate' : 'deactivate';
                    var data = {
                        action: 'homelab_toggle_status_log_cron',
                        nonce: '<?php echo wp_create_nonce("homelab_settings_nonce"); ?>',
                        status_log_cron_active: action === 'activate' ? 1 : 0
                    };
                    $.post(ajaxurl, data, function (response) {
                        if (response.success) {
                            $('#activate_status_log_cron').prop('disabled', response.data.status_log_cron_active);
                            $('#deactivate_status_log_cron').prop('disabled', !response.data.status_log_cron_active);
                            showNotice('Status log cron job ' + action + 'd successfully.', 'success');
                        } else {
                            showNotice('Failed to ' + action + ' status log cron job.', 'error');
                        }
                    });
                });

                // Update settings via AJAX
                function updateSettings(data) {
                    data.action = 'homelab_update_settings';
                    data.nonce = '<?php echo wp_create_nonce("homelab_settings_nonce"); ?>';
                    $.post(ajaxurl, data, function (response) {
                        if (response.success) {
                            showNotice('Settings updated successfully.', 'success');
                        } else {
                            showNotice('Failed to update settings.', 'error');
                        }
                    });
                }

                // Show notice message
                function showNotice(message, type) {
                    var notice = $('#homelab-settings-notice');
                    notice.text(message).removeClass('notice-success notice-error').addClass('notice-' + type).fadeIn();
                    setTimeout(function () {
                        notice.fadeOut();
                    }, 3000);
                }
            });
        })(jQuery);

    </script>
    <?php
}

/**
 * AJAX handler for updating settings
 */
add_action('wp_ajax_homelab_update_settings', 'homelab_update_settings');
function homelab_update_settings()
{
    // Check nonce for security
    check_ajax_referer('homelab_settings_nonce', 'nonce');

    // Get current settings
    $settings = get_option('homelab_settings', []);

    // Update settings based on the request
    if (isset($_POST['api_access'])) {
        $settings['api_access'] = sanitize_text_field($_POST['api_access']);
    }
    if (isset($_POST['api_key'])) {
        $settings['api_key'] = sanitize_text_field($_POST['api_key']);
    }
    if (isset($_POST['data_retention_days'])) {
        $settings['data_retention_days'] = intval($_POST['data_retention_days']);
    }
    if (isset($_POST['status_log_retention_days'])) {
        $settings['status_log_retention_days'] = intval($_POST['status_log_retention_days']);
    }
    if (isset($_POST['service_data_cron_active'])) {
        $settings['service_data_cron_active'] = intval($_POST['service_data_cron_active']);
    }
    if (isset($_POST['status_log_cron_active'])) {
        $settings['status_log_cron_active'] = intval($_POST['status_log_cron_active']);
    }

    // Save updated settings
    update_option('homelab_settings', $settings);

    // Send success response
    wp_send_json_success($settings);
}

/**
 * Generate a new API key
 */
function homelab_generate_api_key()
{
    return wp_generate_password(32, false);
}

/**
 * AJAX handler for regenerating API key
 */
add_action('wp_ajax_homelab_regenerate_api_key', 'homelab_regenerate_api_key');
function homelab_regenerate_api_key()
{
    // Check nonce for security
    check_ajax_referer('homelab_settings_nonce', 'nonce');

    // Generate new API key
    $api_key = homelab_generate_api_key();

    // Update settings
    $settings = get_option('homelab_settings', []);
    $settings['api_key'] = $api_key;
    update_option('homelab_settings', $settings);

    // Send success response
    wp_send_json_success(['api_key' => $api_key]);
}

/**
 * Activate/Deactivate Service Data Cron Job
 */
add_action('wp_ajax_homelab_toggle_service_data_cron', 'homelab_toggle_service_data_cron');
function homelab_toggle_service_data_cron()
{
    // Check nonce for security
    check_ajax_referer('homelab_settings_nonce', 'nonce');

    // Get current settings
    $settings = get_option('homelab_settings', []);

    // Toggle cron job state
    $settings['service_data_cron_active'] = !$settings['service_data_cron_active'];

    if ($settings['service_data_cron_active']) {
        // Schedule the cron job
        wp_schedule_event(time(), 'daily', 'homelab_delete_old_service_data');
    } else {
        // Unschedule the cron job
        wp_clear_scheduled_hook('homelab_delete_old_service_data');
    }

    // Save updated settings
    update_option('homelab_settings', $settings);

    // Send success response
    wp_send_json_success(['service_data_cron_active' => $settings['service_data_cron_active']]);
}

/**
 * Activate/Deactivate Status Log Cron Job
 */
add_action('wp_ajax_homelab_toggle_status_log_cron', 'homelab_toggle_status_log_cron');
function homelab_toggle_status_log_cron()
{
    // Check nonce for security
    check_ajax_referer('homelab_settings_nonce', 'nonce');

    // Get current settings
    $settings = get_option('homelab_settings', []);

    // Toggle cron job state
    $settings['status_log_cron_active'] = !$settings['status_log_cron_active'];

    if ($settings['status_log_cron_active']) {
        // Schedule the cron job
        wp_schedule_event(time(), 'daily', 'homelab_delete_old_status_logs');
    } else {
        // Unschedule the cron job
        wp_clear_scheduled_hook('homelab_delete_old_status_logs');
    }

    // Save updated settings
    update_option('homelab_settings', $settings);

    // Send success response
    wp_send_json_success(['status_log_cron_active' => $settings['status_log_cron_active']]);
}