<?php

/* File: helpers.php
 * Authors: Timothy Pace, Zachary Graham
 * Modularize useful functions in the PHP API
 *
 */

$directions = array("N", "NNE", "NE", "ENE", "E", "ESE", "SE", "SSE", "S", "SSW", "SW", "WSW", "W", "WNW", "NW", "NNW");

function degrees_to_compass($num) {
    global $directions;
    $index = (int) ($num/22.5 + 0.5);
    return $directions[($index % 16)];
}

function categorize_wind($windSpeed) {
    if ($windSpeed < 1.5) return 0;
    else if ($windSpeed >= 1.5 && $windSpeed < 5.5) return 1;
    else if ($windSpeed >= 5.5 && $windSpeed < 11) return 2;
    else if ($windSpeed >= 11 && $windSpeed < 17) return 3;
    else if ($windSpeed >= 17 && $windSpeed < 24.5) return 4;
    else if ($windSpeed >= 24.5 && $windSpeed < 32.5) return 5;
    else if ($windSpeed >= 32.5) return 6;
    else return -1;
}
