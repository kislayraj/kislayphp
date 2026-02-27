<?php

namespace App\Middleware;

class RequestLogger {
    public function __invoke($req, $res, $next) {
        $start = microtime(true);
        $next();
        $duration = (microtime(true) - $start) * 1000;

        error_log(sprintf(
            "[%s] %s %s - %dms",
            date('Y-m-d H:i:s'),
            $req->getMethod(),
            $req->getUri(),
            $duration
        ));
    }
}