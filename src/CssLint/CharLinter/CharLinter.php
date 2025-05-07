<?php

declare(strict_types=1);

namespace CssLint\CharLinter;

use CssLint\LintContext;

interface CharLinter
{
    public function lintChar(string $charValue, LintContext $lintContext): ?bool;
}
