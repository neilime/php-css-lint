<?php

namespace Tests\Fixtures\Downloader;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use RuntimeException;
use Throwable;

class CssFileDownloader
{
    private CachedHttpDownloader $downloader;
    private FilesystemAdapter $resolveCache;

    public function __construct()
    {
        $this->downloader = new CachedHttpDownloader('css_files');
        $this->resolveCache = new FilesystemAdapter('css_versions', 3600, __DIR__ . '/../../../.cache');
    }

    public function downloadLatestFiles(string $targetDir): void
    {
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0o777, true);
        }

        $files = [
            'bootstrap.css' => $this->resolveWithCache('bootstrap', fn() => $this->resolveLatestBootstrapUrl()),
            'tailwind.css' => $this->resolveWithCache('tailwind', fn() => $this->resolveLatestTailwindUrl()),
            'normalize.css' => $this->resolveWithCache('normalize', fn() => $this->resolveLatestNormalizeUrl()),
        ];

        foreach ($files as $filename => $url) {
            echo "Downloading {$filename} from {$url}...\n";
            try {
                $content = $this->downloader->fetch($url);
                file_put_contents($targetDir . '/' . $filename, $content);
                echo "Saved {$filename}\n";
            } catch (Throwable $e) {
                echo "[ERROR] Failed to download {$filename}: " . $e->getMessage() . "\n";
            }
        }
    }

    private function resolveWithCache(string $key, callable $resolver): string
    {
        $cacheItem = $this->resolveCache->getItem($key);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $url = $resolver();

        $cacheItem->set($url);
        $this->resolveCache->save($cacheItem);

        return $url;
    }

    private function resolveLatestBootstrapUrl(): string
    {
        $meta = $this->fetchJson('https://data.jsdelivr.com/v1/package/npm/bootstrap');
        $version = $meta['tags']['latest'] ?? 'latest';
        return "https://cdn.jsdelivr.net/npm/bootstrap@{$version}/dist/css/bootstrap.min.css";
    }

    private function resolveLatestTailwindUrl(): string
    {
        $meta = $this->fetchJson('https://data.jsdelivr.com/v1/package/npm/tailwindcss');
        $version = $meta['tags']['latest'] ?? 'latest';
        return "https://cdn.jsdelivr.net/npm/tailwindcss@{$version}/index.min.css";
    }

    private function resolveLatestNormalizeUrl(): string
    {
        $meta = $this->fetchJson('https://data.jsdelivr.com/v1/package/npm/normalize.css');
        $version = $meta['version'] ?? 'latest';
        return "https://cdn.jsdelivr.net/npm/normalize.css@{$version}/normalize.css";
    }

    private function fetchJson(string $url): array
    {
        $json = @file_get_contents($url);
        if ($json === false) {
            throw new RuntimeException("Unable to fetch {$url}");
        }

        $data = json_decode($json, true);
        if ($data === null) {
            throw new RuntimeException("Invalid JSON from {$url}");
        }

        return $data;
    }
}
