<?php
return [
    'enabled'    => env('TELEMETRY_ENABLED', true),
    'secret'     => env('TELEMETRY_SECRET'),
    'beacon_url' => env('TELEMETRY_BEACON_URL'), // optionnel: serveur central du propriétaire
];
