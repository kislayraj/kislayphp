<?php

namespace App\Controllers;

use App\Models\User;
use PDOException;

class IndexController {
    private function queryValue($req, string $key): mixed
    {
        if (is_object($req)) {
            if (method_exists($req, 'getQueryParams')) {
                $params = $req->getQueryParams();
                if (is_array($params) && array_key_exists($key, $params)) {
                    return $params[$key];
                }
            }

            if (method_exists($req, 'uri')) {
                $uri = $req->uri();
                if (is_string($uri) && $uri !== '') {
                    $query = parse_url($uri, PHP_URL_QUERY);
                    if (is_string($query) && $query !== '') {
                        $parsed = [];
                        parse_str($query, $parsed);
                        if (array_key_exists($key, $parsed)) {
                            return $parsed[$key];
                        }
                    }
                }
            }
        }

        return $_GET[$key] ?? null;
    }

    private function queryInt($req, string $key, int $default, int $min, int $max): int
    {
        $raw = $this->queryValue($req, $key);
        if ($raw === null || is_array($raw)) {
            return $default;
        }

        $value = filter_var($raw, FILTER_VALIDATE_INT);
        if ($value === false) {
            return $default;
        }

        $value = (int) $value;
        if ($value < $min) {
            $value = $min;
        }
        if ($value > $max) {
            $value = $max;
        }

        return $value;
    }

    public function welcome($req, $res) {
        $res->json([
            'message' => 'Welcome to Kislay Core Production Example',
            'version' => '0.0.1',
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
        $page = $this->queryInt($req, 'page', 1, 1, 1000000);
        $limit = $this->queryInt($req, 'limit', 20, 1, 100);
        $pageData = User::paginate($limit, $page);

        $res->json([
            'status' => 'success',
            'page' => $pageData['current_page'],
            'limit' => $pageData['per_page'],
            'count' => count($pageData['data']),
            'total' => $pageData['total'],
            'total_pages' => $pageData['last_page'],
            'has_next' => $pageData['has_more_pages'],
            'data' => $pageData['data']
        ]);
    }

    public function createUser($req, $res) {
        $payload = [];
        if (is_object($req) && method_exists($req, 'getJson')) {
            $parsed = $req->getJson();
            if (is_array($parsed)) {
                $payload = $parsed;
            }
        }

        $name = isset($payload['name']) && is_string($payload['name']) && trim($payload['name']) !== ''
            ? trim($payload['name'])
            : 'User ' . random_int(1000, 9999);

        $providedEmail = null;
        if (isset($payload['email']) && is_string($payload['email']) && trim($payload['email']) !== '') {
            $providedEmail = strtolower(trim($payload['email']));
            if (!filter_var($providedEmail, FILTER_VALIDATE_EMAIL)) {
                $res->json([
                    'status' => 'error',
                    'error' => 'invalid_email'
                ], 422);
                return;
            }
        }

        $email = $providedEmail;
        if ($email === null) {
            try {
                $email = 'user_' . bin2hex(random_bytes(8)) . '@example.com';
            } catch (\Throwable) {
                $email = 'user_' . uniqid('', true) . '@example.com';
            }
        }

        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
            ]);
            $data = $user->toArray();

            $res->json([
                'status' => 'created',
                'id' => $data['id'] ?? null,
                'user' => [
                    'name' => $data['name'] ?? $name,
                    'email' => $data['email'] ?? $email,
                ]
            ], 201);
            return;
        } catch (PDOException $e) {
            if (!User::isUniqueViolation($e)) {
                throw $e;
            }
            $res->json([
                'status' => 'error',
                'error' => 'email_exists'
            ], 409);
            return;
        }
    }
}
