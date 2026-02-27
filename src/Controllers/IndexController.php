<?php

namespace App\Controllers;

use App\Services\Database;

class IndexController {
    public function welcome($req, $res) {
        $res->json([
            'message' => 'Welcome to Kislay Core Production Example',
            'version' => '1.0.0',
            'database' => 'SQLite',
            'tracing' => [
                'id' => $req->header('x-correlation-id')
            ]
        ]);
    }

    public function health($req, $res) {
        $res->status(200)->send('UP');
    }

    public function getUsers($req, $res) {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT * FROM users ORDER BY created_at DESC");
        $users = $stmt->fetchAll();
        
        $res->json([
            'status' => 'success',
            'count' => count($users),
            'data' => $users
        ]);
    }

    public function createUser($req, $res) {
        $db = Database::getConnection();
        $name = "User " . rand(100, 999);
        $email = "user" . rand(1000, 9999) . "@example.com";
        
        $stmt = $db->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
        $stmt->execute([$name, $email]);
        
        $res->status(201)->json([
            'status' => 'created',
            'id' => $db->lastInsertId(),
            'user' => ['name' => $name, 'email' => $email]
        ]);
    }
}