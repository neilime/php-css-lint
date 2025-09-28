<?php

namespace Tests\Fixtures\Downloader;

use RuntimeException;

class CssReferentialScraper
{
    private const MDN_URL = 'https://unpkg.com/@mdn/browser-compat-data/data.json';
    private const W3C_URL = 'https://www.w3.org/Style/CSS/all-properties.en.json';

    /**
     * @var array<string, CachedHttpDownloader>
     */
    private $downloaders = [];
    private bool $forceRefresh;

    public function __construct(bool $forceRefresh = false)
    {
        $this->downloaders = [
            self::MDN_URL => new CachedHttpDownloader('css_referential_mdn', __DIR__ . '/../../../.cache', 1000, 3),
            self::W3C_URL => new CachedHttpDownloader('css_referential_w3c', __DIR__ . '/../../../.cache', 1000, 3),
        ];
        $this->forceRefresh = $forceRefresh;
    }

    public function fetchReferentials(): array
    {
        $w3cReferencial = $this->fetchW3CReferential();
        
        // Add delay between different API calls to be respectful to servers
        usleep(2000000); // 2 seconds
        
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

        $atRules = $w3cReferencial['at-rules'] ?? [];
        foreach ($mdnReferencial['at-rules'] as $atRule => $info) {
            if (!isset($atRules[$atRule])) {
                $atRules[$atRule] = $info;
                continue;
            }
            if (!$atRules[$atRule]['standard'] && $info['standard']) {
                $atRules[$atRule] = $info;
            }
        }

        $atRulesProperties = $w3cReferencial['at-rules-properties'] ?? [];
        foreach ($mdnReferencial['at-rules-properties'] as $property => $info) {
            if (!isset($atRulesProperties[$property])) {
                $atRulesProperties[$property] = $info;
                continue;
            }
            if (!$atRulesProperties[$property]['standard'] && $info['standard']) {
                $atRulesProperties[$property] = $info;
            }
        }

        return [
            'properties' => $properties,
            'at-rules' => $atRules,
            'at-rules-properties' => $atRulesProperties,
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

        function isStandard(array $info): bool
        {
            return $info['__compat']['status']['standard_track'] ?? false;
        };

        $properties = [];
        foreach ($data['css']['properties'] as $property => $info) {
            $properties[$property] = [
                'standard' => isStandard($info),
            ];
        }

        $atRules = [];
        $atRulesProperties = [];
        foreach ($data['css']['at-rules'] as $atRule => $info) {
            $atRules[$atRule] = [
                'standard' => isStandard($info),
            ];

            $atRulesProperties[$atRule] = [];

            foreach ($info as $property => $propertyInfo) {
                // Get kebab case properties only
                if (preg_match('/^[a-z0-9-]+$/', $property) && !isset($atRulesProperties[$property])) {
                    $atRulesProperties[$atRule][$property] = [
                        'standard' => isStandard($propertyInfo),
                    ];
                }
            }
        }

        return [
            'properties'  => $properties,
            'at-rules'    => $atRules,
            'at-rules-properties' => $atRulesProperties,
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
