<?php
function homelab_format_speed($speed) {
    $units = array('B/s', 'KB/s', 'MB/s', 'GB/s');
    $unit = 0;

    while ($speed >= 1024 && $unit < count($units) - 1) {
        $speed /= 1024;
        $unit++;
    }

    return round($speed, 2) . ' ' . $units[$unit];
}

function homelab_format_size($size) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $unit = 0;

    while ($size >= 1024 && $unit < count($units) - 1) {
        $size /= 1024;
        $unit++;
    }

    return round($size, 2) . ' ' . $units[$unit];
}

function homelab_format_time($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $seconds = $seconds % 60;

    return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
}