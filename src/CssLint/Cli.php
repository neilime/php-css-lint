<?php

namespace CssLint;

class Cli
{
    private static $SCRIPT_NAME = 'php-css-lint';
    private static $RETURN_CODE_ERROR = 1;
    private static $RETURN_CODE_SUCCESS = 0;

    /**
     * Entrypoint of the cli, will execute the linter according to the given arguments
     * @param array $aArguments arguments to be parsed (@see $_SERVER['argv'])
     * @return int the return code related to the execution of the linter
     **/
    public function run(array $aArguments): int
    {
        $oCliArgs = $this->parseArguments($aArguments);
        if (!$oCliArgs->filePathOrCssString) {
            $this->printUsage();
            return self::$RETURN_CODE_SUCCESS;
        }

        $oProperties = new \CssLint\Properties();
        if ($oCliArgs->options) {
            $aOptions = json_decode($oCliArgs->options, true);

            if (json_last_error()) {
                $sErrorMessage = json_last_error_msg();
                $this->printError('Unable to parse option argument: ' . $sErrorMessage);
                return self::$RETURN_CODE_ERROR;
            }

            if (!$aOptions) {
                $this->printError('Unable to parse empty option argument');
                return self::$RETURN_CODE_ERROR;
            }
            $oProperties->setOptions($aOptions);
        }

        $oCssLinter = new \CssLint\Linter($oProperties);

        $sFilePathOrCssString = $oCliArgs->filePathOrCssString;
        if (!file_exists($sFilePathOrCssString)) {
            return $this->lintString($oCssLinter, $sFilePathOrCssString);
        }

        $sFilePath = $sFilePathOrCssString;
        if (!is_readable($sFilePath)) {
            $this->printError('File "' . $sFilePath . '" is not readable');
            return self::$RETURN_CODE_ERROR;
        }

        return $this->lintFile($oCssLinter, $sFilePath);
    }

    /**
     * Retrieve the parsed Cli arguments from given arguments array
     * @return \CssLint\CliArgs an instance of Cli arguments object containing parsed arguments
     */
    private function parseArguments(array $aArguments): \CssLint\CliArgs
    {
        return new \CssLint\CliArgs($aArguments);
    }

    /**
     * Display usage of the cli
     */
    private function printUsage()
    {
        $this->printLine('Usage:' . PHP_EOL .
            '------' . PHP_EOL .
            PHP_EOL .
            '  ' . self::$SCRIPT_NAME . ' [--options=\'{ }\'] css_file_or_string_to_lint' . PHP_EOL .
            PHP_EOL .
            'Arguments:' . PHP_EOL .
            '----------' . PHP_EOL .
            PHP_EOL .
            '  --options' . PHP_EOL .
            '    Options (optional), must be a json object:' . PHP_EOL .
            '     * "allowedIndentationChars" => [" "] or ["\t"]: will override the current property' . PHP_EOL .
            '     * "constructors": { "property" => bool }: will merge with the current property' . PHP_EOL .
            '     * "standards": { "property" => bool }: will merge with the current property' . PHP_EOL .
            '     * "nonStandards": { "property" => bool }: will merge with the current property' . PHP_EOL .
            '    Example: --options=\'{ "constructors": {"o" : false}, "allowedIndentationChars": ["\t"] }\'' .
            PHP_EOL .
            PHP_EOL .
            '  css_file_or_string_to_lint' . PHP_EOL .
            '    The CSS file path (absolute or relative) or a CSS string to be linted' . PHP_EOL .
            '    Example:' . PHP_EOL .
            '      ./path/to/css_file_path_to_lint.css' . PHP_EOL .
            '      ".test { color: red; }"' . PHP_EOL .
            PHP_EOL .
            'Examples:' . PHP_EOL .
            '---------' . PHP_EOL .
            PHP_EOL .
            '  Lint a CSS file:' . PHP_EOL .
            '    ' . self::$SCRIPT_NAME . ' ./path/to/css_file_path_to_lint.css' . PHP_EOL . PHP_EOL .
            '  Lint a CSS string:' . PHP_EOL .
            '    ' . self::$SCRIPT_NAME . ' ".test { color: red; }"' . PHP_EOL .  PHP_EOL .
            '  Lint with only tabulation as indentation:' . PHP_EOL .
            '    ' . self::$SCRIPT_NAME .
            ' --options=\'{ "allowedIndentationChars": ["\t"] }\' ".test { color: red; }"' . PHP_EOL .
            PHP_EOL . PHP_EOL);
    }

    /**
     * Performs lint on a given file path
     * @param \CssLint\Linter $oCssLinter the instance of the linter
     * @param string $sFilePath the path of the file to be linted
     * @return int the return code related to the execution of the linter
     */
    private function lintFile(\CssLint\Linter $oCssLinter, string $sFilePath): int
    {
        $this->printLine('# Lint CSS file "' . $sFilePath . '"...');

        if ($oCssLinter->lintFile($sFilePath)) {
            $this->printLine("\033[32m => CSS file \"" . $sFilePath . "\" is valid\033[0m" . PHP_EOL);
            return self::$RETURN_CODE_SUCCESS;
        }

        $this->printLine("\033[31m => CSS file \"" . $sFilePath . "\" is not valid:\033[0m" . PHP_EOL);
        $this->displayLinterErrors($oCssLinter->getErrors());
        return self::$RETURN_CODE_ERROR;
    }


    /**
     * Performs lint on a given string
     * @param \CssLint\Linter $oCssLinter the instance of the linter
     * @param string $sString the CSS string to be linted
     * @return int the return code related to the execution of the linter
     */
    private function lintString(\CssLint\Linter $oCssLinter, string $sString): int
    {
        $this->printLine('# Lint CSS string...');

        if ($oCssLinter->lintString($sString)) {
            $this->printLine("\033[32m => CSS string is valid\033[0m" . PHP_EOL);
            return self::$RETURN_CODE_SUCCESS;
        }

        $this->printLine("\033[31m => CSS string is not valid:\033[0m" . PHP_EOL);
        $this->displayLinterErrors($oCssLinter->getErrors());
        return self::$RETURN_CODE_ERROR;
    }

    /**
     * Display an error message
     * @param string $sError the message to be displayed
     */
    private function printError(string $sError)
    {
        $this->printLine("\033[31m/!\ Error: " . $sError . "\033[0m" . PHP_EOL);
    }

    /**
     * Display the errors returned by the linter
     * @param array $aErrors the generated errors to be displayed
     */
    private function displayLinterErrors(array $aErrors)
    {
        foreach ($aErrors as $sError) {
            $this->printLine("\033[31m    - " . $sError . "\033[0m");
        }
        $this->printLine("");
    }

    /**
     * Display the given message in a new line
     * @param string $sMessage the message to be displayed
     */
    private function printLine(string $sMessage)
    {
        echo $sMessage . PHP_EOL;
    }
}
