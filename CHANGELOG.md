# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [5.2.0] - 2026-08-16

### Added
- Built-in webhook endpoint (`POST /zenmanage/webhook` by default) that a Zenmanage environment webhook can call to immediately refresh cached flag rules instead of waiting for `cache_ttl` to lapse. Disabled by default — enable it with `webhook.enabled` (`ZENMANAGE_WEBHOOK_ENABLED`) and configure `webhook.secret` (`ZENMANAGE_WEBHOOK_SECRET`) to verify the `X-Zenmanage-Signature` header on incoming requests. See the README's Webhooks section. ([ZEN-1072](https://linear.app/zenmanage/issue/ZEN-1072))

## [5.1.3] - 2026-08-16

### Fixed
- Updated `zenmanage/zenmanage-php` dependency to `^5.1.3`, which fixes `FlagManager::all()` to fall back to the configured `DefaultsCollection` on a per-key basis — previously a `DefaultsCollection` set via `withDefaults()` was silently ignored by `all()`, so a total rule-loading failure returned an empty array instead of the configured defaults. Since `DirectClient::all()` delegates directly to `FlagManager::all()`, this fixes the same gap for Laravel. ([ZEN-1100](https://linear.app/zenmanage/issue/ZEN-1100))

## [5.1.2] - 2026-08-05

### Fixed
- Updated `zenmanage/zenmanage-php` dependency to `^5.1.2`, which fixes `FlagManager::single()` to report the effective default value (inline parameter, falling back to a `DefaultsCollection` entry) on every usage report, including when the flag is found and evaluated normally — previously the default was only sent on the fallback paths. Since `DirectClient::single()` delegates directly to `FlagManager::single()`, this fixes the same gap for Laravel. ([ZEN-1120](https://linear.app/zenmanage/issue/ZEN-1120))

## [5.1.1] - 2026-08-05

### Changed
- Updated `zenmanage/zenmanage-php` dependency to `^5.1.1`, which renames SDK request headers to use a consistent `X-ZEN-` prefix, matching the server-side convention:
  - `X-API-Key` → `X-ZEN-API-KEY`
  - `X-ZENMANAGE-CONTEXT` → `X-ZEN-CONTEXT`
  - `X-Default-Value` → `X-ZEN-DEFAULT-VALUE`
  - The API still accepts the old header names as legacy aliases, so this is non-breaking.

## [5.0.0] - 2026-05-29

### Changed
- Minimum PHP version raised to **8.1** — PHP 8.0 is no longer supported
- Updated `zenmanage/zenmanage-php` dependency to `^5.0.0`

### Removed
- PHP 8.0 support
