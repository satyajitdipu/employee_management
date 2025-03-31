<?php

namespace App\Support;

use App\Settings\AttendanceSettings;

class Utility {
    public static function get_location_from_ip($ip)
    {
        $ip_locations = app(AttendanceSettings::class)->ip_locations;
        $location = "-";
        foreach($ip_locations as $key => $ip_location) {
            if($ip_location['ip'] == $ip) {
                $location = $ip_location['location'];
            }
        }
        return $location;
    }

    public static function allowed_time_options($interval=15) {
        $times = [];
        for ($minutes = 0; $minutes < 24 * 60; $minutes += $interval) {
            $hour = floor($minutes / 60);
            $minute = $minutes % 60;
    
            // Format the time in 24-hour format
            $time24 = sprintf('%02d%02d', $hour, $minute);
    
            // Special cases for midnight and noon
            if ($time24 == '0000') {
                $times[$time24] = '12 Midnight';
            } elseif ($time24 == '1200') {
                $times[$time24] = '12 Noon';
            } else {
                // Format the time in 12-hour format
                $time12 = date('g:i A', strtotime($time24));
                $times[$time24] = $time12;
            }
        }
        return $times;
    }

    public static function time_string_to_seconds($time_string) {
        // Extract hours and minutes from the time string
        $hours = intval(substr($time_string, 0, 2));
        $minutes = intval(substr($time_string, 2, 2));
    
        // Convert hours and minutes to seconds
        $total_seconds = ($hours * 3600) + ($minutes * 60);
    
        return $total_seconds;
    }
}
