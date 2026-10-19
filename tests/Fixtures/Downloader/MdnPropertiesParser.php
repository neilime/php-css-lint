<?php

namespace Tests\Fixtures\Downloader;

class MdnPropertiesParser
{
    public function parse(array $properties): array
    {
        $referential = [];
        foreach ($properties as $property => $info) {
            $referential[$property] = [
                'standard' => $info['__compat']['status']['standard_track'] ?? false,
            ];
        }

        foreach ($properties as $info) {
            foreach ($info['__compat']['support'] ?? [] as $support) {
                if (!is_array($support)) {
                    continue;
                }

                $statements = array_is_list($support) ? $support : [$support];
                foreach ($statements as $statement) {
                    $alias = $statement['alternative_name'] ?? null;
                    if (!is_string($alias) || $alias === '' || empty($statement['version_added']) || !empty($statement['version_removed'])) {
                        continue;
                    }

                    // The canonical property's standards status does not describe its aliases.
                    $referential[$alias] ??= ['standard' => false];
                }
            }
        }

        return $referential;
    }
}
