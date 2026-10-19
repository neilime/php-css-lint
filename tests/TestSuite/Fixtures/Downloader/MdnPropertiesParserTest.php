<?php

namespace Tests\TestSuite\Fixtures\Downloader;

use PHPUnit\Framework\TestCase;
use Tests\Fixtures\Downloader\MdnPropertiesParser;

class MdnPropertiesParserTest extends TestCase
{
    /**
     * @dataProvider propertySupportProvider
     */
    public function testParsesSupportedPropertyAliases(array $support, bool $aliasExpected): void
    {
        $parser = new MdnPropertiesParser();
        $properties = [
            'canonical-property' => [
                '__compat' => [
                    'status' => ['standard_track' => true],
                    'support' => $support,
                ],
            ],
        ];

        $actual = $parser->parse($properties);

        $expected = ['canonical-property' => ['standard' => true]];
        if ($aliasExpected) {
            $expected['alternate-property'] = ['standard' => false];
        }
        $this->assertSame($expected, $actual);
    }

    public function propertySupportProvider(): array
    {
        $supportedAlias = ['alternative_name' => 'alternate-property', 'version_added' => '1'];

        return [
            'single support statement' => [['chrome' => $supportedAlias], true],
            'multiple support statements' => [
                ['chrome' => [['version_added' => '2'], $supportedAlias]],
                true,
            ],
            'duplicate aliases across browsers' => [
                ['chrome' => $supportedAlias, 'firefox' => $supportedAlias],
                true,
            ],
            'mirrored support' => [
                ['chrome' => $supportedAlias, 'chrome_android' => 'mirror'],
                true,
            ],
            'unsupported alias' => [
                ['chrome' => ['alternative_name' => 'alternate-property', 'version_added' => false]],
                false,
            ],
            'removed alias' => [
                ['chrome' => $supportedAlias + ['version_removed' => '2']],
                false,
            ],
            'alias still supported in another browser' => [
                ['chrome' => $supportedAlias + ['version_removed' => '2'], 'firefox' => $supportedAlias],
                true,
            ],
            'canonical name without alias' => [['chrome' => ['version_added' => '1']], false],
            'prefix without alias' => [['chrome' => ['prefix' => '-webkit-', 'version_added' => '1']], false],
        ];
    }

    public function testPreservesCanonicalClassificationsRegardlessOfPropertyOrder(): void
    {
        $parser = new MdnPropertiesParser();
        $properties = [
            'canonical-property' => [
                '__compat' => [
                    'status' => ['standard_track' => false],
                    'support' => [
                        'chrome' => ['alternative_name' => 'alternate-property', 'version_added' => '1'],
                    ],
                ],
            ],
            'alternate-property' => [
                '__compat' => [
                    'status' => ['standard_track' => true],
                    'support' => ['chrome' => ['version_added' => '1']],
                ],
            ],
        ];

        $expected = [
            'canonical-property' => ['standard' => false],
            'alternate-property' => ['standard' => true],
        ];
        $this->assertEquals($expected, $parser->parse($properties));
        $this->assertEquals($expected, $parser->parse(array_reverse($properties, true)));
    }

    public function testKeepsVendorAliasesNonStandardAndIgnoresValueAliases(): void
    {
        $parser = new MdnPropertiesParser();
        $properties = [
            'canonical-property' => [
                '__compat' => [
                    'status' => ['standard_track' => true],
                    'support' => [
                        'chrome' => ['alternative_name' => '-webkit-alternate-property', 'version_added' => '1'],
                    ],
                ],
                'some_value' => [
                    '__compat' => [
                        'support' => [
                            'chrome' => ['alternative_name' => 'alternate-value', 'version_added' => '1'],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertSame([
            'canonical-property' => ['standard' => true],
            '-webkit-alternate-property' => ['standard' => false],
        ], $parser->parse($properties));
    }
}
