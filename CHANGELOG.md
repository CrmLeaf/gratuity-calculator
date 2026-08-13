# Changelog

Notable changes to `crmleaf/gratuity-calculator`.

Format per [Keep a Changelog](https://keepachangelog.com/en/1.1.0/); versioning
per [Semantic Versioning](https://semver.org/spec/v2.0.0.html) - with one extra
rule this package observes, because it computes statutory figures:

> **Any change that alters a published result is at minimum a minor release**,
> and is listed under `Changed` with the notification, circular or Act section
> that prompted it.

## [Unreleased]

## [1.0.0] - 2026-08-12

### Added

- Initial release. Applies the statutory formula including the two rules that trip people up: a part-year counts only past six months, and the five-year qualifying period is waived on death or disablement.

### Statutory basis

- Payment of Gratuity Act 1972, section 4 - fifteen days' wages for every completed year, divided by 26 for a covered establishment and by 30 for one that is not - with the ₹20 lakh exemption under section 10(10) of the Income-tax Act 1961.

[Unreleased]: https://github.com/crmleaf/gratuity-calculator/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/crmleaf/gratuity-calculator/releases/tag/v1.0.0
