# CssLint\Linter  







## Methods

| Name | Description |
|------|-------------|
|[__construct](#linter__construct)|Constructor|
|[getCssLintProperties](#lintergetcsslintproperties)|Return an instance of the "\CssLint\Properties" helper, initialize a new one if not define already|
|[getErrors](#lintergeterrors)|Return the errors occurred during the lint process|
|[lintFile](#linterlintfile)|Performs lint for a given file path|
|[lintString](#linterlintstring)|Performs lint on a given string|
|[setCssLintProperties](#lintersetcsslintproperties)|Set an instance of the "\CssLint\Properties" helper|




### Linter::__construct  

**Description**

```php
public __construct (\CssLint\Properties $oProperties)
```

Constructor 

 

**Parameters**

* `(\CssLint\Properties) $oProperties`
: (optional) an instance of the "\CssLint\Properties" helper  

**Return Values**

`void`


<hr />


### Linter::getCssLintProperties  

**Description**

```php
public getCssLintProperties (void)
```

Return an instance of the "\CssLint\Properties" helper, initialize a new one if not define already 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`\CssLint\Properties`




<hr />


### Linter::getErrors  

**Description**

```php
public getErrors (void)
```

Return the errors occurred during the lint process 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`array`




<hr />


### Linter::lintFile  

**Description**

```php
public lintFile (string $sFilePath)
```

Performs lint for a given file path 

 

**Parameters**

* `(string) $sFilePath`
: : a path of an existing and readable file  

**Return Values**

`bool`

> : true if the file is a valid css file, else false


**Throws Exceptions**


`\InvalidArgumentException`


`\RuntimeException`


<hr />


### Linter::lintString  

**Description**

```php
public lintString (string $sString)
```

Performs lint on a given string 

 

**Parameters**

* `(string) $sString`

**Return Values**

`bool`

> : true if the string is a valid css string, false else


<hr />


### Linter::setCssLintProperties  

**Description**

```php
public setCssLintProperties (void)
```

Set an instance of the "\CssLint\Properties" helper 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`


<hr />

