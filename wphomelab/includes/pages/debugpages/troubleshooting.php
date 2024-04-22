<?php
function homelab_troubleshooting_page() {
    ?>
    <div class="wrap">
        <h1>Troubleshooting</h1>
        <p>Welcome to the Homelab Troubleshooting page.</p>
        <p>Here you can find tools and information to help troubleshoot issues with your Homelab setup.</p>
        <ul>
            <li><a href="<?php echo admin_url('admin.php?page=homelab-table-viewer'); ?>">Table Viewer</a> - View and analyze the plugin's database tables.</li>
            <li><a href="<?php echo admin_url('admin.php?page=homelab-scheduled-fetchs'); ?>">Scheduled Fetchs</a> - Check the status and configuration of scheduled data fetches.</li>
        </ul>
    </div>
    <?php
}