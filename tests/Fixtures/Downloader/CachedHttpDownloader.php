<?php

namespace Tests\Fixtures\Downloader;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class CachedHttpDownloader
{
    private Client $client;
    private FilesystemAdapter $cache;

    public function __construct(string $namespace, string $cachePath = __DIR__ . '/../../.cache')
    {
        $this->client = new Client([
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (compatible; FixtureDownloader/1.0)',
            ],
        ]);

        $this->cache = new FilesystemAdapter($namespace, 0, $cachePath);
    }

    public function fetch(string $url, bool $forceRefresh = false): string
    {
        $cacheKey = md5($url);
        $cachedItem = $this->cache->getItem($cacheKey);
        $headers = [];

        if ($cachedItem->isHit() && !$forceRefresh) {
            $cachedData = $cachedItem->get();
            if (isset($cachedData['etag'])) {
                $headers['If-None-Match'] = $cachedData['etag'];
            }
            if (isset($cachedData['last_modified'])) {
                $headers['If-Modified-Since'] = $cachedData['last_modified'];
            }
        }

        try {
            $response = $this->client->get($url, ['headers' => $headers]);
            $body = (string) $response->getBody();

            $cachedItem->set([
                'etag' => $response->getHeaderLine('ETag'),
                'last_modified' => $response->getHeaderLine('Last-Modified'),
                'body' => $body,
            ]);
            $this->cache->save($cachedItem);

            return $body;
        } catch (RequestException $e) {
            if ($e->getResponse() && $e->getResponse()->getStatusCode() === 304) {
                $cachedData = $cachedItem->get();
                return $cachedData['body'];
            }

            throw $e;
        }
    }
}
