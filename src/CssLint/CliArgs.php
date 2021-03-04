<?php

namespace CssLint;

class CliArgs
{
    public ?string $filePathOrCssString = null;
    public ?string $options = null;

    /**
     * Constructor
     * @param array $aArguments arguments to be parsed (@see $_SERVER['argv'])
     *              Accepts "-o", "--options" '{}'
     *              Accepts a string as last argument, a file path or a string containing CSS
     */
    public function __construct(array $aArguments)
    {
        if (empty($aArguments) || count($aArguments) === 1) {
            return;
        }

        array_shift($aArguments);

        $this->filePathOrCssString = array_pop($aArguments);

        if ($aArguments) {
            $aParsedArguments = $this->extractArguments($aArguments);

            if (!empty($aParsedArguments['options'])) {
                $this->options = $aParsedArguments['options'];
            }
        }
    }

    /**
     * @param array $aArguments array of arguments to be parsed (@see $_SERVER['argv'])
     * @return array an associative array of key=>value arguments
     */
    private function extractArguments(array $aArguments): array
    {
        $aParsedArguments = [];

        foreach ($aArguments as $sArgument) {
            // --foo --bar=baz
            if (substr($sArgument, 0, 2) == '--') {
                $sEqualPosition = strpos($sArgument, '=');

                // --bar=baz
                if ($sEqualPosition !== false) {
                    $sKey = substr($sArgument, 2, $sEqualPosition - 2);
                    $sValue = substr($sArgument, $sEqualPosition + 1);
                    $aParsedArguments[$sKey] = $sValue;
                }
            }
        }
        return $aParsedArguments;
    }
}
