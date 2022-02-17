# Invoice App
Invoicing application that returns user sum of user documents from csv file

## Table of content
- [Folder Structure](#folder-structure)
- [Project Setup](#project-setup)
    - [Requirements](#requiremеnts)
    - [Installation](#installation)
    - [Serving the application](#serve)
- [Configuration](#configuration)
- [Coding style](./doc/STYLE.md)
- [Testing](#testing)
    - [Unit Tests](#unit-tests)
    - [Integration Tests](#integration-tests)
- [Linting](#linting)
- [Code Analyser](#code-analyser)
- [CopyPaste Detector](#copypaste-detector)


## Folder structure
```
├── app
│   ├── Controller - contains queue commands
│   ├── Models - database data
├── config - routes, dependencies
├── public - application entry point
├── resources - css, js
└── tests
```

## Project Setup

### Requiremеnts

- In order to run the application you need php ^7.3|^8.0
- In order to install the application you need [composer](#https://getcomposer.org/doc/)
- In order to check application specific extensions use ` composer check-platform-reqs`

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
