<?php

require_once __DIR__ . '/../config/app.php';

// Initialize Registry
$registry = new Kislay\Discovery\ServiceRegistry();

// Initialize Gateway (if needed, or usually in its own process)
$gateway = new Kislay\Gateway\Gateway();

return [
    'registry' => $registry,
    'gateway'  => $gateway
];