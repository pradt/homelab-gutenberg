<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// List services page callback
function homelab_list_services_page() {
    global $wpdb;

    $table_name_services = $wpdb->prefix . 'homelab_services';
    $services = $wpdb->get_results("SELECT * FROM $table_name_services");

    

    ?>
    <div class="wrap">
    <h1>List Services</h1>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($services as $service) : ?>
                <tr>
                    <td>
                        <?php
                        if ($service->image_id) {
                            $image_url = wp_get_attachment_url($service->image_id);
                            echo '<img src="' . esc_url($image_url) . '" alt="Service Image" width="50" height="50">';
                        } elseif ($service->icon) {
                            echo '<i class="' . esc_attr($service->icon) . '"></i>';
                        }
                        ?>
                    </td>
                    <td><?php echo esc_html($service->name); ?></td>
                    <td>
                        <?php
                        if ($service->status_check) {
                            $status = homelab_get_latest_service_status($service->id);
                            $status_class = strtolower($status);
                            echo '<span class="status-icon ' . esc_attr($status_class) . '"></span>';
                        } else {
                            echo '?';
                        }
                        ?>
                    </td>
                    <td>
                        <a href="<?php echo admin_url('admin.php?page=homelab-edit-service&service_id=' . $service->id); ?>">Edit</a> |
                        <?php if ($show_tags_button && $service->tags) : ?>
                            <?php
                            $tags = explode(',', $service->tags);
                            foreach ($tags as $tag) {
                                $tag = trim($tag);
                                $tag_term = get_term_by('name', $tag, 'post_tag');
                                if ($tag_term) {
                                    $tag_link = get_tag_link($tag_term->term_id);
                                    echo '<a href="' . esc_url($tag_link) . '" class="homelab-service-tags-button">' . esc_html($tag) . '</a> ';
                                }
                            }
                            ?>
                        <?php endif; ?>
                        <a href="<?php echo admin_url('admin.php?page=homelab-add-note&service_id=' . $service->id); ?>">Add Note</a> |
                        <a href="<?php echo admin_url('admin.php?page=homelab-view-service&service_id=' . $service->id); ?>">View</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

    <?php
}