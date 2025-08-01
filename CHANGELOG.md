# Changelog

## 3.x

### 3.0.1

*Aug 1, 2025*

* Replaced exceptions with errors

### 3.0.0

*Aug 1, 2025*

* PHP 8.5 is required
* All instances are now readonly
* Removed ``getComponents()`` and ``getPrefix()``

## 2.x

### 2.1.0

*Aug 1, 2025*

* Added ``$prefix`` and ``$components`` properties

### 2.0.3

*Jul 28, 2024*

* Reclassified some exceptions as runtime (`UnexpectedValueException`)

### 2.0.2

*Dec 21, 2023*

* Fix makeRelative no longer accepting any callable instead of Closure objects (unclean revert in 2.0.1)

### 2.0.1

*Dec 21, 2023*

* Remove ext-ds dependency introduced in 2.0.0 for performance reasons

### 2.0.0

*Dec 5, 2023*

Forked from 1.0.0

* Types are declared on PathUtils
* PHP 8.0 is required

## 1.x

### 1.0.0

*Nov 6, 2021*

Initial release
