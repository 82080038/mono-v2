
<?php
// Helper functions for analytics
if (!function_exists('array_sum')) {
    function array_sum($array) {
        return array_reduce($array, function($carry, $item) {
            return $carry + $item;
        }, 0);
    }
}

if (!function_exists('array_column')) {
    function array_column($array, $column) {
        return array_map(function($item) use ($column) {
            return $item[$column] ?? 0;
        }, $array);
    }
}

if (!function_exists('deg2rad')) {
    function deg2rad($degrees) {
        return $degrees * (M_PI / 180);
    }
}
?>