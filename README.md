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

| Polyfill | Level | Version | Type |
|----------|-------|---------|------|
| `fsync()` | ❇️ Best-effort | PHP 8.1+ | Function |
| `fdatasync()` | ❇️ Best-effort | PHP 8.1+ | Function |
| `imagecreatefromavif()` | ⚠️ Stub | PHP 8.1+ | Function |
| `imageavif()` | ⚠️ Stub | PHP 8.1+ | Function |
| `IMG_AVIF` | ✅ Full | PHP 8.1+ | Constant |
| `IMG_WEBP_LOSSLESS` | ✅ Full | PHP 8.1+ | Constant |
| `MYSQLI_REFRESH_REPLICA` | ✅ Full | PHP 8.1+ | Constant |
| `T_READONLY` | ✅ Full | PHP 8.1+ | Constant |
| `curl_upkeep()` | ❇️ Best-effort | PHP 8.2+ | Function |
| `libxml_get_external_entity_loader()` | ⚠️ Stub | PHP 8.2+ | Function |
| `memory_reset_peak_usage()` | ⚠️ No-op | PHP 8.2+ | Function |
| `mysqli_execute_query()` | ✅ Full | PHP 8.2+ | Function |
| `openssl_cipher_key_length()` | ✅ Full | PHP 8.2+ | Function |
| `sodium_crypto_stream_xchacha20_xor_ic()` | ⚠️ Stub | PHP 8.2+ | Function |
| `imap_is_open()` | ✅ Full | PHP 8.2+ | Function |
| `posix_sysconf()` | ⚠️ Stub | PHP 8.3+ | Function |
| `posix_pathconf()` | ⚠️ Stub | PHP 8.3+ | Function |
| `posix_fpathconf()` | ⚠️ Stub | PHP 8.3+ | Function |
| `posix_eaccess()` | ❇️ Best-effort | PHP 8.3+ | Function |
| `socket_atmark()` | ⚠️ Stub | PHP 8.3+ | Function |
| `POSIX_PC_*` (10 constants) | ✅ Full | PHP 8.3+ | Constant |
| `POSIX_F_OK`, `POSIX_R_OK`, `POSIX_W_OK`, `POSIX_X_OK` | ✅ Full | PHP 8.3+ | Constant |
| `bcceil()` | ✅ Full | PHP 8.4+ | Function |
| `bcfloor()` | ✅ Full | PHP 8.4+ | Function |
| `bcround()` | ✅ Full | PHP 8.4+ | Function |
| `request_parse_body()` | ⚠️ Stub | PHP 8.4+ | Function |
| `ldap_exop()` | ⚠️ Stub | PHP 8.4+ | Function |
| `ldap_parse_exop()` | ⚠️ Stub | PHP 8.4+ | Function |
| `PHP_SBINDIR` | ✅ Full | PHP 8.4+ | Constant |
| `curl_multi_get_handles()` | ⚠️ Stub | PHP 8.5+ | Function |
| `grapheme_levenshtein()` | ✅ Full | PHP 8.5+ | Function |
| `FILTER_THROW_ON_FAILURE` | ✅ Full | PHP 8.5+ | Constant |
| `PHP_BUILD_DATE` | ✅ Full | PHP 8.5+ | Constant |
| `PHP_BUILD_PROVIDER` | ✅ Full | PHP 8.5+ | Constant |
| `Uri\Rfc3986\Uri` | ✅ Full | PHP 8.5+ | Class |
| `Uri\WhatWg\Url` | ✅ Full | PHP 8.5+ | Class |
| `Uri\WhatWg\InvalidUrlException` | ✅ Full | PHP 8.5+ | Class |
| `Uri\WhatWg\UrlValidationError` | ✅ Full | PHP 8.5+ | Class |
| `Uri\WhatWg\UrlValidationErrorType` | ✅ Full | PHP 8.5+ | Class |
| `Uri\UriException` | ✅ Full | PHP 8.5+ | Class |
| `Uri\InvalidUriException` | ✅ Full | PHP 8.5+ | Class |
| `Uri\UriComparisonMode` | ✅ Full | PHP 8.5+ | Class |

> **Note:** Additionally, all [Symfony Polyfill](https://github.com/symfony/polyfill) packages are included.

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