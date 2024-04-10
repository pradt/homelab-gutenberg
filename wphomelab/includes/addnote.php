<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Add note page
function homelab_add_note_page() {
    global $wpdb;

    $table_name_notes = $wpdb->prefix . 'homelab_service_notes';

    if (isset($_GET['service_id'])) {
        $service_id = intval($_GET['service_id']);
    } else {
        echo '<div class="notice notice-error"><p>Invalid service ID.</p></div>';
        return;
    }

    if (isset($_POST['submit'])) {
        // Handle form submission and add the note
        $note = sanitize_textarea_field($_POST['note']);
        $user_id = get_current_user_id();

        if (empty($note)) {
            echo '<div class="notice notice-error"><p>Please enter a note.</p></div>';
        } else {
            $wpdb->insert(
                $table_name_notes,
                array(
                    'service_id' => $service_id,
                    'user_id' => $user_id,
                    'note' => $note,
                    'created_at' => current_time('mysql'),
                )
            );

            // Redirect to the view service page after adding the note
            wp_redirect(admin_url('admin.php?page=homelab-view-service&service_id=' . $service_id));
            exit;
        }
    }

    // Render the add note form
    ?>
    <div class="wrap">
        <h1>Add Note</h1>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th><label for="note">Note</label></th>
                    <td><textarea name="note" id="note" rows="5" cols="50" required></textarea></td>
                </tr>
            </table>
            <?php submit_button('Add Note'); ?>
        </form>
    </div>
    <?php
}