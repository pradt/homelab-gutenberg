<?php
 function homelab_render_service_block($attributes) {
    global $wpdb;

    // Provide default values if attributes are not set
    $service_id = $attributes['serviceId'] ?? null;
    $show_status = $attributes['showStatus'] ?? true;
    $show_description = $attributes['showDescription'] ?? true;
    $show_icon = $attributes['showIcon'] ?? true;
    $show_image = $attributes['showImage'] ?? true;
    $show_launch_button = $attributes['showLaunchButton'] ?? true;
    $show_tags_button = $attributes['showTagsButton'] ?? true;
    $show_category_button = $attributes['showCategoryButton'] ?? true;
    $show_view_button = $attributes['showViewButton'] ?? true;
    
    // Early return if no service ID is provided
    if (null === $service_id) {
        return '';
    }

    // Retrieve the service data based on the service ID
    $table_name_services = $wpdb->prefix . 'homelab_services';
    $service = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name_services WHERE id = %d", $service_id));

    if (!$service) {
        return '';
    }

    
    $image_url = $service->image_id ? wp_get_attachment_url($service->image_id) : '';

    $status_class = '';
    $status_color = '';
    if ($service->status_check) {
        $status = homelab_get_latest_service_status($service->id);
        $status_class = strtolower($status);
        switch ($status) {
            case 'GREEN':
                $status_color = 'green';
                break;
            case 'AMBER':
                $status_color = 'orange';
                break;
            case 'RED':
                $status_color = 'red';
                break;
            default:
                $status_color = 'gray';
                break;
        }
    }

    $description = $show_description ? wp_trim_words($service->description, 20, '...') : '';

    // Start capturing the output
    ob_start();
    ?>
    <div class="homelab-service-block">
        <div class="homelab-service-header">
            <?php if ($show_icon && $service->icon) : ?>
                <div class="homelab-service-icon">
                    <i class="<?php echo esc_attr($service->icon); ?>"></i>
                </div>
            <?php endif; ?>
            <?php if ($show_image && $image_url) : ?>
                <div class="homelab-service-image">
                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($service->name); ?>">
                </div>
            <?php endif; ?>
            <h3 class="homelab-service-title"><?php echo esc_html($service->name); ?></h3>
            <?php if ($show_status && $service->status_check) : ?>
                <div class="homelab-service-status">
                    <span class="status-indicator <?php echo esc_attr($status_class); ?>" style="background-color: <?php echo esc_attr($status_color); ?>;"></span>
                </div>
            <?php endif; ?>
        </div>
        <?php if ($show_description) : ?>
            <div class="homelab-service-body">
                <p class="homelab-service-description"><?php echo esc_html($description); ?> <a href="#" class="read-more">Read More</a></p>
            </div>
        <?php endif; ?>
        <div class="homelab-service-footer">
            <?php if ($show_launch_button && $service->service_url) : ?>
                <a href="<?php echo esc_url($service->service_url); ?>" target="_blank" class="homelab-service-launch-button">Launch</a>
            <?php endif; ?>
            <?php if ($show_tags_button && $service->tags) : ?>
    <?php
    $tags = explode(',', $service->tags);
    foreach ($tags as $tag) {
        $tag = trim($tag);
        $tag_link = get_tag_link(get_term_by('name', $tag, 'post_tag')->term_id);
        echo '<a href="' . esc_url($tag_link) . '" class="homelab-service-tags-button">' . esc_html($tag) . '</a> ';
    }
    ?>
<?php endif; ?>
<?php if ($show_category_button && $service->category_id) : ?>
    <a href="<?php echo esc_url(get_category_link($service->category_id)); ?>" class="homelab-service-category-button">Posts by Category</a>
<?php endif; ?>
            <?php if ($show_view_button) : ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=homelab-view-service&service_id=' . $service->id)); ?>" class="homelab-service-view-button">View Service</a>
            <?php endif; ?>
        </div>
    </div>
    <style>
        .homelab-service-block {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 1px 1px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            padding: 15px;
        }
        .homelab-service-header {
            align-items: center;
            display: flex;
            margin-bottom: 10px;
        }
        .homelab-service-icon {
            font-size: 24px;
            margin-right: 10px;
        }
        .homelab-service-image {
            margin-right: 10px;
        }
        .homelab-service-image img {
            max-width: 100%;
            height: auto;
        }
        .homelab-service-title {
            margin: 0;
        }
        .homelab-service-status {
            margin-left: auto;
        }
        .status-indicator {
            border-radius: 50%;
            display: inline-block;
            height: 10px;
            width: 10px;
        }
        .homelab-service-body {
            margin-bottom: 10px;
        }
        .homelab-service-description {
            margin: 0;
        }
        .read-more {
            color: #007bff;
            text-decoration: none;
        }
        .read-more:hover {
            text-decoration: underline;
        }
        .homelab-service-footer {
            text-align: right;
        }
        .homelab-service-footer a {
            background-color: #007bff;
            border-color: #007bff;
            border-radius: 4px;
            color: #fff;
            display: inline-block;
            margin-left: 5px;
            padding: 6px 12px;
            text-align: center;
            text-decoration: none;
            vertical-align: middle;
        }
        .homelab-service-footer a:hover {
            background-color: #0069d9;
            border-color: #0062cc;
        }
    </style>
    <?php
    return ob_get_clean();
}