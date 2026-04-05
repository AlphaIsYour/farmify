<?php
return [
    'threshold_moisture'  => env('THRESHOLD_MOISTURE', 30),
    'threshold_stop'      => env('THRESHOLD_STOP', 70),
    'poll_interval'       => env('POLL_INTERVAL', 5),
    'device_timeout'      => env('DEVICE_TIMEOUT', 60),
    'ingest_interval'     => env('INGEST_INTERVAL', 10),
    'auto_irrigation'     => env('AUTO_IRRIGATION', true),
    'auto_refresh'        => env('AUTO_REFRESH', true),
    'show_offline'        => env('SHOW_OFFLINE', true),
    'default_chart_range' => env('DEFAULT_CHART_RANGE', '6h'),
];