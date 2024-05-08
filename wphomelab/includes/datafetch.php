<?php
add_action('homelab_fetch_service_data', 'homelab_fetch_service_data', 10, 1);
//Function that is called by the scheduled event
function homelab_fetch_service_data($service_id) {
    // Log the start of the function
    error_log("homelab_fetch_service_data: Starting data fetch for service ID $service_id");

    // Fetch data from the service and store it in the database
    $service = homelab_get_service($service_id);

    // Log the retrieved service object
    error_log("homelab_fetch_service_data: Retrieved service object:");
    error_log(print_r($service, true));

    $data = homelab_get_data_from_service($service);

    // Log the retrieved data
    error_log("homelab_fetch_service_data: Retrieved data from service:");
    error_log(print_r($data, true));

    homelab_save_service_data($service_id, $data);

    // Log the completion of the function
    error_log("homelab_fetch_service_data: Data fetch completed for service ID $service_id");
}



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
}

/**
 * Fetches service data based on the provided service details.
 *
 * @param array $service An associative array containing the service details.
 *                       Required keys:
 *                       - 'service_type': The type of the service.
 *                       - 'api_url': The API URL of the service.
 *                       - 'api_key': The API key for authentication (if applicable).
 *                       - 'username': The username for authentication (if applicable).
 *                       - 'password': The password for authentication (if applicable).
 *                       Additional keys may be required depending on the service type.
 *
 * @return array The fetched service data, or an empty array if the service type is not supported.
 */
