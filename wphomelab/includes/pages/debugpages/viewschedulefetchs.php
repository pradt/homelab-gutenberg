<?php
// Render the admin page
function homelab_render_schedule_page() {
    // Get all the scheduled fetches
    $scheduled_fetches = homelab_get_scheduled_fetches();
    ?>
    <div class="wrap">
        <h1>Homelab Scheduled Fetches</h1>
        <?php if (!empty($scheduled_fetches)) : ?>
            <table class="wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th>Service ID</th>
                        <th>Fetch Interval</th>
                        <th>Next Fetch</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($scheduled_fetches as $fetch) : ?>
                        <tr>
                            <td><?php echo esc_html($fetch['service_id']); ?></td>
                            <td><?php echo esc_html($fetch['interval']); ?> seconds</td>
                            <td><?php echo esc_html($fetch['next_fetch']); ?></td>
                            <td>
                                <a href="<?php echo esc_url(wp_nonce_url(add_query_arg('action', 'stop', add_query_arg('service_id', $fetch['service_id'])), 'homelab_stop_fetch_' . $fetch['service_id'])); ?>" class="button">Stop</a>
                                <a href="<?php echo esc_url(wp_nonce_url(add_query_arg('action', 'start', add_query_arg('service_id', $fetch['service_id'])), 'homelab_start_fetch_' . $fetch['service_id'])); ?>" class="button">Start</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p>No scheduled fetches found.</p>
        <?php endif; ?>
    </div>
    <?php
}

// Get all the scheduled fetches
function homelab_get_scheduled_fetches() {
    $scheduled_fetches = array();
    $services = homelab_get_all_services();
    
    foreach ($services as $service) {
        $service_id = $service->id;
        $fetch_interval = $service->fetch_interval;
        
        if (!empty($fetch_interval) && $fetch_interval > 0) {
            $next_fetch = wp_next_scheduled('homelab_fetch_service_data', array($service_id));
            
            if ($next_fetch) {
                $scheduled_fetches[] = array(
                    'service_id' => $service_id,
                    'interval' => $fetch_interval,
                    'next_fetch' => date('Y-m-d H:i:s', $next_fetch),
                );
            }
        }
    }
    
    return $scheduled_fetches;
}