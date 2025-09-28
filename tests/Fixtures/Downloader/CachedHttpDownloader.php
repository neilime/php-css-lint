<?php

namespace Tests\Fixtures\Downloader;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class CachedHttpDownloader
{
    private Client $client;
    private FilesystemAdapter $cache;
    private int $requestDelayMs;
    private int $maxRetries;

    public function __construct(string $namespace, string $cachePath = __DIR__ . '/../../../.cache', int $requestDelayMs = 1000, int $maxRetries = 3)
    {
        $this->client = new Client([
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (compatible; FixtureDownloader/1.0)',
            ],
        ]);

        $this->cache = new FilesystemAdapter($namespace, 0, $cachePath);
        $this->requestDelayMs = $requestDelayMs;
        $this->maxRetries = $maxRetries;
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

        $response = $this->fetchWithRetry($url, ['headers' => $headers]);

        if ($response->getStatusCode() === 304) {
            $cachedData = $cachedItem->get();
            return $cachedData['body'];
        }

        $body = (string) $response->getBody();

        $cachedItem->set([
            'etag' => $response->getHeaderLine('ETag'),
            'last_modified' => $response->getHeaderLine('Last-Modified'),
            'body' => $body,
        ]);
        $this->cache->save($cachedItem);

        return $body;
    }

    private function fetchWithRetry(string $url, array $options = []): \Psr\Http\Message\ResponseInterface
    {
        $attempt = 0;
        $maxRetries = $this->maxRetries;

        while ($attempt <= $maxRetries) {
            try {
                // Add a delay before each request (except the first one)
                if ($attempt > 0) {
                    $delayMs = $this->requestDelayMs * (2 ** ($attempt - 1)); // Exponential backoff
                    usleep($delayMs * 1000); // Convert to microseconds
                } elseif ($this->requestDelayMs > 0) {
                    usleep($this->requestDelayMs * 1000); // Basic rate limiting
                }

                $response = $this->client->get($url, $options);
                return $response;
                
            } catch (ClientException $e) {
                // Check if it's a 429 (Too Many Requests) or other retryable client error
                if ($e->getResponse() && in_array($e->getResponse()->getStatusCode(), [429, 503, 502, 504])) {
                    if ($attempt < $maxRetries) {
                        $attempt++;
                        continue;
                    }
                }
                throw $e;
            } catch (RequestException $e) {
                // Handle network/connection errors
                if ($attempt < $maxRetries) {
                    $attempt++;
                    continue;
                }
                throw $e;
            }
        }
        
        throw new \RuntimeException("Max retries ({$maxRetries}) exceeded for URL: {$url}");
    }
}
