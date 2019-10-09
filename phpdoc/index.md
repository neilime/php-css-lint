## Table of contents

- [\CssLint\Linter](#class-csslintlinter)
- [\CssLint\Properties](#class-csslintproperties)

<hr />

### Class: \CssLint\Linter

| Visibility | Function |
|:-----------|:---------|
| public | <strong>getCssLintProperties()</strong> : <em>[\CssLint\Properties](#class-csslintproperties)</em><br /><em>Return an instance of the "\CssLint\Properties" helper, initialize a new one if not define already</em> |
| public | <strong>getErrors()</strong> : <em>array</em><br /><em>Return the errors occured during the lint process</em> |
| public | <strong>lintFile(</strong><em>string</em> <strong>$sFilePath</strong>)</strong> : <em>boolean : true if the file is a valid css file, else false</em><br /><em>Performs lint for a given file path</em> |
| public | <strong>lintString(</strong><em>string</em> <strong>$sString</strong>)</strong> : <em>boolean : true if the string is a valid css string, false else</em><br /><em>Performs lint on a given string</em> |
| protected | <strong>addContextContent(</strong><em>string</em> <strong>$sContextContent</strong>)</strong> : <em>[\CssLint\Linter](#class-csslintlinter)</em><br /><em>Append new value to context content</em> |
| protected | <strong>addError(</strong><em>string</em> <strong>$sError</strong>)</strong> : <em>[\CssLint\Linter](#class-csslintlinter)</em><br /><em>Add a new error message to the errors property, it adds extra infos to the given error message</em> |
| protected | <strong>assertContext(</strong><em>string/array</em> <strong>$sContext</strong>)</strong> : <em>boolean</em><br /><em>Assert that current context is the same as given</em> |
| protected | <strong>assertPreviousChar(</strong><em>string</em> <strong>$sChar</strong>)</strong> : <em>boolean</em><br /><em>Assert that previous char is the same as given</em> |
| protected | <strong>getCharNumber()</strong> : <em>int</em><br /><em>Return the current char number</em> |
| protected | <strong>getContextContent()</strong> : <em>string</em><br /><em>Return context content</em> |
| protected | <strong>getLineNumber()</strong> : <em>int</em><br /><em>Return the current line number</em> |
| protected | <strong>incrementCharNumber()</strong> : <em>[\CssLint\Linter](#class-csslintlinter)</em><br /><em>Add 1 to the current char number</em> |
| protected | <strong>incrementLineNumber()</strong> : <em>[\CssLint\Linter](#class-csslintlinter)</em><br /><em>Add 1 to the current line number</em> |
| protected | <strong>initLint()</strong> : <em>[\CssLint\Linter](#class-csslintlinter)</em><br /><em>Initialize linter, reset all process properties</em> |
| protected | <strong>isComment()</strong> : <em>boolean</em><br /><em>Tells if the linter is parsing a comment</em> |
| protected | <strong>isEndOfLine(</strong><em>string</em> <strong>$sChar</strong>)</strong> : <em>boolean : true if the char is an end of line token, else false</em><br /><em>Check if a given char is an end of line token</em> |
| protected | <strong>isNestedSelector()</strong> : <em>boolean</em><br /><em>Tells if the linter is parsing a nested selector</em> |
| protected | <strong>lintChar(</strong><em>string</em> <strong>$sChar</strong>)</strong> : <em>boolean : true if the process should continue, else false</em><br /><em>Performs lint on a given char</em> |
| protected | <strong>lintCommentChar(</strong><em>string</em> <strong>$sChar</strong>)</strong> : <em>boolean/null : true if the process should continue, else false, null if this char is not about comment</em><br /><em>Performs lint for a given char, check comment part</em> |
| protected | <strong>lintNestedSelectorChar(</strong><em>string</em> <strong>$sChar</strong>)</strong> : <em>boolean/null : true if the process should continue, else false, null if this char is not about nested selector</em><br /><em>Performs lint for a given char, check nested selector part</em> |
| protected | <strong>lintPropertyContentChar(</strong><em>string</em> <strong>$sChar</strong>)</strong> : <em>boolean/null : true if the process should continue, else false, null if this char is not about property content</em><br /><em>Performs lint for a given char, check property content part</em> |
| protected | <strong>lintPropertyNameChar(</strong><em>string</em> <strong>$sChar</strong>)</strong> : <em>boolean/null : true if the process should continue, else false, null if this char is not about property name</em><br /><em>Performs lint for a given char, check property name part</em> |
| protected | <strong>lintSelectorChar(</strong><em>string</em> <strong>$sChar</strong>)</strong> : <em>boolean/null : true if the process should continue, else false, null if this char is not about selector</em><br /><em>Performs lint for a given char, check selector part</em> |
| protected | <strong>lintSelectorContentChar(</strong><em>string</em> <strong>$sChar</strong>)</strong> : <em>boolean/null : true if the process should continue, else false, null if this char is not about selector content</em><br /><em>Performs lint for a given char, check selector content part</em> |
| protected | <strong>resetCharNumber()</strong> : <em>[\CssLint\Linter](#class-csslintlinter)</em><br /><em>Reset current char number property</em> |
| protected | <strong>resetContext()</strong> : <em>[\CssLint\Linter](#class-csslintlinter)</em><br /><em>Reset context property</em> |
| protected | <strong>resetContextContent()</strong> : <em>[\CssLint\Linter](#class-csslintlinter)</em><br /><em>Reset context content property</em> |
| protected | <strong>resetErrors()</strong> : <em>[\CssLint\Linter](#class-csslintlinter)</em><br /><em>Reset the errors property</em> |
| protected | <strong>resetLineNumber()</strong> : <em>[\CssLint\Linter](#class-csslintlinter)</em><br /><em>Reset current line number property</em> |
| protected | <strong>resetPreviousChar()</strong> : <em>[\CssLint\Linter](#class-csslintlinter)</em><br /><em>Reset previous char property</em> |
| protected | <strong>setComment(</strong><em>boolean</em> <strong>$bComment</strong>)</strong> : <em>void</em><br /><em>Set the comment flag</em> |
| protected | <strong>setContext(</strong><em>string</em> <strong>$sContext</strong>)</strong> : <em>[\CssLint\Linter](#class-csslintlinter)</em><br /><em>Set new context</em> |
| protected | <strong>setNestedSelector(</strong><em>boolean</em> <strong>$bNestedSelector</strong>)</strong> : <em>void</em><br /><em>Set the nested selector flag</em> |
| protected | <strong>setPreviousChar(</strong><em>string</em> <strong>$sChar</strong>)</strong> : <em>[\CssLint\Linter](#class-csslintlinter)</em><br /><em>Set new previous char</em> |

<hr />

### Class: \CssLint\Properties

| Visibility | Function |
|:-----------|:---------|
| public | <strong>propertyExists(</strong><em>string</em> <strong>$sProperty</strong>)</strong> : <em>boolean</em> |

