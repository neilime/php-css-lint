<?php

declare(strict_types=1);

namespace CssLint\Tokenizer;

final class TokenizerStringInspector
{
    public static function isSpace(string $char): bool
    {
        return ctype_space($char);
    }

    public static function isWhitespace(string $content): bool
    {
        return $content !== '' && ctype_space($content);
    }

    public static function hasOpenStringOrParenthesisContext(string $content): bool
    {
        $context = self::searchOpenStringOrParenthesis($content);
        return $context['hasOpenString'] || $context['hasOpenParenthesis'];
    }

    public static function hasOpenParenthesisContext(string $content): bool
    {
        return self::searchOpenStringOrParenthesis($content)['hasOpenParenthesis'];
    }

    /**
     * @return array{hasOpenString: bool, hasOpenParenthesis: bool}
     */
    private static function searchOpenStringOrParenthesis(string $content): array
    {
        if (strpbrk($content, "\"'()") === false) {
            return [
                'hasOpenString' => false,
                'hasOpenParenthesis' => false,
            ];
        }

        $stringDelimiter = null;
        $parenthesisLevel = 0;
        $isEscaped = false;

        $contentLength = strlen($content);
        for ($index = 0; $index < $contentLength; ++$index) {
            $char = $content[$index];
            if ($stringDelimiter !== null) {
                if ($isEscaped) {
                    $isEscaped = false;
                    continue;
                }

                if ($char === '\\') {
                    $isEscaped = true;
                    continue;
                }

                if ($char === $stringDelimiter) {
                    $stringDelimiter = null;
                }

                continue;
            }

            if ($char === '"' || $char === "'") {
                $stringDelimiter = $char;
                continue;
            }

            if ($char === '(') {
                ++$parenthesisLevel;
                continue;
            }

            if ($char === ')' && $parenthesisLevel > 0) {
                --$parenthesisLevel;
            }
        }

        return [
            'hasOpenString' => $stringDelimiter !== null,
            'hasOpenParenthesis' => $parenthesisLevel > 0,
        ];
    }
}
