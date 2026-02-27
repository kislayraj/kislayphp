<?php

namespace App\Controllers;

class IndexController {
    public function welcome($req, $res) {
        $res->json([
            'message' => 'Welcome to Kislay Core Production Example',
            'version' => '1.0.0',
            'tracing' => [
                'id' => $req->header('x-correlation-id')
            ]
        ]);
    }

    public function health($req, $res) {
        $res->status(200)->send('UP');
    }
}