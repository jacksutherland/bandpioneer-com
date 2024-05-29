# Changelog

## 3.0.2 - 2024-05-18

### Added
- Add support for [Closure](https://github.com/nystudio107/craft-closure) module and add `collect` to allowed Twig functions.

### Fixed
- Fix an error when parsing templates.

## 3.0.1 - 2024-05-18

### Added
- Add `GlobalsExtension` and `StringLoaderExtension` to template parser.

### Changed
- Loosen template security policy to allow methods and properties.

## 3.0.0 - 2024-05-11

### Added
- Add support for Craft and plugin Twig extensions in allowed Twig.
- Add `ArrayHelper::filterNullFalse()`.

### Changed
- Now requires PHP `8.2.0+`.
- Now requires Craft `5.0.0+`.

## 2.0.7 - 2024-05-18

### Added
- Add support for [Closure](https://github.com/nystudio107/craft-closure) module and add `collect` to allowed Twig functions.

### Fixed
- Fix an error when parsing templates.

## 2.0.6 - 2024-05-18

### Added
- Add `GlobalsExtension` and `StringLoaderExtension` to template parser.

### Changed
- Loosen template security policy to allow methods and properties.

## 2.0.5 - 2024-03-14

### Added
- Add support for Craft and plugin Twig extensions in allowed Twig.

## 2.0.4 - 2024-03-03

### Added
- Add Templates service for easy cut-down, safe Twig string rendering.

## 2.0.3 - 2023-09-20

### Added
- Add ability to set Monolog target options.

## 2.0.2 - 2023-05-10

### Added
- Add `vuiGetValue()` as a Twig function for `ArrayHelper::getValue()`.

### Fixed
- Fix tabs support.

## 2.0.1 - 2022-09-30

### Fixed
- Fix an error by checking if a dispatcher actually exists before setting its targets. (thanks @boboldehampsink).

## 2.0.0 - 2022-05-05

### Changed
- Switch to Monolog for logging.
- Craft 4 upgrade.

### Fixed
- Fix credits css.

## 1.0.4 - 2021-11-03

### Fixed
- Fix sidebar tabs not working in some instances, and make fully accessible.

## 1.0.3 - 2021-03-18

### Fixed
- Fix file logging initializing too early before Craft has been bootstrapped.

## 1.0.2 - 2020-04-15

### Added
- Add file logging helper.

## 1.0.1 - 2020-01-25

### Changed
- Lower Craft requirement.

## 1.0.0 - 2020-01-12

### Changed
- Craft 3 upgrade.

## 0.1.0 - 2015-06-07

- Initial release.
