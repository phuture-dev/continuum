<div align="center">

# Phuture Continuum

**A forward-compatibility layer bringing tomorrow’s PHP features to today’s runtimes.**

![PHP Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2Fphuture-dev%2Fcontinuum%2Frefs%2Fheads%2Fmain%2Fcomposer.json&query=require.php&style=for-the-badge&label=PHP%20Version&color=purple)
![Latest Release](https://img.shields.io/github/v/tag/phuture-dev/continuum?sort=semver&style=for-the-badge&label=latest%20release&color=blue)
![Tests Status](https://img.shields.io/github/actions/workflow/status/phuture-dev/continuum/tests.yml?style=for-the-badge&label=tests)
![License](https://img.shields.io/github/license/phuture-dev/continuum?style=for-the-badge&color=orange)

</div>

## Introduction

**Continuum** bridges the gap between PHP versions by extending [Symfony Polyfill](https://github.com/symfony/polyfill) with additional methods, constants, and stubs. Built on Symfony's solid foundation, Continuum provides extra polyfills and graceful fallbacks—enabling you to write forward-compatible code that works consistently across different PHP runtimes.

## Features

This package polyfills most common PHP functions from PHP 8.1 to 8.5, designed to be used on PHP 8.0 or later.

| Polyfill                                              | Level      | Type      |
|-------------------------------------------------------|------------|-----------|
| ▶️ **PHP 8.1+**                                                                |
| `array_is_list()`                                     | ✅ Full    | Function  |
| `enum_exists()`                                       | ✅ Full    | Function  |
| `fsync()`                                             | ❇️ Partial | Function  |
| `fdatasync()`                                         | ❇️ Partial | Function  |
| `IMAGETYPE_AVIF`                                      | ✅ Full    | Constant  |
| `IMG_AVIF`                                            | ✅ Full    | Constant  |
| `IMG_WEBP_LOSSLESS`                                   | ✅ Full    | Constant  |
| `MYSQLI_REFRESH_REPLICA`                              | ✅ Full    | Constant  |
| `T_READONLY`                                          | ✅ Full    | Constant  |
| `CURLStringFile`                                      | ✅ Full    | Class     |
| `ReturnTypeWillChange`                                | ✅ Full    | Attribute |
| ▶️ **PHP 8.2+**                                                                |
| `ini_parse_quantity()`                                | ✅ Full    | Function  |
| `odbc_connection_string_is_quoted()`                  | ✅ Full    | Function  |
| `odbc_connection_string_should_quote()`               | ✅ Full    | Function  |
| `odbc_connection_string_quote()`                      | ✅ Full    | Function  |
| `curl_upkeep()`                                       | ❇️ Partial | Function  |
| `memory_reset_peak_usage()`                           | ⚠️ No-op   | Function  |
| `mysqli_execute_query()`                              | ✅ Full    | Function  |
| `openssl_cipher_key_length()`                         | ✅ Full    | Function  |
| `imap_is_open()`                                      | ✅ Full    | Function  |
| `AllowDynamicProperties`                              | ✅ Full    | Attribute |
| `SensitiveParameter`                                  | ✅ Full    | Attribute |
| `SensitiveParameterValue`                             | ✅ Full    | Class     |
| `Random\Engine`                                       | ✅ Full    | Interface |
| `Random\CryptoSafeEngine`                             | ✅ Full    | Interface |
| `Random\Engine\Secure`                                | ✅ Full    | Class     |
| ▶️ **PHP 8.3+**                                                                |
| `json_validate()`                                     | ✅ Full    | Function  |
| `mb_str_pad()`                                        | ✅ Full    | Function  |
| `str_increment()`                                     | ✅ Full    | Function  |
| `str_decrement()`                                     | ✅ Full    | Function  |
| `stream_context_set_options()`                        | ✅ Full    | Function  |
| `posix_eaccess()`                                     | ✅ Full    | Function  |
| `POSIX_PC_*` (10 constants)                           | ✅ Full    | Constant  |
| `POSIX_*_OK` (4 constants)                            | ✅ Full    | Constant  |
| `Override`                                            | ✅ Full    | Attribute |
| `DateError` / `DateException` (and related)           | ✅ Full    | Class     |
| ▶️ **PHP 8.4+**                                                                |
| `array_find()`                                        | ✅ Full    | Function  |
| `array_find_key()`                                    | ✅ Full    | Function  |
| `array_any()`                                         | ✅ Full    | Function  |
| `array_all()`                                         | ✅ Full    | Function  |
| `mb_trim()` / `mb_ltrim()` / `mb_rtrim()`             | ✅ Full    | Function  |
| `mb_ucfirst()` / `mb_lcfirst()`                       | ✅ Full    | Function  |
| `bcdivmod()`                                          | ✅ Full    | Function  |
| `fpow()`                                              | ✅ Full    | Function  |
| `grapheme_str_split()`                                | ✅ Full    | Function  |
| `bcceil()`                                            | ✅ Full    | Function  |
| `bcfloor()`                                           | ✅ Full    | Function  |
| `bcround()`                                           | ✅ Full    | Function  |
| `PHP_SBINDIR`                                         | ✅ Full    | Constant  |
| `CURL_HTTP_VERSION_3`                                 | ✅ Full    | Constant  |
| `CURL_HTTP_VERSION_3ONLY`                             | ✅ Full    | Constant  |
| `Deprecated`                                          | ✅ Full    | Attribute |
| `ReflectionConstant`                                  | ✅ Full    | Class     |
| ▶️ **PHP 8.5+**                                                                |
| `array_first()`                                       | ✅ Full    | Function  |
| `array_last()`                                        | ✅ Full    | Function  |
| `get_error_handler()`                                 | ✅ Full    | Function  |
| `get_exception_handler()`                             | ✅ Full    | Function  |
| `locale_is_right_to_left()`                           | ✅ Full    | Function  |
| `grapheme_levenshtein()`                              | ✅ Full    | Function  |
| `PHP_BUILD_DATE`                                      | ✅ Full    | Constant  |
| `PHP_BUILD_PROVIDER`                                  | ✅ Full    | Constant  |
| `NoDiscard`                                           | ✅ Full    | Attribute |
| `DelayedTargetValidation`                             | ✅ Full    | Attribute |
| `Uri\Rfc3986\Uri`                                     | ✅ Full    | Class     |
| `Uri\WhatWg\Url`                                      | ✅ Full    | Class     |
| `Uri\WhatWg\InvalidUrlException`                      | ✅ Full    | Class     |
| `Uri\WhatWg\UrlValidationError`                       | ✅ Full    | Class     |
| `Uri\WhatWg\UrlValidationErrorType`                   | ✅ Full    | Class     |
| `Uri\UriException`                                    | ✅ Full    | Class     |
| `Uri\InvalidUriException`                             | ✅ Full    | Class     |
| `Uri\UriComparisonMode`                               | ✅ Full    | Class     |

This package also provides polyfills for some PHP extensions, allowing better portability across different PHP runtimes.

| Extension      |
|----------------|
| `ext-mbstring` |
| `ext-iconv`    |
| `ext-apcu`     |
| `ext-ctype`    |
| `ext-uuid`     |
| `ext-bcmath`   |
| `ext-intl`     |

## Installation

```bash
composer require phuture/continuum
```

## Contributing

Thank you for considering contributing to this project! You can read the **[Contribution Guide](CONTRIBUTING.md)** and our **[Developer Workflow Guide](WORKFLOW.md)**.

## Code of Conduct

This project follows a Code of Conduct that all community members and contributors are expected to adhere to our **[Contributor Code of Conduct](CODE_OF_CONDUCT.md)**.

## License

This project is open-source and available under the **MIT License**.