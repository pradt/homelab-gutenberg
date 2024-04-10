<?php
function homelab_test_list_page() {
    echo '<h2>List of Records</h2>';
    echo '<ul>';
    for ($id = 1; $id <= 10; $id++) {
        
        $url = add_query_arg(['page' => 'homelab_test_detail_page', 'id' => $id], admin_url('admin.php'));
        echo '<li><a href="' . esc_url($url) . '">This is record ' . $id . '</a></li>';
    }
    echo '</ul>';
}

function homelab_test_detail_page() {
    // Check if the user has the capability to publish posts
    if (!current_user_can('edit_posts')) {
        wp_die(__('Sorry, you are not allowed to access this page.'));
    }

    // Retrieve the ID from the query parameter and sanitize it
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    // Display the ID
    if ($id > 0) {
        echo '<h2>Detail Page</h2>';
        echo '<p>ID received: ' . $id . '</p>';
    } else {
        echo '<p>Invalid ID.</p>';
    }
}