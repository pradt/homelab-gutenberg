<?php

// Edit service page
function homelab_edit_service_page() {
    global $wpdb;

    $table_name_services = $wpdb->prefix . 'homelab_services';
    $table_name_images = $wpdb->prefix . 'homelab_service_images';

    if (!isset($_GET['service_id'])) {
        // Button to open modal
        echo '<button id="selectServiceBtn">Select Service to Edit</button>';
        
        // Modal placeholder
        echo '<div id="serviceSelectionModal" style="display:none;">
                <h2>Select a Service</h2>
                <ul id="serviceList">';
        
        // Query to get services created by the user
        $services = $wpdb->get_results("SELECT * FROM $table_name_services ORDER BY name ASC");
        foreach ($services as $service) {
            echo '<li><a href="?page=homelab-edit-service&service_id=' . $service->id . '">' . esc_html($service->name) . '</a></li>';
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

    if (isset($_POST['submit'])) {
        // Handle form submission and update the service
        $name = sanitize_text_field($_POST['name']);
        $description = sanitize_textarea_field($_POST['description']);
        $icon = sanitize_text_field($_POST['icon']);
        $image_id = intval($_POST['image']);
        $tags = sanitize_text_field($_POST['tags']);
        $category_id = intval($_POST['category']);
        $color = sanitize_hex_color($_POST['color']);
        $parent_id = intval($_POST['parent']);
        $service_url = sanitize_text_field($_POST['service_url']);
        $alt_page_url = sanitize_text_field($_POST['alt_page_url']);
        $status_check = isset($_POST['status_check']) ? 1 : 0;
        $status_check_url = sanitize_text_field($_POST['status_check_url']);
        $disable_ssl = isset($_POST['disable_ssl']) ? 1 : 0;
        $accepted_response = sanitize_text_field($_POST['accepted_response']);
        $polling_interval = intval($_POST['polling_interval']);
        $notify_email = sanitize_email($_POST['notify_email']);

        // Validate form data
        $errors = array();

        if (empty($name)) {
            $errors[] = 'Service name is required.';
        }

        if ($status_check && empty($status_check_url)) {
            $errors[] = 'Status check URL is required when status check is enabled.';
        }

        if ($status_check && (empty($polling_interval) || !is_numeric($polling_interval) || $polling_interval < 5)) {
            $errors[] = 'Polling interval must be at least 5 seconds.';
        }

        echo '<a href="' . esc_url(add_query_arg(['action' => 'delete', 'service_id' => $service_id, 'confirm' => 'false'], admin_url('admin.php?page=edit-service-page'))) . '" class="button">Delete Service</a>';


        if (!empty($errors)) {
            // Display errors and redisplay the form
            echo '<div class="notice notice-error"><ul>';
            foreach ($errors as $error) {
                echo '<li>' . esc_html($error) . '</li>';
            }
            echo '</ul></div>';
        } else {
            // Update the service fields
            $wpdb->update(
                $table_name_services,
                array(
                    'name' => $name,
                    'description' => $description,
                    'icon' => $icon,
                    'image_id' => $image_id,
                    'tags' => $tags,
                    'category_id' => $category_id,
                    'color' => $color,
                    'parent_id' => $parent_id,
                    'service_url' => $service_url,
                    'alt_page_url' => $alt_page_url,
                    'status_check' => $status_check,
                    'status_check_url' => $status_check_url,
                    'disable_ssl' => $disable_ssl,
                    'accepted_response' => $accepted_response,
                    'polling_interval' => $polling_interval,
                    'notify_email' => $notify_email,
                ),
                array('id' => $service_id)

                
            );

            echo '<style>
.toast {
    position: fixed;
    z-index: 10000;
    top: 20px;
    right: 20px;
    padding: 10px 20px;
    background-color: #44c767;
    color: #ffffff;
    border-radius: 5px;
    box-shadow: 0 0 5px rgba(0,0,0,0.2);
    display: none;
}
</style>';

// After successful update
echo '<script>
jQuery(document).ready(function($) {
    var $toast = $("<div class=\'toast\'>Service has been saved successfully!</div>").appendTo("body");
    $toast.fadeIn(400).delay(3000).fadeOut(400, function() {
        $(this).remove();
    });
});
</script>';



            // Re-schedule the service check if the polling interval or status check settings have changed
            if ($service->status_check) {
                homelab_add_service_check_schedule($service_id, $polling_interval);
            } else {
                wp_clear_scheduled_hook('homelab_check_service_status_' . $service_id);
            }

            //exit;
        }
    }

    // Render the edit service form
    ?>
    <div class="wrap">
        <h1>Edit Service</h1>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th><label for="name">Name</label></th>
                    <td><input type="text" name="name" id="name" class="regular-text" value="<?php echo esc_attr(isset($_POST['name']) ? $_POST['name'] : $service->name); ?>" required></td>
                </tr>
                <tr>
                    <th><label for="description">Description</label></th>
                    <td>
                    <textarea name="description" id="description" rows="4" cols="50"><?php echo esc_textarea(isset($_POST['description']) ? $_POST['description'] : $service->description); ?></textarea>
                </td>
                </tr>
                <tr>
                    <th><label for="icon">Icon</label></th>
                    <td>
                        <select name="icon" id="icon">
                            <option value="">Select an icon</option>
                            <!-- Add Font Awesome icon options here -->
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="image">Service Image</label></th>
                    <td>
                        <select name="image" id="image">
                            <option value="">Select an image</option>
                            <?php
$images = $wpdb->get_results("SELECT * FROM $table_name_images");
foreach ($images as $image) {
    $selected = (isset($_POST['image']) && $_POST['image'] == $image->id) || (!isset($_POST['image']) && $image->id == $service->image_id) ? 'selected' : '';
    echo '<option value="' . $image->id . '" ' . $selected . '>' . $image->name . '</option>';
}
?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="tags">Tags</label></th>
                    <td><input type="text" name="tags" id="tags" class="regular-text" value="<?php echo esc_attr(isset($_POST['tags']) ? $_POST['tags'] : $service->tags); ?>">
</td>
                </tr>
                <tr>
                    <th><label for="category">Category</label></th>
                    <td>
                        <?php
                        wp_dropdown_categories(array(
                            'name' => 'category',
                            'id' => 'category',
                            'taxonomy' => 'category',
                            'hide_empty' => 0,
                            'show_option_none' => 'Select a category',
                            'selected' => $service->category_id,
                        ));
                        ?>
                    </td>
                </tr>
                <tr>
                    <th><label for="color">Color</label></th>
                    <td>
                        <input type="color" name="color" id="color" value="<?php echo esc_attr(isset($_POST['color']) ? $_POST['color'] : $service->color); ?>">
                </td>
                </tr>
                <tr>
                    <th><label for="parent">Parent</label></th>
                    <td>
                        <select name="parent" id="parent">
                            <option value="">Select a parent service</option>
                            <?php
                            $services = $wpdb->get_results("SELECT * FROM $table_name_services");
                            foreach ($services as $s) {
                                if ($s->id != $service_id) {
                                    $selected = (isset($_POST['parent']) && $_POST['parent'] == $s->id) || (!isset($_POST['parent']) && $s->id == $service->parent_id) ? 'selected' : '';
echo '<option value="' . $s->id . '" ' . $selected . '>' . $s->name . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="service_url">Service URL</label></th>
                    <td>
                        <input type="text" name="service_url" id="service_url" class="regular-text" value="<?php echo esc_attr(isset($_POST['service_url']) ? $_POST['service_url'] : $service->service_url); ?>">
                </td>
                </tr>
                <tr>
                    <th><label for="alt_page_url">Alternative Page URL</label></th>
                    <td>
                    <input type="text" name="alt_page_url" id="alt_page_url" class="regular-text" value="<?php echo esc_attr(isset($_POST['alt_page_url']) ? $_POST['alt_page_url'] : $service->alt_page_url); ?>">
                </td>
                </tr>
                <tr>
                    <th><label for="status_check">Status Check</label></th>
                    <td>
                        <input type="checkbox" name="status_check" id="status_check" value="1" <?php checked(isset($_POST['status_check']) ? 1 : $service->status_check, 1); ?>></td>
                </tr>
                <tr class="status-check-fields" style="display: <?php echo $service->status_check ? 'table-row' : 'none'; ?>;">
                    <th><label for="status_check_url">Status Check URL</label></th>
                    <td>
                    <input type="text" name="status_check_url" id="status_check_url" class="regular-text" value="<?php echo esc_attr(isset($_POST['status_check_url']) ? $_POST['status_check_url'] : $service->status_check_url); ?>">
                </td>
                </tr>
                <tr class="status-check-fields" style="display: <?php echo $service->status_check ? 'table-row' : 'none'; ?>;">
                    <th><label for="disable_ssl">Disable SSL</label></th>
                    <td>
                    <input type="checkbox" name="disable_ssl" id="disable_ssl" value="1" <?php checked(isset($_POST['disable_ssl']) ? 1 : $service->disable_ssl, 1); ?>>
                </td>
                </tr>
                <tr class="status-check-fields" style="display: <?php echo $service->status_check ? 'table-row' : 'none'; ?>;">
                    <th><label for="accepted_response">Accepted HTTP Response</label></th>
                    <td><input type="text" name="accepted_response" id="accepted_response" class="regular-text" value="<?php echo esc_attr(isset($_POST['accepted_response']) ? $_POST['accepted_response'] : $service->accepted_response); ?>">
                </td>
                </tr>
                <tr class="status-check-fields" style="display: <?php echo $service->status_check ? 'table-row' : 'none'; ?>;">
                    <th><label for="polling_interval">Polling Interval (seconds)</label></th>
                    <td>
                    <input type="number" name="polling_interval" id="polling_interval" class="regular-text" min="5" value="<?php echo esc_attr(isset($_POST['polling_interval']) ? $_POST['polling_interval'] : $service->polling_interval); ?>">
                </td>
                </tr>
                <tr class="status-check-fields" style="display: <?php echo $service->status_check ? 'table-row' : 'none'; ?>;">
                    <th><label for="notify_email">Notify Email</label></th>
                    <td>
                        <input type="email" name="notify_email" id="notify_email" class="regular-text" value="<?php echo esc_attr(isset($_POST['notify_email']) ? $_POST['notify_email'] : $service->notify_email); ?>">
                </td>
                </tr>
            </table>
            <?php submit_button('Update Service'); ?>
            <br />
            <br />
            <!-- Delete Service Link -->
            <a href="<?php echo esc_url(add_query_arg(['action' => 'delete', 'service_id' => $service->id, 'confirm' => 'false'], $_SERVER['REQUEST_URI'])); ?>" class="button" style="color: red; margin-top: 20px;">Delete Service</a>
        </form>
    </div>
    <script>
    jQuery(document).ready(function($) {
    $('#status_check').change(function() {
        var pollingInterval = $('#polling_interval');
        
        $('.status-check-fields').toggle(this.checked);

        // If status check is unchecked, remove 'required' attribute to avoid validation errors
        if (!this.checked) {
            pollingInterval.removeAttr('required').removeAttr('min').val('');
        } else {
            pollingInterval.attr('required', 'required').attr('min', '5').val('5');
        }
    });
    
    // Trigger change event on page load in case the checkbox is already checked/unchecked
    $('#status_check').change();
});
    </script>
    <?php
}

// Delete service with confirmation
function homelab_delete_service_with_confirmation($service_id) {
    global $wpdb;

    $table_name_services = $wpdb->prefix . 'homelab_services';

    // Check if the service has any child services
    $child_services = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name_services WHERE parent_id = %d", $service_id));

    if (!empty($child_services)) {
        // Remove the parent_id from child services
        $wpdb->update(
            $table_name_services,
            array('parent_id' => 0),
            array('parent_id' => $service_id)
        );
    }

    // Display a confirmation dialog before deleting the service
    if (isset($_GET['confirm']) && $_GET['confirm'] === 'true') {
        homelab_delete_service($service_id);
        wp_redirect(admin_url('admin.php?page=homelab-list-services'));
        exit;
    } else {
        $confirmation_url = admin_url('admin.php?page=homelab-list-services&action=delete&service_id=' . $service_id . '&confirm=true');
        ?>
        <div class="wrap">
            <h1>Delete Service</h1>
            <p>Are you sure you want to delete this service?</p>
            <p>
                <a href="<?php echo esc_url($confirmation_url); ?>" class="button button-primary">Yes, Delete</a>
                <a href="<?php echo admin_url('admin.php?page=homelab-list-services'); ?>" class="button">Cancel</a>
            </p>
        </div>
        <?php
    }
}

// Delete service
function homelab_delete_service($service_id) {
    global $wpdb;

    $table_name_services = $wpdb->prefix . 'homelab_services';
    $table_name_status_logs = $wpdb->prefix . 'homelab_service_status_logs';

    // Delete the service from the services table
    $wpdb->delete($table_name_services, array('id' => $service_id));

    // Delete the associated status logs
    $wpdb->delete($table_name_status_logs, array('service_id' => $service_id));

    // Clear the scheduled service check
    wp_clear_scheduled_hook('homelab_check_service_status_' . $service_id);
}