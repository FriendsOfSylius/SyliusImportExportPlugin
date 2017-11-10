<p align="center">
    <a href="http://sylius.org" target="_blank">
        <img src="http://demo.sylius.org/assets/shop/img/logo.png" />
    </a>
</p>
<h1 align="center">FOSSyliusImportExportPlugin</h1>
<p align="center">
    <a href="https://packagist.org/packages/friendsofsylius/sylius-import-export-plugin" title="License">
        <img src="https://img.shields.io/packagist/l/friendsofsylius/sylius-import-export-plugin.svg" />
    </a>
    <a href="https://packagist.org/packages/friendsofsylius/sylius-import-export-plugin" title="Version">
        <img src="https://img.shields.io/packagist/v/friendsofsylius/sylius-import-export-plugin.svg" />
    </a>
    <a href="http://travis-ci.org/FriendsOfSylius/SyliusImportExportPlugin" title="Build status">
        <img src="https://img.shields.io/travis/FriendsOfSylius/SyliusImportExportPlugin/master.svg" />
    </a>
    <a href="https://scrutinizer-ci.com/g/FriendsOfSylius/SyliusImportExportPlugin/" title="Scrutinizer">
        <img src="https://img.shields.io/scrutinizer/g/FriendsOfSylius/SyliusImportExportPlugin.svg" />
    </a>
</p>

## Installation

1. Require relevant portphp format support

  - Run `composer require portphp/csv --no-update` to add CSV format support
  - Run `composer require portphp/excel --no-update` to add Excel format support

2. Require and install the plugin

  - Run `composer require friendsofsylius/sylius-import-export-plugin --dev`

## Usage

### Available importer types

* country (csv, excel)
* payment_method (csv, excel)
* tax_category (csv, excel)

## Example import files

See the fixtures in the Behat tests: `tests/Behat/Resources/fixtures`

### UI

For all available importers, a form to upload files is automatically injected into the relevant
admin overview panel using the event hook system, ie. `admin/tax-categories/`.

### CLI commands

  - Get list of available importers

    ```bash
    $ bin/console sylius:import
    ```

  - Import a file using the `tax_category` importer

    ```bash
    $ bin/console sylius:import tax_category my/tax/categories/csv/file.csv --format=csv
    ```

## Development

### Adding new importer types

  - Notes
  
    * Replace `foo` with the name of the type you want to implement in the following examples.
    * Replace `bar` with the name of the format you want to implement in the following examples.
    * Note it is of course also possible to implement a dedicated importer for `foo` type and format `bar`,
      in case a generic type implementation is not possible.

  - Implement the importer
 
    ```php
    // alternatively extend from AbstractImporter
    class FooImporter implements ImporterInterface
    ```

 - Define service
 
   ```yaml
    sylius.importer.foobar.:
        class: FriendsOfSylius\SyliusImportExportPlugin\Importer\FooImporter
        arguments:
            - "@sylius.factory.bar_reader"
            - "@sylius.factory.foo"
            - "@sylius.repository.foo"
            - "@sylius.manager.foo"
        tags:
            - { name: sylius.importer, type: foo, format: bar }
   ```

### Running plugin tests

  - Test application install

    ```bash
    $ (cd tests/Application && yarn install)
    $ (cd tests/Application && yarn run gulp)
    $ (cd tests/Application && bin/console assets:install web -e test)
    
    $ (cd tests/Application && bin/console doctrine:database:create -e test)
    $ (cd tests/Application && bin/console doctrine:schema:create -e test)

  - PHPUnit

    ```bash
    $ bin/phpunit
    ```

  - PHPSpec

    ```bash
    $ bin/phpspec run
    ```

  - Behat (non-JS scenarios)

    ```bash
    $ bin/behat features --tags="~@javascript"
    ```

  - Behat (JS scenarios)
 
    1. Download [Chromedriver](https://sites.google.com/a/chromium.org/chromedriver/)
    
    2. Run Selenium server with previously downloaded Chromedriver:
    
        ```bash
        $ bin/selenium-server-standalone -Dwebdriver.chrome.driver=chromedriver
        ```
    3. Run test application's webserver on `localhost:8080`:
    
        ```bash
        $ (cd tests/Application && bin/console server:run 127.0.0.1:8080 -d web -e test)
        ```
    
    4. Run Behat:
    
        ```bash
        $ bin/behat features --tags="@javascript"
        ```

### Opening Sylius with your plugin

- Using `test` environment:

    ```bash
    $ (cd tests/Application && bin/console sylius:fixtures:load -e test)
    $ (cd tests/Application && bin/console server:run -d web -e test)
    ```
    
- Using `dev` environment:

    ```bash
    $ (cd tests/Application && bin/console sylius:fixtures:load -e dev)
    $ (cd tests/Application && bin/console server:run -d web -e dev)
    ```
