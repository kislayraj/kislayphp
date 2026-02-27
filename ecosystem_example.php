<?php
/**
 * Kislay Ecosystem Full Integration Example
 * 
 * This example demonstrates:
 * 1. Service Discovery with active health checks (kislayphp_discovery)
 * 2. API Gateway with automatic X-Forwarded-* headers (kislayphp_gateway)
 * 3. HTTP Server with non-blocking I/O (kislayphp_extension)
 * 4. Async client with automatic retries and Tracing propagation (kislayphp_extension)
 */

$registry = new Kislay\Discovery\ServiceRegistry();
echo "[Registry] Initialized with active health-check thread.\n";

$serviceB = new Kislay\Core\App();
$serviceBPort = 9001;

$serviceB->get('/health', function($req, $res) {
    $res->status(200)->send("OK");
});

$serviceB->get('/users/echo', function($req, $res) {
    $cid = $req->header('x-correlation-id');
    echo "[Service B] Handled request. CID: $cid\n";
    $res->json([
        'status' => 'success',
        'message' => 'Hello from backend!',
        'tracing' => [
            'correlation_id' => $cid,
            'forwarded_for'  => $req->header('x-forwarded-for'),
            'forwarded_proto'=> $req->header('x-forwarded-proto')
        ]
    ]);
});

$serviceB->listenAsync('127.0.0.1', $serviceBPort);
echo "[Service B] Running on port $serviceBPort\n";

$registry->register(
    'user-service',
    "http://127.0.0.1:$serviceBPort",
    ['env' => 'production'],
    'user-svc-instance-1',
    '/health'
);
echo "[Registry] Registered 'user-service' with active health check at /health\n";

$gateway = new Kislay\Gateway\Gateway();
$gatewayPort = 9008;
$gateway->addServiceRoute('GET', '/users/*', 'user-service');
$gateway->setResolver(function($service) use ($registry) {
    return $registry->resolve($service);
});

$gateway->listen('127.0.0.1', $gatewayPort);
echo "[Gateway] Listening on port $gatewayPort (Proxying /users/* -> user-service)\n";

echo "[Client] Preparing request to Gateway...\n";
$http = new Kislay\Core\AsyncHttp();
$http->get("http://127.0.0.1:$gatewayPort/users/echo");
$http->retry(3, 500);

$http->executeAsync()->then(function($res) use ($http) {
    $data = json_decode($http->getResponse(), true);
    echo "\n========================================\n";
    echo " CLIENT SUCCESS\n";
    echo "----------------------------------------\n";
    echo "Status Code:    " . $http->getResponseCode() . "\n";
    echo "Correlation ID: " . $data['tracing']['correlation_id'] . " (Propagated!)\n";
    echo "Gateway Proxy:  " . $data['tracing']['forwarded_for'] . " (Identified!)\n";
    echo "Response Msg:   " . $data['message'] . "\n";
    echo "========================================\n\n";
})->catch(function($err) {
    echo "[Client] Error: $err\n";
});

$start = microtime(true);
while (microtime(true) - $start < 3.0) {
    usleep(100000);
}

echo "[System] Shutting down...\n";
$serviceB->stop();
$gateway->stop();
echo "[System] Done.\n";