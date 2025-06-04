<?php

declare(strict_types=1);

namespace CssLint;

enum LintErrorKey: string
{
    case UNCLOSED_TOKEN = 'unclosed_token';
    case UNEXPECTED_CHARACTER_END_OF_CONTENT = 'unexpected_character_end_of_content';
    case UNEXPECTED_SELECTOR_CHARACTER = 'unexpected_selector_character';
    case INVALID_PROPERTY_DECLARATION = 'invalid_property_declaration';
    case UNEXPECTED_CHARACTER_IN_BLOCK_CONTENT = 'unexpected_character_in_block_content';
    case INVALID_INDENTATION_CHARACTER = 'invalid_indentation_character';
    case MIXED_INDENTATION = 'mixed_indentation';
    case INVALID_INDENTATION_SIZE = 'invalid_indentation_size';
    case INVALID_AT_RULE_DECLARATION = 'invalid_at_rule_declaration';
    case INVALID_AT_RULE_VALUE = 'invalid_at_rule_value';
}
