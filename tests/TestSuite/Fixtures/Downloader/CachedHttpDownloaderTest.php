<?php

namespace Tests\TestSuite\Fixtures\Downloader;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Tests\Fixtures\Downloader\CachedHttpDownloader;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;

class CachedHttpDownloaderTest extends TestCase
{
    private string $tempCacheDir;

    protected function setUp(): void
    {
        $this->tempCacheDir = sys_get_temp_dir() . '/test_cache_' . uniqid();
        mkdir($this->tempCacheDir, 0o777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempCacheDir);
    }

    public function testRetryOn429Error(): void
    {
        // Mock handler that returns 429 twice, then succeeds
        $mock = new MockHandler([
            new ClientException('Too Many Requests', new Request('GET', 'test'), new Response(429)),
            new ClientException('Too Many Requests', new Request('GET', 'test'), new Response(429)),
            new Response(200, [], 'success content'),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $downloader = new CachedHttpDownloader('test', $this->tempCacheDir, 10, 3); // 10ms delay, 3 max retries

        // Use reflection to replace the client
        $reflection = new ReflectionClass($downloader);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($downloader, $client);

        $result = $downloader->fetch('http://test.com', true);

        $this->assertEquals('success content', $result);
    }

    public function testRetryFailsAfterMaxAttempts(): void
    {
        // Mock handler that always returns 429
        $mock = new MockHandler([
            new ClientException('Too Many Requests', new Request('GET', 'test'), new Response(429)),
            new ClientException('Too Many Requests', new Request('GET', 'test'), new Response(429)),
            new ClientException('Too Many Requests', new Request('GET', 'test'), new Response(429)),
            new ClientException('Too Many Requests', new Request('GET', 'test'), new Response(429)),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $downloader = new CachedHttpDownloader('test', $this->tempCacheDir, 10, 3); // 10ms delay, 3 max retries

        // Use reflection to replace the client
        $reflection = new ReflectionClass($downloader);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($downloader, $client);

        $this->expectException(ClientException::class);
        $downloader->fetch('http://test.com', true);
    }

    public function testSuccessfulRequest(): void
    {
        // Mock handler that returns success immediately
        $mock = new MockHandler([
            new Response(200, [], 'success content'),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $downloader = new CachedHttpDownloader('test', $this->tempCacheDir, 10, 3);

        // Use reflection to replace the client
        $reflection = new ReflectionClass($downloader);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($downloader, $client);

        $result = $downloader->fetch('http://test.com', true);

        $this->assertEquals('success content', $result);
    }

    public function testCachedResponse(): void
    {
        // First call
        $mock1 = new MockHandler([
            new Response(200, ['ETag' => '"test-etag"'], 'cached content'),
        ]);
        $handlerStack1 = HandlerStack::create($mock1);
        $client1 = new Client(['handler' => $handlerStack1]);

        $downloader = new CachedHttpDownloader('test', $this->tempCacheDir, 10, 3);

        $reflection = new ReflectionClass($downloader);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($downloader, $client1);

        $result1 = $downloader->fetch('http://test.com', true);
        $this->assertEquals('cached content', $result1);

        // Second call should use cache and return 304
        $mock2 = new MockHandler([
            new Response(304),
        ]);
        $handlerStack2 = HandlerStack::create($mock2);
        $client2 = new Client(['handler' => $handlerStack2]);
        $clientProperty->setValue($downloader, $client2);

        $result2 = $downloader->fetch('http://test.com', false);
        $this->assertEquals('cached content', $result2);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }

        rmdir($dir);
    }
}
