# Zenmanage API SDK for Laravel 

[![Build Status](https://github.com/zenmanage/zenmanage-laravel/actions/workflows/tests.yml/badge.svg)](https://github.com/zenmanage/zenmanage-laravel) [![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=zenmanage_zenmanage-laravel&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=zenmanage_zenmanage-laravel)

This library helps with integrating Zenmanage into Laravel applications.

## Installation

This library can be installed via [Composer](https://getcomposer.org):

```bash
composer require zenmanage/zenmanage-laravel
```

## Configuration

The only required configuration is the Environment Token. You can get your Environment Token via the [Project settings](https://app.zenmanage.com/admin/projects) in your Zenmanage account.

Configuration values can be set when creating a new API client or via environment variables. The environment takes precedence over values provided during the initialization process.

**Configuration via environment variables**

```bash
ZENMANAGE_ENVIRONMENT_TOKEN=tok_sample
```

## Contributing

Bug reports and pull requests are welcome on GitHub at https://github.com/zenmanage/zenmanage-laravel. This project is intended to be a safe, welcoming space for collaboration, and contributors are expected to adhere to the [Contributor Covenant](http://contributor-covenant.org) code of conduct.

## License

The library is available as open source under the terms of the [MIT License](http://opensource.org/licenses/MIT).

## Code of Conduct

Everyone interacting in the Zenmanage's code bases, issue trackers, chat rooms and mailing lists is expected to follow the [code of conduct](https://github.com/zenmanage/zenmanage-laravel/blob/master/CODE_OF_CONDUCT.md).

## What is Zenmanage?

[Zenmanage](https://zenmanage.com/) allows you to control which features and settings are enabled in your application giving you better flexibility to deploy code and release features.

Zenmanage was started in 2024 as an alternative to highly complex feature flag tools. Learn more [about us](https://zenmanage.com/).
