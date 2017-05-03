# Change Log

## Unreleased

### Added

- wrap middleware to wrap PSR-7 style middleware #23
- Middleware documentation

## 0.3.1 - 2017-05-02

### Added

- HttpServiceProvider which provides basic services of the library
- serveStatic middleware #19
- Request Path #20

## 0.3.0 - 2017-03-18

### Changed

Completely re-tooled the entire package. This no longer holds any micro framework code. [Krak\\Lava](https://github.com/krakphp/lava) is the replacement for this.

- Converted functions for dispatching/server/response-factory into classes with proper interfaces since you typically won't need to make your own very frequently
- Removed all framework related code
- Removed all unneeded composer dependencies (only nikic/iter remains)
- Routing is now a lot simpler and not dependent on any libraries like `krak/mw`. They are simply value objects now.
- This package is now at `krak/http` instead of `krak/mw-http`

### Added

- This CHANGELOG
- ResponseFactoryStore for easily storing and utilizing response factories.

## 0.2.4 - 2016-12-05

### Changed

- Fixed REST parseJson Bug
- Updated responseFactory to be a parameter not a service

### Added

- test for 415 parse json error
- more documentation
- a few more services to the REST package for convenience


## 0.2.3 - 2016-12-05

### Added

- Mountable Middleware \#5
- Redirect Marshal Response Matching \#3

## 0.2.2 - 2016-12-04

Several minor changes to the system.

### Changed

- Adding server to app service dependencies
- Documentation updates and Std package update

## 0.2.1 - 2016-11-30

### Fixed

- Hotfix for fixing package.php inclusion

## 0.2.0 - 2016-11-28

### Added

- Added Evenement event listener integration to the App
- Added Pimple integration into the core. Refactored how
  the app works entirely
- AutoArgs Package - allows for symfony style action parameters
- Initial Documentation and Package Refactoring

## Changed

- Fixed bug with config defaults

## 0.1.0 - 2016-11-23

Initial Release

### Added

- Packages
- Server
- Basic Routing
- HttpApp
