# Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

### Added

- This CHANGELOG

## [0.2.4] - 2016-12-05

### Changed

- Fixed REST parseJson Bug
- Updated responseFactory to be a parameter not a service

### Added

- test for 415 parse json error
- more documentation
- a few more services to the REST package for convenience


## [0.2.3] - 2016-12-05

### Added

- Mountable Middleware \#5
- Redirect Marshal Response Matching \#3

## [0.2.2] - 2016-12-04

Several minor changes to the system.

### Changed

- Adding server to app service dependencies
- Documentation updates and Std package update

## [0.2.1] - 2016-11-30

### Fixed

- Hotfix for fixing package.php inclusion

## [0.2.0] - 2016-11-28

### Added

- Added Evenement event listener integration to the App
- Added Pimple integration into the core. Refactored how
  the app works entirely
- AutoArgs Package - allows for symfony style action parameters
- Initial Documentation and Package Refactoring

## Changed

- Fixed bug with config defaults

### [0.1.0] - 2016-11-23

Initial Release

### Added

- Packages
- Server
- Basic Routing
- HttpApp
