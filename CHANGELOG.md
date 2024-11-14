# Changelog

## 5.1.1 - 2024-11-14

### Changed

* Issue [#320](https://github.com/dotkernel/api/issues/320): composer.json dependencies cleanup by [@alexmerlin](https://github.com/alexmerlin) in [#346](https://github.com/dotkernel/api/pull/346)

### Added

* Nothing

### Deprecated

* Nothing

### Removed

* Issue [#340](https://github.com/dotkernel/api/issues/340): Removed laminas/laminas-config package by [@MarioRadu](https://github.com/MarioRadu) in [#344](https://github.com/dotkernel/api/pull/344)

### Fixed

* Nothing

## 5.1.0 - 2024-10-31

### Changed

* Nothing

### Added

* Issue [#325](https://github.com/dotkernel/api/issues/325): Implemented enums in database by [@alexmerlin](https://github.com/alexmerlin) in [#339](https://github.com/dotkernel/api/pull/339)

### Deprecated

* Nothing

### Removed

* Nothing

### Fixed

* Nothing

## 5.0.2 - 2024-10-29

### Changed

* Update qodana_code_quality.yml by [@arhimede](https://github.com/arhimede) in [#323](https://github.com/dotkernel/api/pull/323)

### Added

* Added .gitattributes by [@bidi47](https://github.com/bidi47) in [#321](https://github.com/dotkernel/api/pull/321)
* Issue [#331](https://github.com/dotkernel/api/issues/331): Added `symfony/var-dumper` to the dev requirements by [@alexmerlin](https://github.com/alexmerlin) in [#333](https://github.com/dotkernel/api/pull/333)
* Issue [#337](https://github.com/dotkernel/api/issues/337): Implemented handle delegators by [@alexmerlin](https://github.com/alexmerlin) in [#338](https://github.com/dotkernel/api/pull/338)

### Deprecated

* Nothing

### Removed

* Nothing

### Fixed

* Nothing

## 5.0.1 - 2024-09-13

### Changed

* Issue [#311](https://github.com/dotkernel/api/issues/311): Upgraded `dot-errorhandler` to `4.x` by
  [@alexmerlin](https://github.com/alexmerlin) in [#312](https://github.com/dotkernel/api/pull/312)

### Added

* Issue [#168](https://github.com/dotkernel/api/issues/168): OpenAPI documentation by
  [@alexmerlin](https://github.com/alexmerlin) in [#306](https://github.com/dotkernel/api/pull/306)
* Issue [#309](https://github.com/dotkernel/api/issues/309): psr-container-doctrine 5.2.1 support and refactoring
  modules configuration by [@cPintiuta](https://github.com/cPintiuta) in
  [#309](https://github.com/dotkernel/api/pull/309)

### Deprecated

* Nothing

### Removed

* Issue [#313](https://github.com/dotkernel/api/issues/313): Remove `config` dependency from handlers. by
  [@alexmerlin](https://github.com/alexmerlin) in [#315](https://github.com/dotkernel/api/pull/315)

### Fixed

* Issue [#303](https://github.com/dotkernel/api/issues/303): fix content type, special case for multipart/form-data by
  [@cPintiuta](https://github.com/cPintiuta) in [#304](https://github.com/dotkernel/api/pull/304)

## 5.0.0 - 2024-07-01

### Changed

* Refactor: Transfer responsibility from handlers to services. by [@alexmerlin](https://github.com/alexmerlin) in
  [#272](https://github.com/dotkernel/api/pull/272)
* Issue [#169](https://github.com/dotkernel/api/issues/169): API deprecation refactoring by
  [@MarioRadu](https://github.com/MarioRadu) in [#291](https://github.com/dotkernel/api/pull/291)
* Issue [#264](https://github.com/dotkernel/api/issues/264): Bump for doctrine orm 2 -> 3, dbal 3 -> 4 through roave
  psr container by [@cPintiuta](https://github.com/cPintiuta) in [#283](https://github.com/dotkernel/api/pull/283)
* Issue [#266](https://github.com/dotkernel/api/issues/266): Replaced annotation-based dependency injection with
  attribute-based dependency injection by [@alexmerlin](https://github.com/alexmerlin) in
  [#280](https://github.com/dotkernel/api/pull/280)
* Issue [#295](https://github.com/dotkernel/api/issues/295): ContentNegotiationMiddleware: Make `$config` readonly by
  [@alexmerlin](https://github.com/alexmerlin) in [#296](https://github.com/dotkernel/api/pull/296)
* Updated license file by [@arhimede](https://github.com/arhimede) in [#282](https://github.com/dotkernel/api/pull/282)
* Update README.md by [@arhimede](https://github.com/arhimede) in [#290](https://github.com/dotkernel/api/pull/290)
* Update qodana_code_quality.yml by [@arhimede](https://github.com/arhimede) in
  [#294](https://github.com/dotkernel/api/pull/294)

### Added

* Issue [#169](https://github.com/dotkernel/api/issues/169): Implemented API evolution pattern by
  [@MarioRadu](https://github.com/MarioRadu) in [#285](https://github.com/dotkernel/api/pull/285)
* Added missing factory spec for ErrorReportHandler. local.php.dist: removed an unnecessary use statement by
  [@alexmerlin](https://github.com/alexmerlin) in [#284](https://github.com/dotkernel/api/pull/284)
* Added version in home handler by [@arhimede](https://github.com/arhimede) in
  [#287](https://github.com/dotkernel/api/pull/287)

### Deprecated

* Nothing

### Removed

* Nothing

### Fixed

* Issue [#277](https://github.com/dotkernel/api/issues/277): Sorted routes by name by
  [@alexmerlin](https://github.com/alexmerlin) in [#278](https://github.com/dotkernel/api/pull/278)
