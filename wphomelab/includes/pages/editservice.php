<?php

function homelab_edit_service_page() {
    ob_start();
    global $wpdb;

    $table_name_services = $wpdb->prefix . 'homelab_services';
    $table_name_images = $wpdb->prefix . 'homelab_service_images';

    // Check if the service ID is provided
    if (!isset($_GET['service_id'])) {
        echo '<div class="wrap"><h1>Invalid Service</h1><p>No service ID provided.</p></div>';
        ob_end_flush();
        return;
    }

    $service_id = intval($_GET['service_id']);

    // Fetch the service from the database
    $service = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name_services WHERE id = %d", $service_id));

    if (!$service) {
        echo '<div class="wrap"><h1>Invalid Service</h1><p>Service not found.</p></div>';
        ob_end_flush();
        return;
    }

    // Handle form submission
    if (isset($_POST['submit'])) {
        homelab_update_service($service_id);
        wp_redirect(admin_url('admin.php?page=homelab-list-services'));
        exit;
    }

    // Render the edit service form
    ?>
    <div class="wrap">
        <h1>Edit Service</h1>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th><label for="name">Name</label></th>
                    <td><input type="text" name="name" id="name" class="regular-text" value="<?php echo esc_attr($service->name); ?>" required></td>
                </tr>
                <tr>
                    <th><label for="service_type">Service/Application</label></th>
                    <td>
                        <select name="service_type" id="service_type">
                            <option value="">Select a service</option>
                            <option value="adguard" <?php selected($service->service_type, 'adguard'); ?>>AdGuard</option>
                            <option value="audiobookshelf" <?php selected($service->service_type, 'audiobookshelf'); ?>>AudioBookshelf</option>
                            <option value="authentik" <?php selected($service->service_type, 'authentik'); ?>>Authentik</option>
                            <option value="autobrr" <?php selected($service->service_type, 'autobrr'); ?>>Autobrr</option>
                            <option value="calibre-web" <?php selected($service->service_type, 'calibre-web'); ?>>Calibre-web</option>
                            <option value="changedetection" <?php selected($service->service_type, 'changedetection'); ?>>Changedetection.io</option>
                            <option value="channels-dvr" <?php selected($service->service_type, 'channels-dvr'); ?>>Channels DVR Server</option>
                            <option value="cloudflare-tunnels" <?php selected($service->service_type, 'cloudflare-tunnels'); ?>>Cloudflare Tunnels</option>
                            <option value="cytube" <?php selected($service->service_type, 'cytube'); ?>>CyTube</option>
                            <option value="deluge" <?php selected($service->service_type, 'deluge'); ?>>Deluge</option>
                            <option value="emby" <?php selected($service->service_type, 'emby'); ?>>Emby</option>
                            <option value="esphome" <?php selected($service->service_type, 'esphome'); ?>>ESPHome</option>
                            <option value="evcc" <?php selected($service->service_type, 'evcc'); ?>>EVCC</option>
                            <option value="fileflows" <?php selected($service->service_type, 'fileflows'); ?>>FileFlows</option>
                            <option value="flood" <?php selected($service->service_type, 'flood'); ?>>Flood</option>
                            <option value="freshrss" <?php selected($service->service_type, 'freshrss'); ?>>FreshRSS</option>
                            <option value="fritzbox" <?php selected($service->service_type, 'fritzbox'); ?>>FRITZ!Box</option>
                            <option value="gamedig" <?php selected($service->service_type, 'gamedig'); ?>>GameDig</option>
                            <option value="gitea" <?php selected($service->service_type, 'gitea'); ?>>Gitea</option>
                            <option value="glances" <?php selected($service->service_type, 'glances'); ?>>Glances</option>
                            <option value="gluten" <?php selected($service->service_type, 'gluten'); ?>>Gluten</option>
                            <option value="gotify" <?php selected($service->service_type, 'gotify'); ?>>Gotify</option>
                            <option value="grafana" <?php selected($service->service_type, 'grafana'); ?>>Grafana</option>
                            <option value="healthchecks" <?php selected($service->service_type, 'healthchecks'); ?>>Health Checks</option>
                            <option value="homeassistant" <?php selected($service->service_type, 'homeassistant'); ?>>Home Assistant</option>
                            <option value="homebox" <?php selected($service->service_type, 'homebox'); ?>>Homebox</option>
                            <option value="immich" <?php selected($service->service_type, 'immich'); ?>>Immich</option>
                            <option value="jdownloader" <?php selected($service->service_type, 'jdownloader'); ?>>JDownloader</option>
                            <option value="jellyfin" <?php selected($service->service_type, 'jellyfin'); ?>>Jellyfin</option>
                            <option value="jellyseerr" <?php selected($service->service_type, 'jellyseerr'); ?>>Jellyseerr</option>
                            <option value="kavita" <?php selected($service->service_type, 'kavita'); ?>>Kavita</option>
                            <option value="komga" <?php selected($service->service_type, 'komga'); ?>>Komga</option>
                            <option value="kopia" <?php selected($service->service_type, 'kopia'); ?>>Kopia</option>
                            <option value="lidarr" <?php selected($service->service_type, 'lidarr'); ?>>Lidarr</option>
                            <option value="mastodon" <?php selected($service->service_type, 'mastodon'); ?>>Mastodon</option>
                            <option value="mealie" <?php selected($service->service_type, 'mealie'); ?>>Mealie</option>
                            <option value="medusa" <?php selected($service->service_type, 'medusa'); ?>>Medusa</option>
                            <option value="mikrotik" <?php selected($service->service_type, 'mikrotik'); ?>>Mikrotik</option>
                            <option value="minecraft" <?php selected($service->service_type, 'minecraft'); ?>>Minecraft Server</option>
                            <option value="miniflux" <?php selected($service->service_type, 'miniflux'); ?>>Miniflux 2</option>
                            <option value="moonraker" <?php selected($service->service_type, 'moonraker'); ?>>Moonraker</option>
                            <option value="mongodb" <?php selected($service->service_type, 'mongodb'); ?>>mongoDb</option>
                            <option value="mysql" <?php selected($service->service_type, 'mysql'); ?>>mySQL</option>
                            <option value="mylar3" <?php selected($service->service_type, 'mylar3'); ?>>Mylar3</option>
                            <option value="navidrome" <?php selected($service->service_type, 'navidrome'); ?>>Navidrome</option>
                            <option value="netdata" <?php selected($service->service_type, 'netdata'); ?>>Netdata</option>
                            <option value="nextcloud" <?php selected($service->service_type, 'nextcloud'); ?>>Nextcloud</option>
                            <option value="nextdns" <?php selected($service->service_type, 'nextdns'); ?>>NextDNS</option>
                            <option value="nginx-proxy-manager" <?php selected($service->service_type, 'nginx-proxy-manager'); ?>>Nginx Proxy Manager</option>
                            <option value="nzbget" <?php selected($service->service_type, 'nzbget'); ?>>NZBGet</option>
                            <option value="octoprint" <?php selected($service->service_type, 'octoprint'); ?>>OctoPrint</option>
                            <option value="omada" <?php selected($service->service_type, 'omada'); ?>>Omada</option>
                            <option value="ombi" <?php selected($service->service_type, 'ombi'); ?>>Ombi</option>
                            <option value="omni" <?php selected($service->service_type, 'omni'); ?>>Omni</option>
                            <option value="opendtu" <?php selected($service->service_type, 'opendtu'); ?>>OpenDTU</option>
                            <option value="openmediavault" <?php selected($service->service_type, 'openmediavault'); ?>>OpenMediaVault</option>
                            <option value="opnsense" <?php selected($service->service_type, 'opnsense'); ?>>OPNSense</option>
                            <option value="overseerr" <?php selected($service->service_type, 'overseerr'); ?>>Overseerr</option>
                            <option value="paperless" <?php selected($service->service_type, 'paperless'); ?>>Paperless NGX</option>
                            <option value="peanut" <?php selected($service->service_type, 'peanut'); ?>>PeaNUT</option>
                            <option value="pfsense" <?php selected($service->service_type, 'pfsense'); ?>>pfSense</option>
                            <option value="photoprism" <?php selected($service->service_type, 'photoprism'); ?>>PhotoPrism</option>
                            <option value="pialert" <?php selected($service->service_type, 'pialert'); ?>>PiAlert</option>
                            <option value="pihole" <?php selected($service->service_type, 'pihole'); ?>>Pi-hole</option>
                            <option value="plantit" <?php selected($service->service_type, 'plantit'); ?>>Plant-It</option>
                            <option value="plex" <?php selected($service->service_type, 'plex'); ?>>Plex</option>
                            <option value="portainer" <?php selected($service->service_type, 'portainer'); ?>>Portainer</option>
                            <option value="postgres" <?php selected($service->service_type, 'postgres'); ?>>Postgres</option>
                            <option value="prometheus" <?php selected($service->service_type, 'prometheus'); ?>>Prometheus</option>
                            <option value="prowlarr" <?php selected($service->service_type, 'prowlarr'); ?>>Prowlarr</option>
                            <option value="proxmox" <?php selected($service->service_type, 'proxmox'); ?>>Proxmox</option>
                            <option value="proxmox-backup" <?php selected($service->service_type, 'proxmox-backup'); ?>>Proxmox Backup</option>
                            <option value="pterodactyl" <?php selected($service->service_type, 'pterodactyl'); ?>>Pterodactyl</option>
                            <option value="pyload" <?php selected($service->service_type, 'pyload'); ?>>pyLoad</option>
                            <option value="qbittorrent" <?php selected($service->service_type, 'qbittorrent'); ?>>qBittorrent</option>
                            <option value="qnap" <?php selected($service->service_type, 'qnap'); ?>>QNAP</option>
                            <option value="radarr" <?php selected($service->service_type, 'radarr'); ?>>Radarr</option>
                            <option value="readarr" <?php selected($service->service_type, 'readarr'); ?>>Readarr</option>
                            <option value="redis" <?php selected($service->service_type, 'redis'); ?>>Redis</option>
                            <option value="romm" <?php selected($service->service_type, 'romm'); ?>>ROMM</option>
                            <option value="rutorrent" <?php selected($service->service_type, 'rutorrent'); ?>>ruTorrent</option>
                            <option value="sabnzbd" <?php selected($service->service_type, 'sabnzbd'); ?>>SABnzbd</option>
                            <option value="scrutiny" <?php selected($service->service_type, 'scrutiny'); ?>>Scrutiny</option>
                            <option value="sonarr" <?php selected($service->service_type, 'sonarr'); ?>>Sonarr</option>
                            <option value="speedtest-tracker" <?php selected($service->service_type, 'speedtest-tracker'); ?>>Speedtest Tracker</option>
                            <option value="stash" <?php selected($service->service_type, 'stash'); ?>>Stash</option>
                            <option value="syncthing-relay" <?php selected($service->service_type, 'syncthing-relay'); ?>>Syncthing Relay Server</option>
                            <option value="synology" <?php selected($service->service_type, 'synology'); ?>>Synology Disk Station</option>
                            <option value="synology-download-station" <?php selected($service->service_type, 'synology-download-station'); ?>>Synology Download Station</option>
                            <option value="tailscale" <?php selected($service->service_type, 'tailscale'); ?>>Tailscale</option>
                            <option value="tautulli" <?php selected($service->service_type, 'tautulli'); ?>>Tautulli</option>
                            <option value="tdarr" <?php selected($service->service_type, 'tdarr'); ?>>Tdarr</option>
                            <option value="traefik" <?php selected($service->service_type, 'traefik'); ?>>Traefik</option>
                            <option value="transmission" <?php selected($service->service_type, 'transmission'); ?>>Transmission</option>
                            <option value="truenas" <?php selected($service->service_type, 'truenas'); ?>>TrueNAS</option>
                            <option value="tube-archivist" <?php selected($service->service_type, 'tube-archivist'); ?>>Tube Archivist</option>
                            <option value="unifi" <?php selected($service->service_type, 'unifi'); ?>>Unifi Controller</option>
                            <option value="unmanic" <?php selected($service->service_type, 'unmanic'); ?>>Unmanic</option>
                            <option value="uptimekuma" <?php selected($service->service_type, 'uptimekuma'); ?>>Uptime Kuma</option>
                            <option value="uptimerobot" <?php selected($service->service_type, 'uptimerobot'); ?>>UptimeRobot</option>
                            <option value="watchtower" <?php selected($service->service_type, 'watchtower'); ?>>Watchtower</option>
                            <option value="whatsup-docker" <?php selected($service->service_type, 'whatsup-docker'); ?>>What's Up Docker</option>
                            <option value="other" <?php selected($service->service_type, 'other'); ?>>Other</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="description">Description</label></th>
                    <td><textarea name="description" id="description" rows="4" cols="50"><?php echo esc_textarea($service->description); ?></textarea></td>
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
                            global $wpdb;
                            $table_name_images = $wpdb->prefix . 'homelab_service_images';
                            $images = $wpdb->get_results("SELECT * FROM $table_name_images");
                            foreach ($images as $image) {
                                $selected = ($image->id == $service->image_id) ? 'selected' : '';
                                echo '<option value="' . $image->id . '" ' . $selected . '>' . $image->name . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="tags">Tags</label></th>
                    <td><input type="text" name="tags" id="tags" class="regular-text" value="<?php echo esc_attr($service->tags); ?>"></td>
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
                    <td><input type="color" name="color" id="color" value="<?php echo esc_attr($service->color); ?>"></td>
                </tr>
                <tr>
                    <th><label for="parent">Parent</label></th>
                    <td>
                        <select name="parent" id="parent">
                            <option value="">Select a parent service</option>
                            <?php
                            global $wpdb;
                            $table_name_services = $wpdb->prefix . 'homelab_services';
                            $services = $wpdb->get_results("SELECT * FROM $table_name_services");
                            foreach ($services as $s) {
                                if ($s->id != $service->id) {
                                    $selected = ($s->id == $service->parent_id) ? 'selected' : '';
                                    echo '<option value="' . $s->id . '" ' . $selected . '>' . $s->name . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="service_url">Service URL</label></th>
                    <td><input type="text" name="service_url" id="service_url" class="regular-text" value="<?php echo esc_attr($service->service_url); ?>"></td>
                </tr>
                <tr>
                    <th><label for="alt_page_url">Alternative Page URL</label></th>
                    <td><input type="text" name="alt_page_url" id="alt_page_url" class="regular-text" value="<?php echo esc_attr($service->alt_page_url); ?>"></td>
                </tr>
                <tr>
                    <th><label for="status_check">Status Check</label></th>
                    <td><input type="checkbox" name="status_check" id="status_check" value="1" <?php checked($service->status_check, 1); ?>></td>
                </tr>
                <tr class="status-check-fields" style="display: <?php echo $service->status_check ? 'table-row' : 'none'; ?>;">
                    <th><label for="status_check_url">Status Check URL</label></th>
                    <td><input type="text" name="status_check_url" id="status_check_url" class="regular-text" value="<?php echo esc_attr($service->status_check_url); ?>"></td>
                </tr>
                <tr class="status-check-fields" style="display: <?php echo $service->status_check ? 'table-row' : 'none'; ?>;">
                    <th><label for="disable_ssl">Disable SSL</label></th>
                    <td><input type="checkbox" name="disable_ssl" id="disable_ssl" value="1" <?php checked($service->disable_ssl, 1); ?>></td>
                </tr>
                <tr class="status-check-fields" style="display: <?php echo $service->status_check ? 'table-row' : 'none'; ?>;">
                    <th><label for="accepted_response">Accepted HTTP Response</label></th>
                    <td><input type="text" name="accepted_response" id="accepted_response" class="regular-text" value="<?php echo esc_attr($service->accepted_response); ?>"></td>
                </tr>
                <tr class="status-check-fields" style="display: <?php echo $service->status_check ? 'table-row' : 'none'; ?>;">
                    <th><label for="polling_interval">Polling Interval (seconds)</label></th>
                    <td><input type="number" name="polling_interval" id="polling_interval" class="regular-text" min="5" value="<?php echo esc_attr($service->polling_interval); ?>"></td>
                </tr>
                <tr class="status-check-fields" style="display: <?php echo $service->status_check ? 'table-row' : 'none'; ?>;">
                    <th><label for="notify_email">Notify Email</label></th>
                    <td><input type="email" name="notify_email" id="notify_email" class="regular-text" value="<?php echo esc_attr($service->notify_email); ?>"></td>
                </tr>
                <tr class="get-data-fields" style="display: none;">
                    <th><label for="get_data">Get Data</label></th>
                    <td><input type="checkbox" name="get_data" id="get_data" value="1" <?php checked($service->get_data, 1); ?>></td>
                </tr>
                <tr class="api-fields" style="display: <?php echo $service->get_data ? 'table-row' : 'none'; ?>;">
                    <th><label for="api_url">API URL</label></th>
                    <td><input type="text" name="api_url" id="api_url" class="regular-text" value="<?php echo esc_attr($service->api_url); ?>"></td>
                </tr>
                <tr class="api-fields" style="display: <?php echo $service->get_data ? 'table-row' : 'none'; ?>;">
                    <th><label for="api_key">API Key</label></th>
                    <td><input type="text" name="api_key" id="api_key" class="regular-text" value="<?php echo esc_attr($service->api_key); ?>"></td>
                </tr>
                <tr class="api-fields" style="display: <?php echo $service->get_data ? 'table-row' : 'none'; ?>;">
                    <th><label for="username">Username</label></th>
                    <td><input type="text" name="username" id="username" class="regular-text" value="<?php echo esc_attr($service->username); ?>"></td>
                </tr>
                <tr class="api-fields" style="display: <?php echo $service->get_data ? 'table-row' : 'none'; ?>;">
                    <th><label for="password">Password</label></th>
                    <td><input type="password" name="password" id="password" class="regular-text" value="<?php echo esc_attr($service->password); ?>"></td>
                </tr>
                <tr class="api-fields" style="display: <?php echo $service->get_data ? 'table-row' : 'none'; ?>;">
                    <th><label for="tunnel_id">Tunnel ID</label></th>
                    <td><input type="text" name="tunnel_id" id="tunnel_id" class="regular-text" value="<?php echo esc_attr($service->tunnel_id); ?>"></td>
                </tr>
                <tr class="api-fields" style="display: <?php echo $service->get_data ? 'table-row' : 'none'; ?>;">
                    <th><label for="account_id">Account ID</label></th>
                    <td><input type="text" name="account_id" id="account_id" class="regular-text" value="<?php echo esc_attr($service->account_id); ?>"></td>
                </tr>
                <tr class="api-fields" style="display: <?php echo $service->get_data ? 'table-row' : 'none'; ?>;">
                    <th><label for="enable_now_playing">Enable Now Playing</label></th>
                    <td><input type="checkbox" name="enable_now_playing" id="enable_now_playing" value="1" <?php checked($service->enable_now_playing, 1); ?>></td>
                </tr>
                <tr class="api-fields" style="display: <?php echo $service->get_data ? 'table-row' : 'none'; ?>;">
                    <th><label for="enable_blocks">Enable Blocks</label></th>
                    <td><input type="checkbox" name="enable_blocks" id="enable_blocks" value="1" <?php checked($service->enable_blocks, 1); ?>></td>
                </tr>
                <tr class="api-fields" style="display: <?php echo $service->get_data ? 'table-row' : 'none'; ?>;">
                    <th><label for="check_uuid">Check UUID</label></th>
                    <td><input type="text" name="check_uuid" id="check_uuid" class="regular-text" value="<?php echo esc_attr($service->check_uuid); ?>"></td>
                </tr>
                <tr class="api-fields" style="display: <?php echo $service->get_data ? 'table-row' : 'none'; ?>;">
                    <th><label for="client_id">Client ID</label></th>
                    <td><input type="text" name="client_id" id="client_id" class="regular-text" value="<?php echo esc_attr($service->client_id); ?>"></td>
                </tr>
                <tr class="api-fields" style="display: <?php echo $service->get_data ? 'table-row' : 'none'; ?>;">
                    <th><label for="toots_limit">Toots Limit</label></th>
                    <td><input type="number" name="toots_limit" id="toots_limit" class="regular-text" min="1" value="<?php echo esc_attr($service->toots_limit); ?>"></td>
                </tr>
                <tr class="api-fields" style="display: <?php echo $service->get_data ? 'table-row' : 'none'; ?>;">
                    <th><label for="server_port">Server Port</label></th>
                    <td><input type="number" name="server_port" id="server_port" class="regular-text" min="1" max="65535" value="<?php echo esc_attr($service->server_port); ?>"></td>
                </tr>
                <tr class="api-fields" style="display: <?php echo $service->get_data ? 'table-row' : 'none'; ?>;">
                    <th><label for="volume">Volume</label></th>
                    <td><input type="text" name="volume" id="volume" class="regular-text" value="<?php echo esc_attr($service->volume); ?>"></td>
                </tr>
                <tr class="api-fields" style="display: <?php echo $service->get_data ? 'table-row' : 'none'; ?>;">
                    <th><label for="fetch_interval">Fetch Every (seconds)</label></th>
                    <td><input type="number" name="fetch_interval" id="fetch_interval" class="regular-text" min="5" value="<?php echo esc_attr($service->fetch_interval); ?>"></td>
                </tr>
                <tr class="api-fields" style="display: <?php echo $service->get_data ? 'table-row' : 'none'; ?>;">
                    <th>&nbsp;</th>
                    <td>
                        <button type="button" class="button" id="test-connection">Test Connection</button>
                        <span class="spinner" id="test-connection-spinner" style="float: none; visibility: hidden;"></span>
                        <div id="test-connection-result" style="margin-top: 5px;"></div>
                    </td>
                </tr>
            </table>
            <?php submit_button('Update Service'); ?>
        </form>
    </div>
    <script>
    jQuery(document).ready(function($) {
        $('#status_check').change(function() {
            $('.status-check-fields').toggle(this.checked);
        });

        $('#get_data').change(function() {
            $('.api-fields').toggle(this.checked);
        });

        $('#service_type').change(function() {
            var selectedService = $(this).val();
            var fieldsToShow = getFieldsToShow();
            var mandatoryFields = getMandatoryFields();

            $('.api-fields').hide();
            if ($('#get_data').is(':checked')) {
                $.each(fieldsToShow[selectedService], function(index, field) {
                    $('#' + field).closest('tr').show();
                });

                $.each(mandatoryFields[selectedService], function(index, field) {
                    $('#' + field).prop('required', true);
                });
            }
        });

        // Trigger change events on page load to show/hide fields based on the saved service data
        $('#status_check').change();
        $('#get_data').change();
        $('#service_type').change();
    });

    /**
 * Get the fields to show based on the selected service and the "Get Data" checkbox state.
 *
 * @return array An associative array mapping service names to their respective fields to show.
 *               Each service is represented as a key-value pair, where the key is the service name
 *               and the value is an array of field names to display when that service is selected
 *               and the "Get Data" checkbox is checked.
 */

 function getFieldsToShow() {
    return [
        'adguard' => ['api_url', 'username', 'password', 'fetch_interval'],
        'audiobookshelf' => ['api_url', 'api_key', 'fetch_interval'],
        'authentik' => ['api_url', 'api_key', 'fetch_interval'],
        'autobrr' => ['api_url', 'api_key', 'fetch_interval'],
        'calibre-web' => ['api_url', 'username', 'password', 'fetch_interval'],
        'changedetection' => ['api_url', 'api_key', 'fetch_interval'],
        'channels-dvr' => ['api_url', 'fetch_interval'],
        'cloudflare-tunnels' => ['api_url', 'api_key', 'tunnel_id', 'account_id', 'fetch_interval'],
        'cytube' => ['api_url', 'api_key', 'enable_now_playing', 'enable_blocks', 'fetch_interval'],
        'deluge' => ['api_url', 'api_key', 'fetch_interval'],
        'emby' => ['api_url', 'api_key', 'enable_now_playing', 'enable_blocks', 'fetch_interval'],
        'esphome' => ['api_url', 'fetch_interval'],
        'evcc' => ['api_url', 'fetch_interval'],
        'fileflows' => ['api_url', 'fetch_interval'],
        'flood' => ['api_url', 'username', 'password', 'fetch_interval'],
        'freshrss' => ['api_url', 'username', 'password', 'fetch_interval'],
        'fritzbox' => ['api_url', 'username', 'password', 'fetch_interval'],
        'gamedig' => ['api_url', 'fetch_interval'],
        'gitea' => ['api_url', 'api_key', 'fetch_interval'],
        'glances' => ['api_url', 'fetch_interval'],
        'gluten' => ['api_url', 'api_key', 'fetch_interval'],
        'gotify' => ['api_url', 'api_key', 'fetch_interval'],
        'grafana' => ['api_url', 'username', 'password', 'fetch_interval'],
        'healthchecks' => ['api_url', 'api_key', 'check_uuid', 'fetch_interval'],
        'homeassistant' => ['api_url', 'api_key', 'fetch_interval'],
        'homebox' => ['api_url', 'username', 'password', 'fetch_interval'],
        'immich' => ['api_url', 'api_key', 'fetch_interval'],
        'jdownloader' => ['api_url', 'username', 'password', 'client_id', 'fetch_interval'],
        'jellyfin' => ['api_url', 'api_key', 'fetch_interval'],
        'jellyseerr' => ['api_url', 'api_key', 'fetch_interval'],
        'kavita' => ['api_url', 'username', 'password', 'fetch_interval'],
        'komga' => ['api_url', 'username', 'password', 'fetch_interval'],
        'kopia' => ['api_url', 'username', 'password', 'fetch_interval'],
        'mastodon' => ['api_url', 'api_key', 'toots_limit', 'fetch_interval'],
        'mealie' => ['api_url', 'api_key', 'fetch_interval'],
        'medusa' => ['api_url', 'api_key', 'fetch_interval'],
        'mikrotik' => ['api_url', 'username', 'password', 'fetch_interval'],
        'minecraft' => ['api_url', 'server_port', 'fetch_interval'],
        'miniflux' => ['api_url', 'api_key', 'fetch_interval'],
        'moonraker' => ['api_url', 'api_key', 'fetch_interval'],
        'mylar3' => ['api_url', 'api_key', 'fetch_interval'],
        'navidrome' => ['api_url', 'api_key', 'username', 'salt', 'fetch_interval'],
        'netdata' => ['api_url', 'api_key', 'fetch_interval'],
        'nextcloud' => ['api_url', 'api_key', 'username', 'password', 'fetch_interval'],
        'nextdns' => ['api_url', 'api_key', 'fetch_interval'],
        'nginx-proxy-manager' => ['api_url', 'username', 'password', 'fetch_interval'],
        'nzbget' => ['api_url', 'username', 'password', 'fetch_interval'],
        'octoprint' => ['api_url', 'api_key', 'fetch_interval'],
        'omada' => ['api_url', 'username', 'password', 'site_name'],
        'ombi' => ['api_url', 'api_key', 'fetch_interval'],
        'omni' => ['api_url', 'api_key', 'fetch_interval'],
        'opendtu' => ['api_url', 'api_key', 'fetch_interval'],
        'openmediavault' => ['api_url', 'username', 'password', 'fetch_interval'],
        'opnsense' => ['api_url', 'username', 'password', 'fetch_interval'],
        'overseerr' => ['api_url', 'api_key', 'fetch_interval'],
        'paperless' => ['api_url', 'api_key', 'username', 'password', 'fetch_interval'],
        'peanut' => ['api_url', 'api_key', 'fetch_interval'],
        'pfsense' => ['api_url', 'username', 'password', 'api_key', 'fetch_interval'],
        'photoprism' => ['api_url', 'username', 'password', 'fetch_interval'],
        'pialert' => ['api_url', 'api_key', 'fetch_interval'],
        'pihole' => ['api_url', 'api_key', 'fetch_interval'],
        'plantit' => ['api_url', 'api_key', 'fetch_interval'],
        'plex' => ['api_url', 'api_key', 'fetch_interval'],
        'portainer' => ['api_url', 'api_key', 'fetch_interval'],
        'prometheus' => ['api_url', 'api_key', 'fetch_interval'],
        'proxmox' => ['api_url', 'username', 'password', 'fetch_interval'],
        'proxmox-backup' => ['api_url', 'username', 'password', 'fetch_interval'],
        'pterodactyl' => ['api_url', 'api_key', 'fetch_interval'],
        'pyload' => ['api_url', 'username', 'password', 'fetch_interval'],
        'qbittorrent' => ['api_url', 'username', 'password', 'fetch_interval'],
        'qnap' => ['api_url', 'username', 'password', 'fetch_interval'],
        'romm' => ['api_url', 'username', 'password', 'fetch_interval'],
        'rutorrent' => ['api_url', 'username', 'password', 'fetch_interval'],
        'sabnzbd' => ['api_url', 'api_key', 'fetch_interval'],
        'scrutiny' => ['api_url', 'api_key', 'fetch_interval'],
        'speedtest-tracker' => ['api_url', 'api_key', 'fetch_interval'],
        'stash' => ['api_url', 'api_key', 'fetch_interval'],
        'syncthing-relay' => ['api_url', 'api_key', 'fetch_interval'],
        'synology' => ['api_url', 'username', 'password', 'volume', 'fetch_interval'],
        'synology-download-station' => ['api_url', 'username', 'password', 'fetch_interval'],
        'tailscale' => ['api_url', 'api_key', 'fetch_interval'],
        'tautulli' => ['api_url', 'api_key', 'fetch_interval'],
        'traefik' => ['api_url', 'username', 'password', 'fetch_interval'],
        'transmission' => ['api_url', 'username', 'password', 'fetch_interval'],
        'truenas' => ['api_url', 'api_key', 'username', 'password', 'fetch_interval'],
        'tube-archivist' => ['api_url', 'api_key', 'fetch_interval'],
        'unifi' => ['api_url', 'username', 'password', 'fetch_interval'],
        'unmanic' => ['api_url', 'api_key', 'fetch_interval'],
        'uptimekuma' => ['api_url', 'slug', 'fetch_interval'],
        'uptimerobot' => ['api_url', 'api_key', 'fetch_interval'],
        'watchtower' => ['api_url', 'api_key', 'fetch_interval'],
        'whatsup-docker' => ['api_url', 'username', 'password', 'fetch_interval'],
        'mysql' => ['api_url', 'username', 'password', 'fetch_interval'],
        'postgres' => ['api_url', 'username', 'password', 'fetch_interval'],
        'mongodb' => ['api_url', 'username', 'password', 'fetch_interval'],
        'redis' => ['api_url', 'username', 'password', 'fetch_interval'],
    ];
}
/**
 * Get the mandatory fields for each service when the "Get Data" checkbox is checked.
 *
 * @return array An associative array mapping service names to their respective mandatory fields.
 *               Each service is represented as a key-value pair, where the key is the service name
 *               and the value is an array of mandatory field names when that service is selected
 *               and the "Get Data" checkbox is checked.
 */
function getMandatoryFields() {
    return [
        'adguard' => ['api_url', 'username', 'password', 'fetch_interval'],
        'audiobookshelf' => ['api_url', 'api_key', 'fetch_interval'],
        'authentik' => ['api_url', 'api_key', 'fetch_interval'],
        'autobrr' => ['api_url', 'api_key', 'fetch_interval'],
        'calibre-web' => ['api_url', 'username', 'password', 'fetch_interval'],
        'changedetection' => ['api_url', 'api_key', 'fetch_interval'],
        'channels-dvr' => ['api_url', 'fetch_interval'],
        'cloudflare-tunnels' => ['api_url', 'api_key', 'tunnel_id', 'account_id', 'fetch_interval'],
        'cytube' => ['api_url', 'api_key', 'enable_now_playing', 'enable_blocks', 'fetch_interval'],
        'deluge' => ['api_url', 'api_key', 'fetch_interval'],
        'emby' => ['api_url', 'api_key', 'enable_now_playing', 'enable_blocks', 'fetch_interval'],
        'esphome' => ['api_url', 'fetch_interval'],
        'evcc' => ['api_url', 'fetch_interval'],
        'fileflows' => ['api_url', 'fetch_interval'],
        'flood' => ['api_url', 'fetch_interval'],
        'freshrss' => ['api_url', 'username', 'password', 'fetch_interval'],
        'fritzbox' => ['api_url', 'fetch_interval'],
        'gamedig' => ['api_url', 'fetch_interval'],
        'gitea' => ['api_url', 'api_key', 'fetch_interval'],
        'glances' => ['api_url', 'fetch_interval'],
        'gluten' => ['api_url', 'fetch_interval'],
        'gotify' => ['api_url', 'api_key', 'fetch_interval'],
        'grafana' => ['api_url', 'username', 'password', 'fetch_interval'],
        'healthchecks' => ['api_url', 'api_key', 'fetch_interval'],
        'homeassistant' => ['api_url', 'api_key', 'fetch_interval'],
        'homebox' => ['api_url', 'username', 'password', 'fetch_interval'],
        'immich' => ['api_url', 'api_key', 'fetch_interval'],
        'jdownloader' => ['api_url', 'username', 'password', 'fetch_interval'],
        'jellyfin' => ['api_url', 'api_key', 'fetch_interval'],
        'jellyseerr' => ['api_url', 'api_key', 'fetch_interval'],
        'kavita' => ['api_url', 'username', 'password', 'fetch_interval'],
        'komga' => ['api_url', 'username', 'password', 'fetch_interval'],
        'kopia' => ['api_url', 'username', 'password', 'fetch_interval'],
        'mastodon' => ['api_url', 'api_key', 'fetch_interval'],
        'mealie' => ['api_url', 'api_key', 'fetch_interval'],
        'medusa' => ['api_url', 'api_key', 'fetch_interval'],
        'mikrotik' => ['api_url', 'username', 'password', 'fetch_interval'],
        'minecraft' => ['api_url', 'server_port', 'fetch_interval'],
        'miniflux' => ['api_url', 'api_key', 'fetch_interval'],
        'moonraker' => ['api_url', 'fetch_interval'],
        'mylar3' => ['api_url', 'api_key', 'fetch_interval'],
        'navidrome' => ['api_url', 'api_key', 'username', 'salt', 'fetch_interval'],
        'netdata' => ['api_url', 'fetch_interval'],
        'nextcloud' => ['api_url', 'fetch_interval'],
        'nextdns' => ['api_url', 'api_key', 'fetch_interval'],
        'nginx-proxy-manager' => ['api_url', 'username', 'password', 'fetch_interval'],
        'nzbget' => ['api_url', 'username', 'password', 'fetch_interval'],
        'octoprint' => ['api_url', 'api_key', 'fetch_interval'],
        'omada' => ['api_url', 'username', 'password', 'fetch_interval'],
        'ombi' => ['api_url', 'api_key', 'fetch_interval'],
        'omni' => ['api_url', 'api_key', 'fetch_interval'],
        'opendtu' => ['api_url', 'fetch_interval'],
        'openmediavault' => ['api_url', 'username', 'password', 'fetch_interval'],
        'opnsense' => ['api_url', 'username', 'password', 'fetch_interval'],
        'overseerr' => ['api_url', 'api_key', 'fetch_interval'],
        'paperless' => ['api_url', 'fetch_interval'],
        'peanut' => ['api_url', 'api_key', 'fetch_interval'],
        'pfsense' => ['api_url', 'fetch_interval'],
        'photoprism' => ['api_url', 'username', 'password', 'fetch_interval'],
        'pialert' => ['api_url', 'fetch_interval'],
        'pihole' => ['api_url', 'api_key', 'fetch_interval'],
        'plantit' => ['api_url', 'api_key', fetch_interval'],
'plex' => ['api_url', 'api_key', 'fetch_interval'],
'portainer' => ['api_url', 'api_key', 'fetch_interval'],
'prometheus' => ['api_url', 'fetch_interval'],
'proxmox' => ['api_url', 'username', 'password', 'fetch_interval'],
'proxmox-backup' => ['api_url', 'username', 'password', 'fetch_interval'],
'pterodactyl' => ['api_url', 'api_key', 'fetch_interval'],
'pyload' => ['api_url', 'username', 'password', 'fetch_interval'],
'qbittorrent' => ['api_url', 'username', 'password', 'fetch_interval'],
'qnap' => ['api_url', 'username', 'password', 'fetch_interval'],
'romm' => ['api_url', 'fetch_interval'],
'rutorrent' => ['api_url', 'fetch_interval'],
'sabnzbd' => ['api_url', 'api_key', 'fetch_interval'],
'scrutiny' => ['api_url', 'fetch_interval'],
'speedtest-tracker' => ['api_url', 'fetch_interval'],
'stash' => ['api_url', 'api_key', 'fetch_interval'],
'syncthing-relay' => ['api_url', 'fetch_interval'],
'synology' => ['api_url', 'username', 'password', 'volume', 'fetch_interval'],
'synology-download-station' => ['api_url', 'username', 'password', 'fetch_interval'],
'tailscale' => ['api_url', 'api_key', 'fetch_interval'],
'tautulli' => ['api_url', 'api_key', 'fetch_interval'],
'traefik' => ['api_url', 'fetch_interval'],
'transmission' => ['api_url', 'username', 'password', 'fetch_interval'],
'truenas' => ['api_url', 'api_key', 'username', 'password', 'fetch_interval'],
'tube-archivist' => ['api_url', 'api_key', 'fetch_interval'],
'unifi' => ['api_url', 'username', 'password', 'fetch_interval'],
'unmanic' => ['api_url', 'fetch_interval'],
'uptimekuma' => ['api_url', 'slug', 'fetch_interval'],
'uptimerobot' => ['api_url', 'api_key', 'fetch_interval'],
'watchtower' => ['api_url', 'api_key', 'fetch_interval'],
'whatsup-docker' => ['api_url', 'fetch_interval'],
'mysql' => ['api_url', 'username', 'password', 'fetch_interval'],
'postgres' => ['api_url', 'username', 'password', 'fetch_interval'],
'mongodb' => ['api_url', 'fetch_interval'],
'redis' => ['api_url', 'fetch_interval'],
];
}
    </script>
    <?php
    ob_end_flush();
}

function homelab_update_service($service_id) {
    global $wpdb;
    $table_name_services = $wpdb->prefix . 'homelab_services';

    // Retrieve the form data
    $name = sanitize_text_field($_POST['name']);
    $service_type = sanitize_text_field($_POST['service_type']);
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
    $get_data = isset($_POST['get_data']) ? 1 : 0;
    $api_url = sanitize_text_field($_POST['api_url']);
    $api_key = sanitize_text_field($_POST['api_key']);
    $username = sanitize_text_field($_POST['username']);
    $password = sanitize_text_field($_POST['password']);
    $tunnel_id = sanitize_text_field($_POST['tunnel_id']);
    $account_id = sanitize_text_field($_POST['account_id']);
    $enable_now_playing = isset($_POST['enable_now_playing']) ? 1 : 0;
    $enable_blocks = isset($_POST['enable_blocks']) ? 1 : 0;
    $check_uuid = sanitize_text_field($_POST['check_uuid']);
    $client_id = sanitize_text_field($_POST['client_id']);
    $toots_limit = intval($_POST['toots_limit']);
    $server_port = intval($_POST['server_port']);
    $volume = sanitize_text_field($_POST['volume']);
    $fetch_interval = intval($_POST['fetch_interval']);

    // Update the service in the database
    $wpdb->update(
        $table_name_services,
        array(
            'name' => $name,
            'service_type' => $service_type,
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
            'get_data' => $get_data,
            'api_url' => $api_url,
            'api_key' => $api_key,
            'username' => $username,
            'password' => $password,
            'tunnel_id' => $tunnel_id,
            'account_id' => $account_id,
            'enable_now_playing' => $enable_now_playing,
            'enable_blocks' => $enable_blocks,
            'check_uuid' => $check_uuid,
            'client_id' => $client_id,
            'toots_limit' => $toots_limit,
            'server_port' => $server_port,
            'volume' => $volume,
            'fetch_interval' => $fetch_interval,
        ),
        array('id' => $service_id)
    );

    // Update the scheduled service check if the status check or polling interval has changed
    if ($status_check) {
        homelab_add_service_check_schedule($service_id, $polling_interval);
    } else {
        wp_clear_scheduled_hook('homelab_check_service_status_' . $service_id);
    }

    // Update the scheduled data fetch if the get data or fetch interval has changed
    if ($get_data) {
        homelab_schedule_data_fetch($service_id, $fetch_interval);
    } else {
        wp_clear_scheduled_hook('homelab_fetch_service_data', array($service_id));
    }
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