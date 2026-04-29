<?php
/**
 * Visual Helper for CMS Charts and Dashboards
 */

function getChartScripts() {
    return '
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    ';
}

function getCalendarScripts() {
    return '
    <!-- FullCalendar CDN -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
    ';
}

/**
 * Color palettes for charts
 */
function getChartColors($count = 10, $alpha = 0.7) {
    $colors = [
        "rgba(54, 162, 235, $alpha)",   // Blue
        "rgba(255, 99, 132, $alpha)",   // Red
        "rgba(255, 206, 86, $alpha)",   // Yellow
        "rgba(75, 192, 192, $alpha)",   // Green
        "rgba(153, 102, 255, $alpha)",  // Purple
        "rgba(255, 159, 64, $alpha)",   // Orange
        "rgba(201, 203, 207, $alpha)",  // Grey
        "rgba(231, 74, 59, $alpha)",    // Danger Red
        "rgba(28, 200, 138, $alpha)",   // Success Green
        "rgba(78, 115, 223, $alpha)"    // Primary Blue
    ];
    
    return array_slice($colors, 0, $count);
}

function getStatusColor($status) {
    switch (strtolower($status)) {
        case 'present': return '#28a745';
        case 'absent': return '#dc3545';
        case 'late': return '#ffc107';
        default: return '#6c757d';
    }
}
?>
