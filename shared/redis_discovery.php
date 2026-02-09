<?php
class RedisDiscoveryClient implements KislayPHP\Discovery\ClientInterface {
    private $redis;
    private $key;

    public function __construct() {
        if (!class_exists('Redis')) {
            fwrite(STDERR, "Missing PHP Redis extension (redis)\n");
            exit(1);
        }

        $host = env_string('KISLAY_REDIS_HOST', '127.0.0.1');
        $port = env_int('KISLAY_REDIS_PORT', 6379);
        $db = env_int('KISLAY_REDIS_DB', 0);
        $password = env_string('KISLAY_REDIS_PASSWORD', '');
        $prefix = env_string('KISLAY_REDIS_PREFIX', 'kislay:discovery');

        $this->redis = new Redis();
        $this->redis->connect($host, $port, 2.0);
        if ($password !== '') {
            $this->redis->auth($password);
        }
        if ($db > 0) {
            $this->redis->select($db);
        }
        $this->key = $prefix;
    }

    public function register($name, $url) {
        $this->redis->hSet($this->key, $name, $url);
        return true;
    }

    public function deregister($name) {
        $this->redis->hDel($this->key, $name);
        return true;
    }

    public function resolve($name) {
        $value = $this->redis->hGet($this->key, $name);
        if ($value === false || $value === null || $value === '') {
            return null;
        }
        return $value;
    }

    public function list() {
        $all = $this->redis->hGetAll($this->key);
        if (!is_array($all)) {
            return [];
        }
        return $all;
    }
}
