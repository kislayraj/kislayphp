<?php
require __DIR__ . '/../shared/common.php';

require_extensions([
    'kislayphp_eventbus',
]);

$host = env_string('KISLAY_EVENTBUS_HOST', '0.0.0.0');
$port = env_int('KISLAY_EVENTBUS_PORT', 8090);

$bus = new KislayPHP\EventBus\Server();
$bus->on('connection', function ($socket) {
    $socket->emit('welcome', ['id' => $socket->id()]);
});

$bus->on('ping', function ($socket, $payload) {
    $socket->emit('pong', ['at' => time(), 'payload' => $payload]);
});

echo "EventBus listening on {$host}:{$port}\n";
$bus->listen($host, $port, '/socket.io/');
