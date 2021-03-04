# CssLint\Properties  







## Methods

| Name | Description |
|------|-------------|
|[getAllowedIndentationChars](#propertiesgetallowedindentationchars)|Retrieve indentation chars allowed by the linter|
|[isAllowedIndentationChar](#propertiesisallowedindentationchar)|Check if the given char is allowed as an indentation char|
|[mergeConstructors](#propertiesmergeconstructors)|Merge the given constructors properties with the current ones|
|[mergeNonStandards](#propertiesmergenonstandards)|Merge the given non standards properties with the current ones|
|[mergeStandards](#propertiesmergestandards)|Merge the given standards properties with the current ones|
|[propertyExists](#propertiespropertyexists)|Checks that the given CSS property is an existing one|
|[setAllowedIndentationChars](#propertiessetallowedindentationchars)|Define the indentation chars allowed by the linter|
|[setOptions](#propertiessetoptions)|Override default properties
"allowedIndentationChars" => [" "] or ["\t"]: will override current property
"constructors": ["property" => bool]: will merge with current property
"standards": ["property" => bool]: will merge with current property
"nonStandards": ["property" => bool]: will merge with current property|




### Properties::getAllowedIndentationChars  

**Description**

```php
public getAllowedIndentationChars (void)
```

Retrieve indentation chars allowed by the linter 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`array`

> a list of allowed indentation chars


<hr />


### Properties::isAllowedIndentationChar  

**Description**

```php
public isAllowedIndentationChar (string $sChar)
```

Check if the given char is allowed as an indentation char 

 

**Parameters**

* `(string) $sChar`
: the character to be checked  

**Return Values**

`bool`

> according to whether the character is allowed or not


<hr />


### Properties::mergeConstructors  

**Description**

```php
public mergeConstructors (array $aConstructors)
```

Merge the given constructors properties with the current ones 

 

**Parameters**

* `(array) $aConstructors`
: the constructors properties to be merged  

**Return Values**

`void`


<hr />


### Properties::mergeNonStandards  

**Description**

```php
public mergeNonStandards (array $aNonStandards)
```

Merge the given non standards properties with the current ones 

 

**Parameters**

* `(array) $aNonStandards`
: non the standards properties to be merged  

**Return Values**

`void`


<hr />


### Properties::mergeStandards  

**Description**

```php
public mergeStandards (array $aStandards)
```

Merge the given standards properties with the current ones 

 

**Parameters**

* `(array) $aStandards`
: the standards properties to be merged  

**Return Values**

`void`


<hr />


### Properties::propertyExists  

**Description**

```php
public propertyExists (string $sProperty)
```

Checks that the given CSS property is an existing one 

 

**Parameters**

* `(string) $sProperty`
: the property to check  

**Return Values**

`bool`

> true if the property exists, else returns false


<hr />


### Properties::setAllowedIndentationChars  

**Description**

```php
public setAllowedIndentationChars (array $aAllowedIndentationChars)
```

Define the indentation chars allowed by the linter 

 

**Parameters**

* `(array) $aAllowedIndentationChars`
: a list of allowed indentation chars  

**Return Values**

`void`


<hr />


### Properties::setOptions  

**Description**

```php
public setOptions (void)
```

Override default properties
"allowedIndentationChars" => [" "] or ["\t"]: will override current property
"constructors": ["property" => bool]: will merge with current property
"standards": ["property" => bool]: will merge with current property
"nonStandards": ["property" => bool]: will merge with current property 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`


<hr />

