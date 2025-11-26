# Changelog

## 7.0.0 - 2025-11-26

### Changed

* Issue [#471](https://github.com/dotkernel/api/issues/471): Clarify instructions regarding multiple connections in `config/autoload/local.php.dist` by [@alexmerlin](https://github.com/alexmerlin) in [#472](https://github.com/dotkernel/api/pull/472)
* updated readme, oss by [@bidi47](https://github.com/bidi47) in [#461](https://github.com/dotkernel/api/pull/461)

### Added

* Issue [#453](https://github.com/dotkernel/api/issues/453): Use native UUIDs in database via ramsey/uuid by [@alexmerlin](https://github.com/alexmerlin) in [#456](https://github.com/dotkernel/api/pull/456)
* Issue [#430](https://github.com/dotkernel/api/issues/430): PostgreSQL implementation by [@alexmerlin](https://github.com/alexmerlin) in [#462](https://github.com/dotkernel/api/pull/462)

### Deprecated

* Nothing

### Removed

* Issue [#468](https://github.com/dotkernel/api/issues/468): Remove `MethodDeprecation` implementation by [@alexmerlin](https://github.com/alexmerlin) in [#470](https://github.com/dotkernel/api/pull/470)

### Fixed

* Nothing

## 6.1.0 - 2025-11-10

### Changed

* Update README for clarity and badge links by [@arhimede](https://github.com/arhimede) in [#447](https://github.com/dotkernel/api/pull/447)

### Added

* Issue [#435](https://github.com/dotkernel/api/issues/435): Implement `dotkernel/dot-maker` in dev mode by [@alexmerlin](https://github.com/alexmerlin) in [#436](https://github.com/dotkernel/api/pull/436)
* Issue [#445](https://github.com/dotkernel/api/issues/445): Added `.laminas-ci.json` by [@alexmerlin](https://github.com/alexmerlin) in [#446](https://github.com/dotkernel/api/pull/446)
* Issue [#449](https://github.com/dotkernel/api/issues/449): Implemented Doctrine table prefixes by [@alexmerlin](https://github.com/alexmerlin) in [#452](https://github.com/dotkernel/api/pull/452)
* Issue [#450](https://github.com/dotkernel/api/issues/450): Add PHP 8.4 & 8.5 support by [@alexmerlin](https://github.com/alexmerlin) in [#454](https://github.com/dotkernel/api/pull/454)

### Deprecated

* Nothing

### Removed

* Issue [#438](https://github.com/dotkernel/api/issues/438): Removed migration file by [@alexmerlin](https://github.com/alexmerlin) in [#439](https://github.com/dotkernel/api/pull/439)
* Issue [#445](https://github.com/dotkernel/api/issues/445): Removed `.laminas-ci/pre-run.sh` by [@alexmerlin](https://github.com/alexmerlin) in [#446](https://github.com/dotkernel/api/pull/446)

### Fixed

* fix(entity): explicitly set userId to null in OAuthAccessToken constructor by [@dq042000](https://github.com/dq042000) in [#437](https://github.com/dotkernel/api/pull/437)

## 6.0.0 - 2025-06-05

### Changed

* Issue [#330](https://github.com/dotkernel/api/issues/330): Move common logic to `Core` module by [@alexmerlin](https://github.com/alexmerlin) in [#358](https://github.com/dotkernel/api/pull/358)
* Issue [#384](https://github.com/dotkernel/api/issues/384): Refactored handlers by [@alexmerlin](https://github.com/alexmerlin) in [#385](https://github.com/dotkernel/api/pull/385)
* Issue [#388](https://github.com/dotkernel/api/issues/388): Inject `InputFilter`s in handlers by [@alexmerlin](https://github.com/alexmerlin) in [#389](https://github.com/dotkernel/api/pull/389)
* Issue [#394](https://github.com/dotkernel/api/issues/394): Service refactoring by [@alexmerlin](https://github.com/alexmerlin) in [#396](https://github.com/dotkernel/api/pull/396)
* Issue [#392](https://github.com/dotkernel/api/issues/392): Refresh Postman documentation by [@Howriq](https://github.com/Howriq) in [#400](https://github.com/dotkernel/api/pull/400)
* Issue [#397](https://github.com/dotkernel/api/issues/397): Autogenerate `OAuth2` keys when cloning the project by [@alexmerlin](https://github.com/alexmerlin) in [#398](https://github.com/dotkernel/api/pull/398)
* Issue [#399](https://github.com/dotkernel/api/issues/399): Merge `Admin.Core` into `API.Core` by [@alexmerlin](https://github.com/alexmerlin) in [#401](https://github.com/dotkernel/api/pull/401)
* Issue [#411](https://github.com/dotkernel/api/issues/411): Updated logic in `ContentNegotiationMiddleware` by [@alexmerlin](https://github.com/alexmerlin) in [#413](https://github.com/dotkernel/api/pull/413)
* Issue [#414](https://github.com/dotkernel/api/issues/414): Replaced `Twig` with custom templating solution by [@alexmerlin](https://github.com/alexmerlin) in [#419](https://github.com/dotkernel/api/pull/419)
* Issue [#415](https://github.com/dotkernel/api/issues/415): Increased `PHPStan` level to `8` by [@alexmerlin](https://github.com/alexmerlin) in [#421](https://github.com/dotkernel/api/pull/421)
* Issue [#426](https://github.com/dotkernel/api/issues/426): Switch from `UUID4` to `UUID7` by [@alexmerlin](https://github.com/alexmerlin) in [#427](https://github.com/dotkernel/api/pull/427)
* Update pre-run.sh by [@alexmerlin](https://github.com/alexmerlin) in [#404](https://github.com/dotkernel/api/pull/404)
* Update `GetIndexResourceHandler.php` by [@alexmerlin](https://github.com/alexmerlin) in [#408](https://github.com/dotkernel/api/pull/408)
* Update local.php.dist by [@arhimede](https://github.com/arhimede) in [#409](https://github.com/dotkernel/api/pull/409)
* Update qodana_code_quality.yml by [@arhimede](https://github.com/arhimede) in [#416](https://github.com/dotkernel/api/pull/416)

### Added

* Issue [#292](https://github.com/dotkernel/api/issues/292): GHA for database validation by [@MarioRadu](https://github.com/MarioRadu) in [#380](https://github.com/dotkernel/api/pull/380)
* Issue [#314](https://github.com/dotkernel/api/issues/314): Implemented `mezzio/mezzio-problem-details` by [@alexmerlin](https://github.com/alexmerlin) in [#402](https://github.com/dotkernel/api/pull/402)
* Issue [#383](https://github.com/dotkernel/api/issues/383): Implemented route grouping by [@alexmerlin](https://github.com/alexmerlin) in [#391](https://github.com/dotkernel/api/pull/391)
* Issue [#390](https://github.com/dotkernel/api/issues/390): Implemented `ResourceProviderMiddleware` and added `ResourceGuardInterface` by [@alexmerlin](https://github.com/alexmerlin) in [#403](https://github.com/dotkernel/api/pull/403)
* Add documentation links by [@arhimede](https://github.com/arhimede) in [#382](https://github.com/dotkernel/api/pull/382)

### Deprecated

* Nothing

### Removed

* Issue [#414](https://github.com/dotkernel/api/issues/414): Removed `Twig` integration by [@alexmerlin](https://github.com/alexmerlin) in [#419](https://github.com/dotkernel/api/pull/419)
* Issue [#417](https://github.com/dotkernel/api/issues/417): `AuthenticationMiddleware` no longer extends `AuthenticationMiddleware` from `mezzio/mezzio-authentication` by [@alexmerlin](https://github.com/alexmerlin) in [#418](https://github.com/dotkernel/api/pull/418)

### Fixed

* Issue [#410](https://github.com/dotkernel/api/issues/410): Fixed error handling by [@alexmerlin](https://github.com/alexmerlin) in [#412](https://github.com/dotkernel/api/pull/412)
* Issue [#422](https://github.com/dotkernel/api/issues/422): Split the `/security/token` endpoint into two separate endpoints by [@alexmerlin](https://github.com/alexmerlin) in [#423](https://github.com/dotkernel/api/pull/423)
* Core sync by [@alexmerlin](https://github.com/alexmerlin) in [#425](https://github.com/dotkernel/api/pull/425)

## 5.3.0 - 2025-01-08

### Changed

* Issue [#352](https://github.com/dotkernel/api/issues/352): Updated PHPStan memory limit by [@bidi47](https://github.com/bidi47) in [#353](https://github.com/dotkernel/api/pull/353)
* Issue [#360](https://github.com/dotkernel/api/issues/360): Restricted `Qodana` to supported PHP versions by [@alexmerlin](https://github.com/alexmerlin) in [#361](https://github.com/dotkernel/api/pull/361)
* Issue [#366](https://github.com/dotkernel/api/issues/366): Updated laminas/laminas-coding-standard to latest version by [@MarioRadu](https://github.com/MarioRadu) in [#367](https://github.com/dotkernel/api/pull/367)
* Issue [#362](https://github.com/dotkernel/api/issues/362): Updated user status, removed isDeleted by [@bidi47](https://github.com/bidi47) in [#359](https://github.com/dotkernel/api/pull/359)
* Issue [#355](https://github.com/dotkernel/api/issues/355): Upgraded to dotkernel/dot-mail 5.0 by [@MarioRadu](https://github.com/MarioRadu) in [#368](https://github.com/dotkernel/api/pull/368)
* Issue [#374](https://github.com/dotkernel/api/issues/374): Updated anonymization by [@bidi47](https://github.com/bidi47) in [#375](https://github.com/dotkernel/api/pull/375)
* Issue [#374](https://github.com/dotkernel/api/issues/372): Ignore development files on production env by [@MarioRadu](https://github.com/MarioRadu) in [#373](https://github.com/dotkernel/api/pull/373)
* Update security.txt file by [@arhimede](https://github.com/arhimede) in [#371](https://github.com/dotkernel/api/pull/371)

### Added

* Issue [#363](https://github.com/dotkernel/api/issues/363): Composer post install script which copies config files by [@MarioRadu](https://github.com/MarioRadu) in [#364](https://github.com/dotkernel/api/pull/364)

### Deprecated

* Nothing

### Removed

* Issue [#354](https://github.com/dotkernel/api/issues/354): Removed laminas/laminas-http dependency by [@MarioRadu](https://github.com/MarioRadu) in [#356](https://github.com/dotkernel/api/pull/356)
* Issue [#362](https://github.com/dotkernel/api/issues/362): Updated user status, removed isDeleted by [@bidi47](https://github.com/bidi47) in [#359](https://github.com/dotkernel/api/pull/359)
* Issue [#369](https://github.com/dotkernel/api/issues/369): Delete `config/autoload/mail.global.php.dist` by [@alexmerlin](https://github.com/alexmerlin) in [#370](https://github.com/dotkernel/api/pull/370)
* Removed composer script `post-create-project-cmd` by [@MarioRadu](https://github.com/MarioRadu) in [#376](https://github.com/dotkernel/api/pull/376)

### Fixed

* Issue [#377](https://github.com/dotkernel/api/issues/377): Fixed `.github/workflows/codecov.yml` by [@alexmerlin](https://github.com/alexmerlin) in [#378](https://github.com/dotkernel/api/pull/378)

## 5.2.0 - 2024-11-19

### Changed

* Issue [#347](https://github.com/dotkernel/api/issues/324): Replaced `analyze` with `static-analysis` in `check` command in composer.json by [@MarioRadu](https://github.com/MarioRadu) in [#351](https://github.com/dotkernel/api/pull/351)
* Issue [#347](https://github.com/dotkernel/api/issues/324): Replaced `analyze` with `static-analysis` command in composer.json by [@MarioRadu](https://github.com/MarioRadu) in [#350](https://github.com/dotkernel/api/pull/350)

### Added

* Issue [#347](https://github.com/dotkernel/api/issues/324): Implemented PHPStan by [@MarioRadu](https://github.com/MarioRadu) in [#349](https://github.com/dotkernel/api/pull/349)

### Deprecated

* Nothing

### Removed

* Issue [#347](https://github.com/dotkernel/api/issues/347): Removed psalm by [@MarioRadu](https://github.com/MarioRadu) in [#348](https://github.com/dotkernel/api/pull/348)

### Fixed

* Nothing

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

* Issue [#311](https://github.com/dotkernel/api/issues/311): Upgraded `dot-errorhandler` to `4.x` by [@alexmerlin](https://github.com/alexmerlin) in [#312](https://github.com/dotkernel/api/pull/312)

### Added

* Issue [#168](https://github.com/dotkernel/api/issues/168): OpenAPI documentation by [@alexmerlin](https://github.com/alexmerlin) in [#306](https://github.com/dotkernel/api/pull/306)
* Issue [#309](https://github.com/dotkernel/api/issues/309): psr-container-doctrine 5.2.1 support and refactoring modules configuration by [@cPintiuta](https://github.com/cPintiuta) in [#309](https://github.com/dotkernel/api/pull/309)

### Deprecated

* Nothing

### Removed

* Issue [#313](https://github.com/dotkernel/api/issues/313): Remove `config` dependency from handlers. by [@alexmerlin](https://github.com/alexmerlin) in [#315](https://github.com/dotkernel/api/pull/315)

### Fixed

* Issue [#303](https://github.com/dotkernel/api/issues/303): fix content type, special case for multipart/form-data by [@cPintiuta](https://github.com/cPintiuta) in [#304](https://github.com/dotkernel/api/pull/304)

## 5.0.0 - 2024-07-01

### Changed

* Refactor: Transfer responsibility from handlers to services. by [@alexmerlin](https://github.com/alexmerlin) in [#272](https://github.com/dotkernel/api/pull/272)
* Issue [#169](https://github.com/dotkernel/api/issues/169): API deprecation refactoring by [@MarioRadu](https://github.com/MarioRadu) in [#291](https://github.com/dotkernel/api/pull/291)
* Issue [#264](https://github.com/dotkernel/api/issues/264): Bump for doctrine orm 2 -> 3, dbal 3 -> 4 through roave psr container by [@cPintiuta](https://github.com/cPintiuta) in [#283](https://github.com/dotkernel/api/pull/283)
* Issue [#266](https://github.com/dotkernel/api/issues/266): Replaced annotation-based dependency injection with attribute-based dependency injection by [@alexmerlin](https://github.com/alexmerlin) in [#280](https://github.com/dotkernel/api/pull/280)
* Issue [#295](https://github.com/dotkernel/api/issues/295): ContentNegotiationMiddleware: Make `$config` readonly by [@alexmerlin](https://github.com/alexmerlin) in [#296](https://github.com/dotkernel/api/pull/296)
* Updated license file by [@arhimede](https://github.com/arhimede) in [#282](https://github.com/dotkernel/api/pull/282)
* Update README.md by [@arhimede](https://github.com/arhimede) in [#290](https://github.com/dotkernel/api/pull/290)
* Update qodana_code_quality.yml by [@arhimede](https://github.com/arhimede) in [#294](https://github.com/dotkernel/api/pull/294)

### Added

* Issue [#169](https://github.com/dotkernel/api/issues/169): Implemented API evolution pattern by [@MarioRadu](https://github.com/MarioRadu) in [#285](https://github.com/dotkernel/api/pull/285)
* Added missing factory spec for ErrorReportHandler. local.php.dist: removed an unnecessary use statement by [@alexmerlin](https://github.com/alexmerlin) in [#284](https://github.com/dotkernel/api/pull/284)
* Added version in home handler by [@arhimede](https://github.com/arhimede) in [#287](https://github.com/dotkernel/api/pull/287)

### Deprecated

* Nothing

### Removed

* Nothing

### Fixed

* Issue [#277](https://github.com/dotkernel/api/issues/277): Sorted routes by name by [@alexmerlin](https://github.com/alexmerlin) in [#278](https://github.com/dotkernel/api/pull/278)
