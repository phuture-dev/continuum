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

| Polyfill                                               | Level          | Type     |
|--------------------------------------------------------|----------------|----------|
| ▶️ **PHP 8.1+**                                                                    |
| `fsync()`                                              | ❇️ Best-effort | Function |
| `fdatasync()`                                          | ❇️ Best-effort | Function |
| `imagecreatefromavif()`                                | ⚠️ Stub        | Function |
| `imageavif()`                                          | ⚠️ Stub        | Function |
| `IMG_AVIF`                                             | ✅ Full        | Constant |
| `IMG_WEBP_LOSSLESS`                                    | ✅ Full        | Constant |
| `MYSQLI_REFRESH_REPLICA`                               | ✅ Full        | Constant |
| `T_READONLY`                                           | ✅ Full        | Constant |
| ▶️ **PHP 8.2+**                                                                    |
| `curl_upkeep()`                                        | ❇️ Best-effort | Function |
| `libxml_get_external_entity_loader()`                  | ⚠️ Stub        | Function |
| `memory_reset_peak_usage()`                            | ⚠️ No-op       | Function |
| `mysqli_execute_query()`                               | ✅ Full        | Function |
| `openssl_cipher_key_length()`                          | ✅ Full        | Function |
| `sodium_crypto_stream_xchacha20_xor_ic()`              | ⚠️ Stub        | Function |
| `imap_is_open()`                                       | ✅ Full        | Function |
| ▶️ **PHP 8.3+**                                                                    |
| `posix_sysconf()`                                      | ⚠️ Stub        | Function |
| `posix_pathconf()`                                     | ⚠️ Stub        | Function |
| `posix_fpathconf()`                                    | ⚠️ Stub        | Function |
| `posix_eaccess()`                                      | ❇️ Best-effort | Function |
| `socket_atmark()`                                      | ⚠️ Stub        | Function |
| `POSIX_PC_*` (10 constants)                            | ✅ Full        | Constant |
| `POSIX_F_OK`, `POSIX_R_OK`, `POSIX_W_OK`, `POSIX_X_OK` | ✅ Full        | Constant |
| ▶️ **PHP 8.4+**                                                                    |
| `bcceil()`                                             | ✅ Full        | Function |
| `bcfloor()`                                            | ✅ Full        | Function |
| `bcround()`                                            | ✅ Full        | Function |
| `request_parse_body()`                                 | ⚠️ Stub        | Function |
| `ldap_exop()`                                          | ⚠️ Stub        | Function |
| `ldap_parse_exop()`                                    | ⚠️ Stub        | Function |
| `PHP_SBINDIR`                                          | ✅ Full        | Constant |
| ▶️ **PHP 8.5+**                                                                    |
| `locale_is_right_to_left()`                            | ✅ Full        | Function |
| `grapheme_levenshtein()`                               | ✅ Full        | Function |
| `PHP_BUILD_DATE`                                       | ✅ Full        | Constant |
| `PHP_BUILD_PROVIDER`                                   | ✅ Full        | Constant |
| `Uri\Rfc3986\Uri`                                      | ✅ Full        | Class    |
| `Uri\WhatWg\Url`                                       | ✅ Full        | Class    |
| `Uri\WhatWg\InvalidUrlException`                       | ✅ Full        | Class    |
| `Uri\WhatWg\UrlValidationError`                        | ✅ Full        | Class    |
| `Uri\WhatWg\UrlValidationErrorType`                    | ✅ Full        | Class    |
| `Uri\UriException`                                     | ✅ Full        | Class    |
| `Uri\InvalidUriException`                              | ✅ Full        | Class    |
| `Uri\UriComparisonMode`                                | ✅ Full        | Class    |

This package also provides polyfills for some PHP extensions, allowing better portability across different PHP runtimes.

| Extension      | Level          |
|----------------|----------------|
| `ext-mbstring` | ✅ Full        |
| `ext-iconv`    | ✅ Full        |
| `ext-apcu`     | ✅ Full        |
| `ext-ctype`    | ✅ Full        |
| `ext-uuid`     | ✅ Full        |
| `ext-bcmath`   | ✅ Full        |
| `ext-intl`     | ❇️ Best-effort |

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