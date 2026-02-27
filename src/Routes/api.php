<?php

use App\Controllers\IndexController;

return function($app) {
    $controller = new IndexController();

    $app->get('/', [$controller, 'welcome']);
    $app->get('/health', [$controller, 'health']);

    // Example: Scoped routes
    $app->group('/api/v1', function($group) use ($controller) {
        $group->get('/status', [$controller, 'health']);
    });
};