function homelab_get_data_from_service($service)
{
    $service_type = $service['service_type'];
    $api_url = $service['api_url'];
    $api_key = $service['api_key'];
    $username = $service['username'];
    $password = $service['password'];
    $service_id = $service['id'];

    // Fetch data from the service based on the service type
    switch ($service_type) {
        case 'adguard':
            return homelab_fetch_adguard_home_data($api_url, $username, $password, $service_id);
        case 'audiobookshelf':
            return homelab_fetch_audiobookshelf_stats($api_url, $api_key, $service_id);
        case 'authentik':
            return homelab_fetch_authentik_data($api_url, $api_key, $service_id);
        case 'autobrr':
            return homelab_fetch_autobrr_data($api_url, $api_key, $service_id);
        case 'calibre-web':
            return homelab_fetch_calibre_web_data($api_url, $username, $password, $service_id);
        case 'changedetection':
            return homelab_fetch_changedetection_data($api_url, $api_key, $service_id);
        case 'channels-dvr':
            return homelab_fetch_channels_dvr_data($api_url, $service_id);
        case 'cloudflare-tunnels':
            $tunnel_id = $service['tunnel_id'];
            $account_id = $service['account_id'];
            return homelab_fetch_cloudflare_tunnels_data($api_url, $api_key, $tunnel_id, $account_id, $service_id);
        case 'cytube':
            $enable_now_playing = isset($service['enable_now_playing']) ? (bool) $service['enable_now_playing'] : false;
            $enable_blocks = isset($service['enable_blocks']) ? (bool) $service['enable_blocks'] : false;
            return homelab_fetch_cytube_data($api_url, $api_key, $service_id, $enable_now_playing, $enable_blocks);
        case 'deluge':
            return homelab_fetch_deluge_data($api_url, $username, $password, $service_id);
        case 'emby':
            $enable_now_playing = isset($service['enable_now_playing']) ? (bool) $service['enable_now_playing'] : false;
            $enable_recently_added = isset($service['enable_blocks']) ? (bool) $service['enable_blocks'] : false;
            return homelab_fetch_emby_data($api_url, $api_key, $service_id, $enable_now_playing, $enable_recently_added);
        case 'esphome':
            return homelab_fetch_esphome_data($api_url, $service_id);
        case 'evcc':
            return homelab_fetch_evcc_data($api_url, $service_id);
        case 'fileflows':
            return homelab_fetch_fileflows_data($api_url, $service_id);
        case 'flood':
            return homelab_fetch_flood_data($api_url, $username, $password, $service_id);
        case 'freshrss':
            return homelab_fetch_freshrss_data($api_url, $username, $password, $service_id);
        case 'fritzbox':
            return homelab_fetch_fritzbox_data($api_url, $username, $password, $service_id);
        case 'gamedig':
            $server_type = $service['server_type'];
            $server_host = $service['server_host'];
            $server_port = $service['server_port'];
            return homelab_fetch_gamedig_data($server_type, $server_host, $server_port, $service_id);
        case 'gitea':
            return homelab_fetch_gitea_data($api_url, $api_key, $service_id);
        case 'glances':
            return homelab_fetch_glances_data($api_url, $service_id);
        case 'gluten':
            return homelab_fetch_gluten_data($api_url, $api_key, $service_id);
        case 'gotify':
            return homelab_fetch_gotify_data($api_url, $api_key, $service_id);
        case 'grafana':
            return homelab_fetch_grafana_data($api_url, $username, $password, $service_id);
        case 'healthchecks':
            $check_uuid = $service['check_uuid'];
            return homelab_fetch_healthchecks_data($api_url, $api_key, $check_uuid, $service_id);
        case 'homeassistant':
            return homelab_fetch_homeassistant_data($api_url, $api_key, $service_id);
        case 'homebox':
            return homelab_fetch_homebox_data($api_url, $username, $password, $service_id);
        case 'immich':
            return homelab_fetch_immich_data($api_url, $api_key, $service_id);
        case 'jdownloader':
            $client_id = $service['client_id'];
            return homelab_fetch_jdownloader_data($api_url, $username, $password, $client_id, $service_id);
        case 'jellyfin':
            return homelab_fetch_jellyfin_data($api_url, $api_key, $service_id);
        case 'jellyseerr':
            return homelab_fetch_jellyseerr_data($api_url, $api_key, $service_id);
        case 'kavita':
            return homelab_fetch_kavita_data($api_url, $username, $password, $service_id);
        case 'komga':
            return homelab_fetch_komga_data($api_url, $username, $password, $service_id);
        case 'kopia':
            return homelab_fetch_kopia_data($api_url, $username, $password, $service_id);
        case 'lidarr':
            return homelab_fetch_lidarr_data($api_url, $api_key);
        case 'mastodon':
            $toots_limit = isset($service['toots_limit']) ? (int) $service['toots_limit'] : 5;
            return homelab_fetch_mastodon_data($api_url, $api_key, $toots_limit, $service_id);
        case 'mealie':
            return homelab_fetch_mealie_data($api_url, $api_key, $service_id);
        case 'medusa':
            return homelab_fetch_medusa_data($api_url, $api_key, $service_id);
        case 'mikrotik':
            return homelab_fetch_mikrotik_data($api_url, $username, $password, $service_id);
        case 'minecraft':
            $server_ip = $api_url;
            $server_port = $service['server_port'];
            return homelab_fetch_minecraft_data($server_ip, $server_port, $service_id);
        case 'miniflux':
            return homelab_fetch_miniflux_data($api_url, $api_key, $service_id);
        case 'moonraker':
            return homelab_fetch_moonraker_data($api_url, $api_key, $service_id);
        case 'mylar3':
            return homelab_fetch_mylar3_data($api_url, $api_key, $service_id);
        case 'navidrome':
            $salt = $service['salt'];
            return homelab_fetch_navidrome_data($api_url, $api_key, $username, $salt, $service_id);
        case 'netdata':
            return homelab_fetch_netdata_data($api_url, $api_key, $service_id);
        case 'nextcloud':
            return homelab_fetch_nextcloud_data($api_url, $api_key, $username, $password, $service_id);
        case 'nextdns':
            return homelab_fetch_nextdns_data($api_url, $api_key, $service_id);
        case 'nginx-proxy-manager':
            return homelab_fetch_nginx_proxy_manager_data($api_url, $username, $password, $service_id);
        case 'nzbget':
            return homelab_fetch_nzbget_data($api_url, $username, $password, $service_id);
        case 'octoprint':
            return homelab_fetch_octoprint_data($api_url, $api_key, $service_id);
        case 'omada':
            $site_name = $service['site_name'];
            return homelab_fetch_omada_data($api_url, $username, $password, $site_name, $service_id);
        case 'ombi':
            return homelab_fetch_ombi_data($api_url, $api_key, $service_id);
        case 'omni':
            return homelab_fetch_omni_data($api_url, $api_key, $service_id);
        case 'opendtu':
            return homelab_fetch_opendtu_data($api_url, $api_key, $service_id);
        case 'openmediavault':
            return homelab_fetch_omv_data($api_url, $username, $password, $service_id);
        case 'opnsense':
            return homelab_fetch_opnsense_data($api_url, $username, $password, $service_id);
        case 'overseerr':
            return homelab_fetch_overseerr_data($api_url, $api_key, $service_id);
        case 'paperless':
            return homelab_fetch_paperless_data($api_url, $api_key, $username, $password, $service_id);
        case 'peanut':
            return homelab_fetch_peanut_data($api_url, $api_key, $service_id);
        case 'pfsense':
            return homelab_fetch_pfsense_data($api_url, $api_key, $username, $password, $service_id);
        case 'photoprism':
            return homelab_fetch_photoprism_data($api_url, $username, $password, $service_id);
        case 'pialert':
            return homelab_fetch_pialert_data($api_url, $api_key, $service_id);
        case 'pihole':
            return homelab_fetch_pihole_data($api_url, $api_key, $service_id);
        case 'plantit':
            return homelab_fetch_plantit_data($api_url, $api_key, $service_id);
        case 'plex':
            return homelab_fetch_plex_data($api_url, $api_key, $service_id);
        case 'portainer':
            return homelab_fetch_portainer_data($api_url, $api_key, $service_id);
        case 'prowlarr':
            return homelab_fetch_prowlarr_data($api_url, $api_key, $service_id);
        case 'proxmox':
            return homelab_fetch_proxmox_data($api_url, $username, $api_key, $service_id);
        case 'proxmox-backup':
            return homelab_fetch_proxmox_backup_data($api_url, $username, $password, $service_id);
        case 'pterodactyl':
            return homelab_fetch_pterodactyl_data($api_url, $api_key, $service_id);
        case 'pyload':
            return homelab_fetch_pyload_data($api_url, $username, $password, $service_id);
        case 'qbittorrent':
            return homelab_fetch_qbittorrent_data($api_url, $username, $password, $service_id);
        case 'qnap':
            return homelab_fetch_qnap_data($api_url, $username, $password, $service_id);
        case 'radarr':
            return homelab_fetch_radarr_data($api_url, $api_key, $service_id);
        case 'readarr':
            return homelab_fetch_readarr_data($api_url, $api_key, $service_id);
        case 'romm':
            return homelab_fetch_romm_data($api_url, $username, $password, $service_id);
        case 'rutorrent':
            return homelab_fetch_rutorrent_data($api_url, $username, $password, $service_id);
        case 'sabnzbd':
            return homelab_fetch_sabnzbd_data($api_url, $api_key, $service_id);
        case 'scrutiny':
            return homelab_fetch_scrutiny_data($api_url, $api_key, $service_id);
        case 'sonarr':
            return homelab_fetch_sonarr_data($api_url, $api_key, $service_id);
        case 'speedtest-tracker':
            return homelab_fetch_speedtest_data($api_url, $api_key, $service_id);
        case 'stash':
            return homelab_fetch_stash_data($api_url, $api_key, $service_id);
        case 'syncthing-relay':
            return homelab_fetch_syncthing_relay_data($api_url, $api_key, $service_id);
        case 'synology':
            $volume = $service['volume'];
            return homelab_fetch_synology_data($api_url, $username, $password, $volume, $service_id);
        case 'synology-download-station':
            return homelab_fetch_synology_download_station_data($api_url, $username, $password, $service_id);
        case 'tailscale':
            return homelab_fetch_tailscale_data($api_url, $api_key, $service_id);
        case 'tautulli':
            return homelab_fetch_tautulli_data($api_url, $api_key, $service_id);
        case 'tdarr':
            return homelab_fetch_tdarr_data($api_url, $api_key, $service_id);
        case 'traefik':
            return homelab_fetch_traefik_data($api_url, $username, $password, $service_id);
        case 'transmission':
            $rpc_url = $api_url;
            $rpc_username = $username;
            $rpc_password = $password;
            return homelab_fetch_transmission_data($rpc_url, $rpc_username, $rpc_password, $service_id);
        case 'truenas':
            $auth = $service['auth'];
            return homelab_fetch_truenas_data($api_url, $auth, $service_id);
        case 'tube-archivist':
            return homelab_fetch_tube_archivist_data($api_url, $api_key, $service_id);
        case 'unifi':
            return homelab_fetch_unifi_data($api_url, $username, $password, $service_id);
        case 'unmanic':
            return homelab_fetch_unmanic_data($api_url, $api_key, $service_id);
        case 'uptimekuma':
            $slug = $service['slug'];
            return homelab_fetch_uptimekuma_data($api_url, $service_id);
        case 'uptimerobot':
            return homelab_fetch_uptimerobot_data($api_url, $api_key, $service_id);
        case 'watchtower':
            return homelab_fetch_watchtower_data($api_url, $api_key, $service_id);
        case 'whatsup-docker':
            return homelab_fetch_whats_up_docker_data($api_url, $username, $password, $service_id);
        case 'mysql':
            return homelab_fetch_mysql_data($api_url, $username, $password, $service_id);
        case 'postgres':
            return homelab_fetch_postgres_data($api_url, $username, $password, $service_id);
        case 'mongodb':
            return homelab_fetch_mongodb_data($api_url, $username, $password, $service_id);
        case 'redis':
            $password = $service['password'] ?? null;
            return homelab_fetch_redis_data($api_url, $password, $service_id);
        default:
            return array();
    }
}

function homelab_save_service_data($service_id, $data, $error_message = null, $error_timestamp = null)
{
    global $wpdb;
    $table_name_service_data = $wpdb->prefix . 'homelab_service_data';

    // Generate a GUID
    $guid = wp_generate_uuid4();

    $wpdb->insert(
        $table_name_service_data,
        array(
            'id' => $guid,
            'service_id' => $service_id,
            'fetched_at' => current_time('mysql'),
            'data' => json_encode($data),
            'is_scheduled' => 1,
            'error_message' => $error_message,
            'error_timestamp' => $error_timestamp,
        )
    );
}
