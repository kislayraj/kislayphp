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

$host = env_string('KISLAY_ORDER_HOST', '0.0.0.0');
$port = env_int('KISLAY_ORDER_PORT', 9002);

$app = new KislayPHP\Core\App();
$config = new KislayPHP\Config\ConfigClient();
$metrics = new KislayPHP\Metrics\Metrics();
$queue = new KislayPHP\Queue\Queue();
$registry = new KislayPHP\Discovery\ServiceRegistry();
$registry->setClient(new RedisDiscoveryClient());

$service_url = "http://{$host}:{$port}";
$config->set('service.order', $service_url);
$registry->register('order-service', $service_url);
$queue->enqueue('events', ['type' => 'order.start', 'at' => time()]);

$app->get('/health', function ($req, $res) {
    $res->json(['ok' => true], 200);
});

$app->get('/orders', function ($req, $res) use ($metrics) {
    $metrics->inc('orders.list');
    $res->json([
        ['id' => 10, 'total' => 42.50],
        ['id' => 11, 'total' => 19.99],
    ], 200);
});

$app->post('/orders', function ($req, $res) use ($metrics) {
    $metrics->inc('orders.create');
    $res->json([
        'ok' => true,
        'order' => $req->all(),
    ], 201);
});

$app->get('/discovery', function ($req, $res) use ($registry) {
    $res->json($registry->list(), 200);
});

$app->get('/config', function ($req, $res) use ($config) {
    $res->json($config->all(), 200);
});

$app->post('/events', function ($req, $res) use ($queue) {
    $queue->enqueue('events', ['type' => 'order.event', 'payload' => $req->all()]);
    $res->json(['ok' => true], 201);
});

echo "Order service listening on {$host}:{$port}\n";
$app->listen($host, $port);
