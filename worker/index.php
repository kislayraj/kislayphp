<?php
require __DIR__ . '/../shared/common.php';

require_extensions([
    'kislayphp_queue',
]);

$queue = new KislayPHP\Queue\Queue();
$queue->enqueue('events', ['type' => 'worker.start', 'at' => time()]);

$iterations = env_int('KISLAY_WORKER_ITERATIONS', 5);
for ($i = 0; $i < $iterations; $i++) {
    $job = $queue->dequeue('events');
    if ($job !== null) {
        echo "processed: " . json_encode($job) . "\n";
    }
    usleep(200000);
}
