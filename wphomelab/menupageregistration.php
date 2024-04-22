<?
// Add menu items
function homelab_plugin_menu() {
    add_menu_page(
        'Homelab',
        'Homelab',
        'manage_options',
        'homelab',
        'homelab_setup_page',
        'dashicons-admin-generic',
        30
    );

    //Setup
    add_submenu_page(
        'homelab',
        'Setup',
        'Setup',
        'manage_options',
        'homelab-setup',
        'homelab_setup_page'
    );

    //New Service
    add_submenu_page(
        'homelab',
        'New Service',
        'New Service',
        'manage_options',
        'homelab-create-service',
        'homelab_create_service_page'
    );
    
    //List Service
    add_submenu_page(
        'homelab',
        'List Services',
        'List Services',
        'manage_options',
        'homelab-list-services',
        'homelab_list_services_page'
    );


    //Status Check Settings ??
    add_submenu_page(
        'homelab',
        'Status Check Settings',
        'Status Check Settings',
        'manage_options',
        'homelab-status-check-settings',
        'homelab_status_check_settings_page'
    );

    
    //Edit Service
     add_submenu_page(
        'homelab', // Assuming this is a registered parent page
        'Edit Service', // Page title
        'Edit Service', // Menu title
        'edit_posts', // Capability
        'homelab-edit-service', // Menu slug
        'homelab_edit_service_page' // Function to display the page
    );
    
    //View Service
    add_submenu_page(
        'homelab', // Assuming this is a registered parent page
        'View Service', // Page title
        'View Service', // Menu title
        'edit_posts', // Capability
        'homelab-view-service', // Menu slug
        'homelab_view_service_page' // Function to display the page
    );

    add_submenu_page(
        'homelab', 
        'Visualise Services',
        'Visualise Services',
        'edit_posts',
        'homelab-services', 
        'homelab_services_page'
        
    ); 

    //Troubleshooting
    add_submenu_page(
        'homelab',
        'Troubleshooting',
        'Troubleshooting',
        'manage_options',
        'homelab-troubleshooting',
        'homelab_troubleshooting_page'
    );

    //Table Viewer
    add_submenu_page(
        'homelab-troubleshooting', // Assuming this is a registered parent page
        'Table Viewer',
        'Table Viewer',
        'manage_options',
        'homelab-table-viewer',
        'homelab_render_table_viewer_page'
    ); 

    add_submenu_page(
        'homelab-troubleshooting', 
        'Scheduled Fetchs',
        'Scheduled Fetchs',
        'manage_options',
        'homelab-scheduled-fetchs',
        'homelab_render_schedule_page'
    );

    add_submenu_page(
        'homelab',
        'WPHomelab Settings',
        'WPHomelab Settings',
        'manage_options',
        'homelab-settings',
        'homelab_render_settings_page'
    );

    
    
    //Test
    add_submenu_page(
        'homelab', // Assuming this is a registered parent page
        'View Test List', // Page title
        'View Test List', // Menu title
        'edit_posts', // Capability
        'homelab_test_list', // Menu slug
        'homelab_test_list_page' // Function to display the page
    ); 


    // Now remove the submenu page
    //remove_submenu_page('homelab', 'homelab-edit-service');
}
add_action('admin_menu', 'homelab_plugin_menu');