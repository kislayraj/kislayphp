<?php
require __DIR__ . '/../../shared/common.php';
require __DIR__ . '/../../shared/redis_discovery.php';

require_extensions([
    'kislayphp_extension',
    'kislayphp_discovery',
    'kislayphp_config',
    'kislayphp_metrics',
    'kislayphp_queue',
]);

$host = env_string('KISLAY_USER_HOST', '0.0.0.0');
$port = env_int('KISLAY_USER_PORT', 9001);

$app = new KislayPHP\Core\App();
$config = new KislayPHP\Config\ConfigClient();
$metrics = new KislayPHP\Metrics\Metrics();
$queue = new KislayPHP\Queue\Queue();
$registry = new KislayPHP\Discovery\ServiceRegistry();
$registry->setClient(new RedisDiscoveryClient());

$service_url = "http://{$host}:{$port}";
$config->set('service.user', $service_url);
$registry->register('user-service', $service_url);
$queue->enqueue('events', ['type' => 'user.start', 'at' => time()]);

$app->get('/health', function ($req, $res) {
    $res->json(['ok' => true], 200);
});

$app->get('/users', function ($req, $res) use ($metrics) {
    $metrics->inc('users.list');
    $res->json([
        ['id' => 1, 'name' => 'Ava'],
        ['id' => 2, 'name' => 'Leo'],
    ], 200);
});

$app->get('/users/:id', function ($req, $res) use ($metrics) {
    $metrics->inc('users.get');
    $res->json([
        'id' => $req->input('id'),
        'name' => 'User',
    ], 200);
});

$app->get('/discovery', function ($req, $res) use ($registry) {
    $res->json($registry->list(), 200);
});

$app->get('/config', function ($req, $res) use ($config) {
    $res->json($config->all(), 200);
});

$app->post('/events', function ($req, $res) use ($queue) {
    $queue->enqueue('events', ['type' => 'user.event', 'payload' => $req->all()]);
    $res->json(['ok' => true], 201);
});

echo "User service listening on {$host}:{$port}\n";
$app->listen($host, $port);
