<?php
class KI_Logger {
    public static function log($message) {
        $logs = get_option('ki_logs', []);
        $logs[] = current_time('mysql') . ' - ' . $message;
        if (count($logs) > 50) array_shift($logs);
        update_option('ki_logs', $logs);
    }
}
