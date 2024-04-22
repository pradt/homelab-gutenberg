<?php
// Add the admin menu page
/* add_action('admin_menu', 'homelab_add_admin_menu');
function homelab_add_admin_menu() {
    add_menu_page('HomeLab Services', 'HomeLab Services', 'manage_options', 'homelab-services', 'homelab_services_page');
}
 */
// Render the admin page
function homelab_services_page()
{
    global $wpdb;

    // Get the alignment options from the query string
    $parent_alignment = isset($_GET['parent_alignment']) ? $_GET['parent_alignment'] : 'horizontal';
    $child_alignment = isset($_GET['child_alignment']) ? $_GET['child_alignment'] : 'vertical';

    // Get the services from the database
    $table_name_services = $wpdb->prefix . 'homelab_services';
    $services = $wpdb->get_results("SELECT * FROM $table_name_services");

    // Group the services by parent_id
    $grouped_services = array();
    foreach ($services as $service) {
        $parent_id = $service->parent_id ? $service->parent_id : 0;
        $grouped_services[$parent_id][] = $service;
    }

    // Start the output
    ?>
    <div class="wrap">
        <h1>HomeLab Services</h1>

        <form method="get" action="">
            <input type="hidden" name="page" value="homelab-services">
            <label for="parent_alignment">Parent Alignment:</label>
            <select name="parent_alignment" id="parent_alignment">
                <option value="horizontal" <?php selected($parent_alignment, 'horizontal'); ?>>Horizontal</option>
                <option value="vertical" <?php selected($parent_alignment, 'vertical'); ?>>Vertical</option>
            </select>
            <label for="child_alignment">Child Alignment:</label>
            <select name="child_alignment" id="child_alignment">
                <option value="horizontal" <?php selected($child_alignment, 'horizontal'); ?>>Horizontal</option>
                <option value="vertical" <?php selected($child_alignment, 'vertical'); ?>>Vertical</option>
            </select>
            <button type="submit" class="button">Apply</button>
        </form>

        <div class="homelab-services-container <?php echo $parent_alignment; ?>">
            <?php homelab_render_services($grouped_services, $grouped_services[0], $child_alignment); ?>
        </div>
    </div>
    <?php
}

// Render the services recursively
function homelab_render_services($grouped_services, $services, $child_alignment)
{
    global $wpdb;

    foreach ($services as $service) {

        // Get the image URL if available
        $image_url = '';
        if ($service->image_id) {
            $table_name_images = $wpdb->prefix . 'homelab_service_images';
            $image = $wpdb->get_row("SELECT url FROM $table_name_images WHERE id = $service->image_id");
            if ($image) {
                $image_url = $image->url;
            }
        }

        // Determine the service status and color
        $status_class = 'status-unknown';
        $status_color = '#999';
        if ($service->status_check) {
            $table_name_status_logs = $wpdb->prefix . 'homelab_service_status_logs';
            $latest_status = $wpdb->get_row("SELECT status FROM $table_name_status_logs WHERE service_id = $service->id ORDER BY check_datetime DESC LIMIT 1");
            if ($latest_status) {
                $status_class = 'status-' . $latest_status->status;
                $status_color = $latest_status->status === 'up' ? '#0f0' : '#f00';
            }
        }

        // Render the service card
        ?>
        <div class="homelab-service-block">
            <div class="homelab-service-header">
                <?php if ($service->icon): ?>
                    <div class="homelab-service-icon">
                        <i class="<?php echo esc_attr($service->icon); ?>"></i>
                    </div>
                <?php endif; ?>
                <?php if ($image_url): ?>
                    <div class="homelab-service-image">
                        <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($service->name); ?>">
                    </div>
                <?php endif; ?>
                <h3 class="homelab-service-title">
                    <?php echo esc_html($service->name); ?>
                </h3>
                <?php if ($service->status_check): ?>
                    <div class="homelab-service-status">
                        <span class="status-indicator <?php echo esc_attr($status_class); ?>"
                            style="background-color: <?php echo esc_attr($status_color); ?>;"></span>
                    </div>
                <?php endif; ?>
            </div>
            <?php if ($service->description): ?>
                <div class="homelab-service-body">
                    <p class="homelab-service-description">
                        <?php echo esc_html($service->description); ?>
                    </p>
                </div>
            <?php endif; ?>
            <div class="homelab-service-footer">
                <?php if ($service->service_url): ?>
                    <a href="<?php echo esc_url($service->service_url); ?>" target="_blank"
                        class="homelab-service-launch-button">Launch</a>
                <?php endif; ?>
                <?php if ($service->tags): ?>
                    <?php
                    $tags = explode(',', $service->tags);
                    foreach ($tags as $tag) {
                        $tag = trim($tag);
                        $tag_link = get_tag_link(get_term_by('name', $tag, 'post_tag')->term_id);
                        echo '<a href="' . esc_url($tag_link) . '" class="homelab-service-tags-button">' . esc_html($tag) . '</a> ';
                    }
                    ?>
                <?php endif; ?>
                <?php if ($service->category_id): ?>
                    <a href="<?php echo esc_url(get_category_link($service->category_id)); ?>"
                        class="homelab-service-category-button">Posts by Category</a>
                <?php endif; ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=homelab-view-service&service_id=' . $service->id)); ?>"
                    class="homelab-service-view-button">View Service</a>
            </div>
            <?php
            // Render child services recursively
            if (isset($grouped_services[$service->id])) {
                echo '<div class="homelab-child-services ' . $child_alignment . '">';
                homelab_render_services($grouped_services, $grouped_services[$service->id], $child_alignment);
                echo '</div>';
            }
            ?>
        </div>
        <?php
    }
}