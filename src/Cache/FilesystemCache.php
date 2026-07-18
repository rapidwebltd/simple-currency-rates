<?php

namespace RapidWeb\SimpleCurrencyRates\Cache;

use RuntimeException;

class FilesystemCache
{
    private $directory;

    public function __construct($directory = null)
    {
        $this->directory = rtrim($directory ?: sys_get_temp_dir().'/simple-currency-rates-cache', '/\\');
    }

    public function get($key, $default = null)
    {
        $payload = $this->read($key);

        return $payload === null ? $default : $payload['value'];
    }

    public function has($key)
    {
        return $this->read($key) !== null;
    }

    public function set($key, $value, $ttl = null)
    {
        $this->ensureDirectoryExists();

        $payload = json_encode([
            'expires_at' => $ttl === null ? null : time() + (int) $ttl,
            'value' => $value,
        ]);
        $temporaryPath = tempnam($this->directory, 'rates-');

        if ($payload === false || $temporaryPath === false || file_put_contents($temporaryPath, $payload, LOCK_EX) === false) {
            throw new RuntimeException('Unable to write the currency rate cache.');
        }

        if (!rename($temporaryPath, $this->path($key))) {
            @unlink($temporaryPath);
            throw new RuntimeException('Unable to replace the currency rate cache.');
        }

        return true;
    }

    private function read($key)
    {
        $path = $this->path($key);

        if (!is_file($path)) {
            return null;
        }

        $payload = json_decode((string) file_get_contents($path), true);

        if (!is_array($payload) || !array_key_exists('expires_at', $payload) || !array_key_exists('value', $payload)) {
            return null;
        }

        if ($payload['expires_at'] !== null && $payload['expires_at'] <= time()) {
            @unlink($path);

            return null;
        }

        return $payload;
    }

    private function ensureDirectoryExists()
    {
        if (!is_dir($this->directory) && !@mkdir($this->directory, 0777, true) && !is_dir($this->directory)) {
            throw new RuntimeException('Unable to create the currency rate cache directory.');
        }
    }

    private function path($key)
    {
        return $this->directory.'/'.sha1($key).'.cache';
    }
}
