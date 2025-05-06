<?php

namespace Tests\Fixtures\Downloader;

use RuntimeException;

class CssReferentialScraper
{
    private const MDN_URL = 'https://unpkg.com/@mdn/browser-compat-data/data.json';
    private const W3C_URL = 'https://www.w3.org/Style/CSS/all-properties.en.json';

    private $downloaders = [];
    private bool $forceRefresh;

    public function __construct(bool $forceRefresh = false)
    {
        $this->downloaders = [
            self::MDN_URL => new CachedHttpDownloader('css_referential_mdn'),
            self::W3C_URL => new CachedHttpDownloader('css_referential_w3c'),
        ];
        $this->forceRefresh = $forceRefresh;
    }

    public function fetchReferentials(): array
    {
        $w3cReferencial = $this->fetchW3CReferential();
        $mdnReferencial = $this->fetchMdnReferential();
        $properties = $w3cReferencial['properties'] ?? [];

        foreach ($mdnReferencial['properties'] as $property => $info) {
            if (!isset($properties[$property])) {
                $properties[$property] = $info;
                continue;
            }
            if (!$properties[$property]['standard'] && $info['standard']) {
                $properties[$property] = $info;
            }
        }

        return [
            'properties' => $properties,
        ];
    }

    public function saveToJson(array $data, string $filePath): void
    {
        file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function fetchMdnReferential(): array
    {
        $json = $this->downloaders[self::MDN_URL]->fetch(self::MDN_URL, $this->forceRefresh);
        if ($json === false) {
            throw new RuntimeException('Failed to fetch CSS properties from MDN.');
        }

        $data = json_decode($json, true);

        $properties = [];
        foreach ($data['css']['properties'] as $property => $info) {
            $isStandard = $info['__compat']['status']['standard_track'] ?? false;
            $properties[$property] = [
                'standard' => $isStandard,
            ];
        }

        return [
            'properties'  => $properties,
        ];
    }

    private function fetchW3CReferential(): array
    {
        $json = $this->downloaders[self::W3C_URL]->fetch(self::W3C_URL, $this->forceRefresh);
        if ($json === false) {
            throw new RuntimeException('Failed to fetch CSS properties from W3C.');
        }

        $standardStatuses = [
            'REC', // Recommendation
            'NOTE', // Working Group Note
        ];

        $data = json_decode($json, true);

        $properties = [];
        foreach ($data as $property) {
            $propertyName = $property['property'] ?? null;
            $isStandard = in_array($property['status'], $standardStatuses, true);
            $properties[$propertyName] = [
                'standard' => $isStandard,
            ];
        }

        return [
            'properties'  => $properties,
        ];
    }
}
