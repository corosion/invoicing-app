# Invoice App
Invoicing application that returns user sum of user documents from csv file.<br/>
Simple monolith structure with react and api is based on [laravel/components](https://github.com/illuminate) and ispired by [TORCH by TIGHTEN](https://github.com/mattstauffer/Torch)

## Table of content
- [Folder Structure](#folder-structure)
- [Project Setup](#project-setup)
    - [Requirements](#requiremеnts)
    - [Installation](#installation)
    - [Serving the application](#serve)
    - [Development](#development)
- [Linting](#linting)
- [Code Analyser](#code-analyser)


## Folder structure
```
├── app
│   ├── Controller - contains application controlers
│   ├── Service - contains services that in out project wraps business logics
│   ├── Model - contains data models 
├── config - routes, dependencies, bootstrap
├── public - application entry point
├── resources - css, js (react components)
└── tests
```

## Project Setup

### Requiremеnts

In order to run the application you need:
- PHP ^7.3|^8.0
- PHP module `php-{dev,dom,json,mbstring,tokenizer,xml}`
- node ^12.22
- npm ^6.14
- composer ^2.0 [composer](#https://getcomposer.org/doc/)

### Installation

* Clone the project from GitHub
```
git clone git@github.com:corosion/invoicing-app.git .
cd invoicing-app
```

* Install vendor modules
```
composer install
```

### Serve

To run the application just use the following command:
```
composer serve
```

### Development

```
npm run watch
```

## Linting

[PHPCodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) is used for linting

```sh
composer run lint
```

## Code Analyser

For Static Code Analysis Atlas uses [PHPStan](https://github.com/phpstan/phpstan)

```sh
composer run analyse
```
