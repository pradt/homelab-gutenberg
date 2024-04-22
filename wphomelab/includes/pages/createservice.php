<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}



// Create new service page callback
function homelab_create_service_page()
{
    ob_start();
    // Handle form submission
    if (isset($_POST['submit'])) {
        homelab_save_service();
        wp_redirect(admin_url('admin.php?page=homelab-list-services'));
        exit;
    }

    /**
     * Get the fields to show based on the selected service and the "Get Data" checkbox state.
     *
     * @return array An associative array mapping service names to their respective fields to show.
     *               Each service is represented as a key-value pair, where the key is the service name
     *               and the value is an array of field names to display when that service is selected
     *               and the "Get Data" checkbox is checked.
     */


    function getFieldsToShow()
    {
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
            'sonarr' => ['api_url', 'api_key', 'fetch_interval'],
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
    function getMandatoryFields()
    {
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
            'plantit' => ['api_url', 'api_key', 'fetch_interval'],
            'plex' => ['api_url', 'api_key', 'fetch_interval'],
            'portainer' => ['api_url', 'api_key', 'fetch_interval'],
            'prometheus' => ['api_url', 'fetch_interval'],
            'proxmox' => ['api_url', 'username', 'password', 'fetch_interval'],
            'proxmox-backup' => ['api_url', 'username', 'password', 'fetch_interval'],
            'pterodactyl' => ['api_url', 'api_key', 'fetch_interval'],
            'pyload' => ['api_url', 'username', 'password', 'fetch_interval'],
            'qbittorrent' => ['api_url', 'username', 'password', 'fetch_interval'],
            'qnap' => ['api_url', 'username', 'password', 'fetch_interval'],
            'radarr' => ['api_url', 'api_key', 'fetch_interval'],
            'romm' => ['api_url', 'fetch_interval'],
            'rutorrent' => ['api_url', 'fetch_interval'],
            'sabnzbd' => ['api_url', 'api_key', 'fetch_interval'],
            'scrutiny' => ['api_url', 'fetch_interval'],
            'sonarr' => ['api_url', 'api_key', 'fetch_interval'],
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

    // Render the create service form
    ?>
    <div class="wrap">
        <h1>Create New Service</h1>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th><label for="name">Name</label></th>
                    <td><input type="text" name="name" id="name" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="service_type">Service/Application</label></th>
                    <td>
                        <select name="service_type" id="service_type">
                            <option value="">Select a service</option>
                            <option value="adguard">AdGuard</option>
                            <option value="audiobookshelf">AudioBookshelf</option>
                            <option value="authentik">Authentik</option>
                            <option value="autobrr">Autobrr</option>
                            <option value="calibre-web">Calibre-web</option>
                            <option value="changedetection">Changedetection.io</option>
                            <option value="channels-dvr">Channels DVR Server</option>
                            <option value="cloudflare-tunnels">Cloudflare Tunnels</option>
                            <option value="cytube">CyTube</option>
                            <option value="deluge">Deluge</option>
                            <option value="emby">Emby</option>
                            <option value="esphome">ESPHome</option>
                            <option value="evcc">EVCC</option>
                            <option value="fileflows">FileFlows</option>
                            <option value="flood">Flood</option>
                            <option value="freshrss">FreshRSS</option>
                            <option value="fritzbox">FRITZ!Box</option>
                            <option value="gamedig">GameDig</option>
                            <option value="gitea">Gitea</option>
                            <option value="glances">Glances</option>
                            <option value="gluten">Gluten</option>
                            <option value="gotify">Gotify</option>
                            <option value="grafana">Grafana</option>
                            <option value="healthchecks">Health Checks</option>
                            <option value="homeassistant">Home Assistant</option>
                            <option value="homebox">Homebox</option>
                            <option value="immich">Immich</option>
                            <option value="jdownloader">JDownloader</option>
                            <option value="jellyfin">Jellyfin</option>
                            <option value="jellyseerr">Jellyseerr</option>
                            <option value="kavita">Kavita</option>
                            <option value="komga">Komga</option>
                            <option value="kopia">Kopia</option>
                            <option value="lidarr">Lidarr</option>
                            <option value="mastodon">Mastodon</option>
                            <option value="mealie">Mealie</option>
                            <option value="medusa">Medusa</option>
                            <option value="mikrotik">Mikrotik</option>
                            <option value="minecraft">Minecraft Server</option>
                            <option value="miniflux">Miniflux 2</option>
                            <option value="moonraker">Moonraker</option>
                            <option value="mongodb">mongoDb</option>
                            <option value="mysql">mySQL</option>
                            <option value="mylar3">Mylar3</option>
                            <option value="navidrome">Navidrome</option>
                            <option value="netdata">Netdata</option>
                            <option value="nextcloud">Nextcloud</option>
                            <option value="nextdns">NextDNS</option>
                            <option value="nginx-proxy-manager">Nginx Proxy Manager</option>
                            <option value="nzbget">NZBGet</option>
                            <option value="octoprint">OctoPrint</option>
                            <option value="omada">Omada</option>
                            <option value="ombi">Ombi</option>
                            <option value="omni">Omni</option>
                            <option value="opendtu">OpenDTU</option>
                            <option value="openmediavault">OpenMediaVault</option>
                            <option value="opnsense">OPNSense</option>
                            <option value="overseerr">Overseerr</option>
                            <option value="paperless">Paperless NGX</option>
                            <option value="peanut">PeaNUT</option>
                            <option value="pfsense">pfSense</option>
                            <option value="photoprism">PhotoPrism</option>
                            <option value="pialert">PiAlert</option>
                            <option value="pihole">Pi-hole</option>
                            <option value="plantit">Plant-It</option>
                            <option value="plex">Plex</option>
                            <option value="portainer">Portainer</option>
                            <option value="postgres">Postgres</option>
                            <option value="prometheus">Prometheus</option>
                            <option value="prowlarr">Prowlarr</option>
                            <option value="proxmox">Proxmox</option>
                            <option value="proxmox-backup">Proxmox Backup</option>
                            <option value="pterodactyl">Pterodactyl</option>
                            <option value="pyload">pyLoad</option>
                            <option value="qbittorrent">qBittorrent</option>
                            <option value="qnap">QNAP</option>
                            <option value="radarr">Radarr</option>
                            <option value="readarr">Readarr</option>
                            <option value="redis">Redis</option>
                            <option value="romm">ROMM</option>
                            <option value="rutorrent">ruTorrent</option>
                            <option value="sabnzbd">SABnzbd</option>
                            <option value="scrutiny">Scrutiny</option>
                            <option value="sonarr">Sonarr</option>
                            <option value="speedtest-tracker">Speedtest Tracker</option>
                            <option value="stash">Stash</option>
                            <option value="syncthing-relay">Syncthing Relay Server</option>
                            <option value="synology">Synology Disk Station</option>
                            <option value="synology-download-station">Synology Download Station</option>
                            <option value="tailscale">Tailscale</option>
                            <option value="tautulli">Tautulli</option>
                            <option value="tdarr">Tdarr</option>
                            <option value="traefik">Traefik</option>
                            <option value="transmission">Transmission</option>
                            <option value="truenas">TrueNAS</option>
                            <option value="tube-archivist">Tube Archivist</option>
                            <option value="unifi">Unifi Controller</option>
                            <option value="unmanic">Unmanic</option>
                            <option value="uptimekuma">Uptime Kuma</option>
                            <option value="uptimerobot">UptimeRobot</option>
                            <option value="watchtower">Watchtower</option>
                            <option value="whatsup-docker">What's Up Docker</option>
                            <option value="other">Other</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="description">Description</label></th>
                    <td><textarea name="description" id="description" rows="4" cols="50"></textarea></td>
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
                                echo '<option value="' . $image->id . '">' . $image->name . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="tags">Tags</label></th>
                    <td><input type="text" name="tags" id="tags" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="category">Category</label></th>
                    <td>
                        <?php
                        wp_dropdown_categories(
                            array(
                                'name' => 'category',
                                'id' => 'category',
                                'taxonomy' => 'category',
                                'hide_empty' => 0,
                                'show_option_none' => 'Select a category',
                            )
                        );
                        ?>
                    </td>
                </tr>
                <tr>
                    <th><label for="color">Color</label></th>
                    <td><input type="color" name="color" id="color"></td>
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
                            foreach ($services as $service) {
                                echo '<option value="' . $service->id . '">' . $service->name . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="service_url">Service URL</label></th>
                    <td><input type="text" name="service_url" id="service_url" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="alt_page_url">Alternative Page URL</label></th>
                    <td><input type="text" name="alt_page_url" id="alt_page_url" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="status_check">Status Check</label></th>
                    <td><input type="checkbox" name="status_check" id="status_check" value="1"></td>
                </tr>
                <tr class="status-check-fields" style="display: none;">
                    <th><label for="status_check_url">Status Check URL</label></th>
                    <td><input type="text" name="status_check_url" id="status_check_url" class="regular-text"></td>
                </tr>
                <tr class="status-check-fields" style="display: none;">
                    <th><label for="disable_ssl">Disable SSL</label></th>
                    <td><input type="checkbox" name="disable_ssl" id="disable_ssl" value="1"></td>
                </tr>
                <tr class="status-check-fields" style="display: none;">
                    <th><label for="accepted_response">Accepted HTTP Response</label></th>
                    <td><input type="text" name="accepted_response" id="accepted_response" class="regular-text"></td>
                </tr>
                <tr class="status-check-fields" style="display: none;">
                    <th><label for="polling_interval">Polling Interval (seconds)</label></th>
                    <td><input type="number" name="polling_interval" id="polling_interval" class="regular-text" min="5">
                    </td>
                </tr>
                <tr class="status-check-fields" style="display: none;">
                    <th><label for="notify_email">Notify Email</label></th>
                    <td><input type="email" name="notify_email" id="notify_email" class="regular-text"></td>
                </tr>
                <tr class="get-data-fields" style="display: none;">
                    <th><label for="get_data">Get Data</label></th>
                    <td><input type="checkbox" name="get_data" id="get_data" value="1"></td>
                </tr>
                <tr class="api-fields" style="display: none;">
                    <th><label for="api_url">API URL</label></th>
                    <td><input type="text" name="api_url" id="api_url" class="regular-text"></td>
                </tr>
                <tr class="api-fields" style="display: none;">
                    <th><label for="api_key">API Key</label></th>
                    <td><input type="text" name="api_key" id="api_key" class="regular-text"></td>
                </tr>
                <tr class="api-fields" style="display: none;">
                    <th><label for="username">Username</label></th>
                    <td><input type="text" name="username" id="username" class="regular-text"></td>
                </tr>
                <tr class="api-fields" style="display: none;">
                    <th><label for="password">Password</label></th>
                    <td><input type="password" name="password" id="password" class="regular-text"></td>
                </tr>
                <tr class="api-fields" style="display: none;">
                    <th><label for="tunnel_id">Tunnel ID</label></th>
                    <td><input type="text" name="tunnel_id" id="tunnel_id" class="regular-text"></td>
                </tr>
                <tr class="api-fields" style="display: none;">
                    <th><label for="account_id">Account ID</label></th>
                    <td><input type="text" name="account_id" id="account_id" class="regular-text"></td>
                </tr>
                <tr class="api-fields" style="display: none;">
                    <th><label for="enable_now_playing">Enable Now Playing</label></th>
                    <td><input type="checkbox" name="enable_now_playing" id="enable_now_playing" value="1"></td>
                </tr>
                <tr class="api-fields" style="display: none;">
                    <th><label for="enable_blocks">Enable Blocks</label></th>
                    <td><input type="checkbox" name="enable_blocks" id="enable_blocks" value="1"></td>
                </tr>
                <tr class="api-fields" style="display: none;">
                    <th><label for="check_uuid">Check UUID</label></th>
                    <td><input type="text" name="check_uuid" id="check_uuid" class="regular-text"></td>
                </tr>
                <tr class="api-fields" style="display: none;">
                    <th><label for="client_id">Client ID</label></th>
                    <td><input type="text" name="client_id" id="client_id" class="regular-text"></td>
                </tr>
                <tr class="api-fields" style="display: none;">
                    <th><label for="toots_limit">Toots Limit</label></th>
                    <td><input type="number" name="toots_limit" id="toots_limit" class="regular-text" min="1"></td>
                </tr>
                <tr class="api-fields" style="display: none;">
                    <th><label for="server_port">Server Port</label></th>
                    <td><input type="number" name="server_port" id="server_port" class="regular-text" min="1" max="65535">
                    </td>
                </tr>
                <tr class="api-fields" style="display: none;">
                    <th><label for="volume">Volume</label></th>
                    <td><input type="text" name="volume" id="volume" class="regular-text"></td>
                </tr>
                <tr class="api-fields" style="display: none;">
                    <th><label for="fetch_interval">Fetch Every (seconds)</label></th>
                    <td><input type="number" name="fetch_interval" id="fetch_interval" class="regular-text" min="5"
                            value="600"></td>
                </tr>
                <tr class="api-fields" style="display: none;">
                    <th>&nbsp;</th>
                    <td>
                        <button type="button" class="button" id="test-connection">Test Connection</button>
                        <span class="spinner" id="test-connection-spinner" style="float: none; visibility: hidden;"></span>
                        <div id="test-connection-result" style="margin-top: 5px;"></div>
                    </td>
                </tr>
            </table>
            <?php submit_button('Create Service'); ?>
        </form>
    </div>
    <script>
        jQuery(document).ready(function ($) {

            //Display/hide Status Check fields
            $('#status_check').change(function () {
                $('.status-check-fields').toggle(this.checked);
            });

            //Manage the Get Data field and Service Dependant fields
            $('#service_type').change(function () {
                var selectedService = $(this).val();

                if (selectedService === 'other' || selectedService === '') {
                    // Hide the 'get_data' field
                    $('#get_data').closest('tr').hide();
                } else {
                    // Show the 'get_data' field
                    $('#get_data').closest('tr').show();
                    updateAPIFields();
                }
            });

            $('#get_data').change(function () {
                updateAPIFields();
            });

            function updateAPIFields() {
                var selectedService = $('#service_type').val();
                var fieldsToShow = <?php echo json_encode(getFieldsToShow()); ?>;
                var mandatoryFields = <?php echo json_encode(getMandatoryFields()); ?>;

                // Hide all API fields and remove the 'required' attribute
                $('.api-fields').hide();
                $('.api-fields input').prop('required', false);

                // Check if the selected service is not empty or 'other' and 'get_data' is checked
                if (selectedService !== '' && selectedService !== 'other' && $('#get_data').is(':checked')) {
                    // Show the API fields for the selected service
                    $.each(fieldsToShow[selectedService], function (index, field) {
                        $('#' + field).closest('tr').show();
                    });

                    // Set the 'required' attribute for the mandatory fields
                    $.each(mandatoryFields[selectedService], function (index, field) {
                        $('#' + field).prop('required', true);
                    });
                }
            }

                
            });

    </script>
    <?php



    ob_end_flush();

}

// Save service details
function homelab_save_service() {
    ob_start();
    global $wpdb;
    $table_name_services = $wpdb->prefix . 'homelab_services';

    try {
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
        $service_type = sanitize_text_field($_POST['service_type']);
        $get_data = isset($_POST['get_data']) ? 1 : 0;
        $api_url = sanitize_text_field($_POST['api_url']);
        $api_key = sanitize_text_field($_POST['api_key']);
        $username = sanitize_text_field($_POST['username']);
        $password = sanitize_text_field($_POST['password']);
        $fetch_interval = intval($_POST['fetch_interval']);

        $wpdb->insert(
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
                'service_type' => $service_type,
                'get_data' => $get_data,
                'api_url' => $api_url,
                'api_key' => $api_key,
                'username' => $username,
                'password' => $password,
                'fetch_interval' => $fetch_interval,
            )
        );

        $service_id = $wpdb->insert_id;

        if ($status_check) {
            homelab_add_service_check_schedule($service_id, $polling_interval);
        }

        if ($get_data) {
            homelab_schedule_data_fetch($service_id, $fetch_interval);
        }

        // Handle success case
        // ...

    } catch (Exception $e) {
        // Handle the exception
        error_log("Error saving service: " . $e->getMessage());
        // Display an error message or redirect to an error page
        // ...
    }
}

/* function homelab_schedule_data_fetch($service_id, $fetch_interval)
{
    // Fetch data from the service and store it in the database
    $service = homelab_get_service($service_id);
    $data = homelab_fetch_service_data($service);
    homelab_save_service_data($service_id, $data);

    // Schedule the next data fetch
    wp_schedule_single_event(time() + $fetch_interval, 'homelab_fetch_service_data', array($service_id));
}

//This will fetch the service from the DB as an array, the second parameter ARRAY_A returns this as an array instead of an object 
function homelab_get_service($service_id)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'homelab_services';

    $service = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $service_id
        ),
        ARRAY_A
    );

    return $service;
} */


