<div align="center">

# Phuture Continuum

**A forward-compatibility layer bringing tomorrow’s PHP features to today’s runtimes.**

![PHP Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2Fphuture-dev%2Fcontinuum%2Frefs%2Fheads%2Fmain%2Fcomposer.json&query=require.php&style=for-the-badge&label=PHP%20Version&color=purple)
![Latest Release](https://img.shields.io/github/v/tag/phuture-dev/continuum?sort=semver&style=for-the-badge&label=latest%20release&color=blue)
![Tests Status](https://img.shields.io/github/actions/workflow/status/phuture-dev/continuum/tests.yml?style=for-the-badge&label=tests)
![License](https://img.shields.io/github/license/phuture-dev/continuum?style=for-the-badge)

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
| `SQLite3Exception`                                    | ✅ Full    | Class     |
| `ldap_exop_sync()`                                    | ✅ Full    | Function  |
| `ldap_connect_wallet()`                               | ✅ Full    | Function  |
| ▶️ **PHP 8.4+**                                                                |
| `array_find()`                                        | ✅ Full    | Function  |
| `array_find_key()`                                    | ✅ Full    | Function  |
| `array_any()`                                         | ✅ Full    | Function  |
| `array_all()`                                         | ✅ Full    | Function  |
| `fpow()`                                              | ✅ Full    | Function  |
| `grapheme_str_split()`                                | ✅ Full    | Function  |
| `PHP_SBINDIR`                                         | ✅ Full    | Constant  |
| `CURL_HTTP_VERSION_3`                                 | ✅ Full    | Constant  |
| `CURL_HTTP_VERSION_3ONLY`                             | ✅ Full    | Constant  |
| `Deprecated`                                          | ✅ Full    | Attribute |
| `RoundingMode`                                        | ✅ Full    | Enum      |
| `ReflectionConstant`                                  | ✅ Full    | Class     |
| `PDO` driver subclasses (Dblib, Firebird, etc.)       | ✅ Full    | Class     |
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
| `Filter\FilterException`                              | ✅ Full    | Class     |
| `Filter\FilterFailedException`                        | ✅ Full    | Class     |

This package also provides polyfills for some PHP extensions, allowing better portability across different PHP runtimes.

| Extension      | Level      |
|----------------|------------|
| `ext-mbstring` | ⚠️ Partial |
| `ext-iconv`    | ✅ Full    |
| `ext-apcu`     | ⚠️ Partial |
| `ext-ctype`    | ✅ Full    |
| `ext-uuid`     | ✅ Full    |
| `ext-bcmath`   | ✅ Full    |
| `ext-intl`     | ⚠️ Partial |
| `ext-zip`      | ⚠️ Partial |

## What's Not Covered

The entries marked ❇️ Partial or ⚠️ Partial in the tables above have meaningful gaps. This section documents exactly what each one omits or cannot replicate.

### Partial Polyfills

**`fsync()` / `fdatasync()`** (PHP 8.1)
- Both fall back to `fflush()`, plus `posix_fsync()` when the `posix` extension is loaded.
- There is no guarantee of actual disk-level synchronization on systems without the POSIX extension.
- The return value is always `true`, even when the underlying OS sync did not occur.

**`curl_upkeep()`** (PHP 8.2)
- Only sets `CURLOPT_FORBID_REUSE = false` and `CURLOPT_FRESH_CONNECT = false`.
- Does not perform real HTTP/2 connection maintenance (window-size updates, PING frames, or keep-alive signalling).
- Full behaviour requires PHP 8.2+ with libcurl ≥ 7.62.0.

### Partial Extensions

**`ext-mbstring`**
- The only functions with **no polyfill** are the stateful regex-search family: `mb_ereg_search()`, `mb_ereg_search_init()`, `mb_ereg_search_pos()`, `mb_ereg_search_regs()`, `mb_ereg_search_getpos()`, `mb_ereg_search_getregs()`, and `mb_ereg_search_setpos()`.
- `mb_convert_kana()` supports only the `R`, `r`, `N`, `n`, `K`, `k`, `V`, `A`, `a` conversion modes. 

**`ext-apcu`**
- In-memory, non-persistent cache: data is lost between requests.
- Scoped to a single process; entries are not shared across PHP-FPM workers.
- No atomic operations beyond basic `get`/`set`; no TTL-based eviction or shared-memory sizing.

**`ext-intl`**
- `Collator`: simplified sorting - does not implement the full Unicode Collation Algorithm.
- `NumberFormatter`: advanced formatting features (currency symbol overrides, padding, significant digits) are missing.
- `Locale`: operations are simplified; no full CLDR data lookup.
- No direct access to ICU binary data; limited to the subset bundled with Symfony.

**`ext-zip`**
- `ZipArchive::getArchiveFlag()` - always returns `0`; archive-level flags are a libzip internal concept not accessible through userland php.
- `ZipArchive::setArchiveFlag()` - always returns `false`.
- `ZipArchive::registerProgressCallback()` - always returns `false`.
- `ZipArchive::registerCancelCallback()` - always returns `false`.
- Procedural `zip_entry_open()` only accepts modes `r` and `rb`; write-mode entry access is not supported.
- Multi-disk ZIP archives are not supported.
- Writing with compression methods `CM_LZMA`, `CM_LZMA2`, and `CM_XZ` is not supported.

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