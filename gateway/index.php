<?php
require __DIR__ . '/../shared/common.php';
require __DIR__ . '/../shared/redis_discovery.php';

require_extensions([
    'kislayphp_gateway',
    'kislayphp_discovery',
    'kislayphp_config',
    'kislayphp_metrics',
    'kislayphp_queue',
]);

$host = env_string('KISLAY_GATEWAY_HOST', '0.0.0.0');
$port = env_int('KISLAY_GATEWAY_PORT', 8081);

$user_host = env_string('KISLAY_USER_HOST', '127.0.0.1');
$user_port = env_int('KISLAY_USER_PORT', 9001);
$order_host = env_string('KISLAY_ORDER_HOST', '127.0.0.1');
$order_port = env_int('KISLAY_ORDER_PORT', 9002);

$config = new KislayPHP\Config\ConfigClient();
$registry = new KislayPHP\Discovery\ServiceRegistry();
$registry->setClient(new RedisDiscoveryClient());
$metrics = new KislayPHP\Metrics\Metrics();
$queue = new KislayPHP\Queue\Queue();

$config->set('service.user', "http://{$user_host}:{$user_port}");
$config->set('service.order', "http://{$order_host}:{$order_port}");

$registry->register('user-service', $config->get('service.user'));
$registry->register('order-service', $config->get('service.order'));

$gateway = new KislayPHP\Gateway\Gateway();
$user_target = $registry->resolve('user-service') ?: $config->get('service.user');
$order_target = $registry->resolve('order-service') ?: $config->get('service.order');
$gateway->addRoute('GET', '/users', $user_target);
$gateway->addRoute('GET', '/users/1', $user_target);
$gateway->addRoute('GET', '/orders', $order_target);
$gateway->addRoute('POST', '/orders', $order_target);

$metrics->inc('gateway.start');
$queue->enqueue('events', ['type' => 'gateway.start', 'at' => time()]);

echo "Gateway listening on {$host}:{$port}\n";
$gateway->listen($host, $port);
