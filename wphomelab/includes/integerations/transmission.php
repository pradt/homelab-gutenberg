<?php
/******************
* Transmission Data Collection
* -----------------------------
* This function collects data from Transmission, a BitTorrent client, for dashboard display.
* It fetches information about the torrents, including their status, progress, download and upload speeds, and more.
*
* Collected Data:
* - Total number of torrents
* - Number of torrents by status (downloading, seeding, stopped, checking, error)
* - Overall download and upload speeds
* - Total data downloaded and uploaded
* - Disk space used by torrents
*
* Data Structure Example (fetched_data):
* {
*   "total_torrents": 10,
*   "torrents_downloading": 3,
*   "torrents_seeding": 5,
*   "torrents_stopped": 1,
*   "torrents_checking": 1,
*   "torrents_error": 0,
*   "download_speed": 1024,
*   "upload_speed": 512,
*   "total_downloaded": 10737418240,
*   "total_uploaded": 5368709120,
*   "disk_space_used": 21474836480
* }
*
* Data not collected but available for extension:
* - Detailed torrent information (name, size, hash, added date)
* - Torrent-specific download and upload progress
* - Torrent file and tracker details
* - Torrent peer and connection information
* - Transmission settings and configuration
*
* Opportunities for additional data collection:
* - Torrent activity history and statistics
* - Bandwidth usage and limits
* - Scheduled and automated torrent management
* - Torrent labeling and categorization
* - Integration with media management tools
*
* Requirements:
* - Transmission RPC (Remote Procedure Call) interface should be enabled and accessible.
* - RPC authentication (username and password) is required.
*
* Parameters:
* - $rpc_url: The URL of the Transmission RPC interface.
* - $rpc_username: The username for RPC authentication.
* - $rpc_password: The password for RPC authentication.
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the RPC request process.
* - Stores error messages and timestamps for troubleshooting.
*******************/
function homelab_fetch_transmission_data($rpc_url, $rpc_username, $rpc_password, $service_id) {
    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;

    $rpc_client = new Transmission\Client($rpc_url, $rpc_username, $rpc_password);

    try {
        $response = $rpc_client->get(array('fields' => array('status', 'rateDownload', 'rateUpload', 'downloadedEver', 'uploadedEver', 'sizeWhenDone', 'leftUntilDone', 'percentDone')));
        $torrents = $response->arguments->torrents;

        $fetched_data['total_torrents'] = count($torrents);

        $status_counts = array(
            'downloading' => 0,
            'seeding' => 0,
            'stopped' => 0,
            'checking' => 0,
            'error' => 0,
        );

        $total_downloaded = 0;
        $total_uploaded = 0;
        $disk_space_used = 0;

        foreach ($torrents as $torrent) {
            $status = $torrent->status;
            if (isset($status_counts[$status])) {
                $status_counts[$status]++;
            }

            $total_downloaded += $torrent->downloadedEver;
            $total_uploaded += $torrent->uploadedEver;
            $disk_space_used += $torrent->sizeWhenDone - $torrent->leftUntilDone;
        }

        $fetched_data['torrents_downloading'] = $status_counts['downloading'];
        $fetched_data['torrents_seeding'] = $status_counts['seeding'];
        $fetched_data['torrents_stopped'] = $status_counts['stopped'];
        $fetched_data['torrents_checking'] = $status_counts['checking'];
        $fetched_data['torrents_error'] = $status_counts['error'];
        $fetched_data['download_speed'] = $response->arguments->downloadSpeed;
        $fetched_data['upload_speed'] = $response->arguments->uploadSpeed;
        $fetched_data['total_downloaded'] = $total_downloaded;
        $fetched_data['total_uploaded'] = $total_uploaded;
        $fetched_data['disk_space_used'] = $disk_space_used;
    } catch (Exception $e) {
        $error_message = "RPC request failed: " . $e->getMessage();
        $error_timestamp = current_time('mysql');
    }

    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
}