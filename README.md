<p align="center">
    <a href="http://sylius.org" target="_blank">
        <img src="http://demo.sylius.org/assets/shop/img/logo.png" />
    </a>
</p>
<h1 align="center">SyliusImportExportPlugin</h1>
<p align="center">
    <a href="https://packagist.org/packages/friendsofsylius/sylius-import-export-plugin" title="License">
        <img src="https://img.shields.io/packagist/l/friendsofsylius/SyliusImportExportPlugin.svg" />
    </a>
    <a href="https://packagist.org/packages/friendsofsylius/sylius-import-export-plugin" title="Version">
        <img src="https://img.shields.io/packagist/v/friendsofsylius/SyliusImportExportPlugin.svg" />
    </a>
    <a href="http://travis-ci.org/FriendsOfSylius/SyliusImportExportPlugin" title="Build status">
        <img src="https://img.shields.io/travis/FriendsOfSylius/SyliusImportExportPlugin/master.svg" />
    </a>
    <a href="https://scrutinizer-ci.com/g/FriendsOfSylius/SyliusImportExportPlugin/" title="Scrutinizer">
        <img src="https://img.shields.io/scrutinizer/g/FriendsOfSylius/SyliusImportExportPlugin.svg" />
    </a>
</p>

## Installation

1. Run `composer create-project friendsofsylius/SyliusImportExportPlugin ProjectName`.

2. From the plugin skeleton root directory, run the following commands:

    ```bash
    $ (cd tests/Application && yarn install)
    $ (cd tests/Application && yarn run gulp)
    $ (cd tests/Application && bin/console assets:install web -e test)
    
    $ (cd tests/Application && bin/console doctrine:database:create -e test)
    $ (cd tests/Application && bin/console doctrine:schema:create -e test)
    ```

## Usage

### Available importer types

* country (csv)
* payment_method (csv)
* tax_category (csv)

## Example import files

See the fixtures in the Behat tests: `tests/Behat/Resources/fixtures`

### UI

For all available importers, a form to upload files is automatically injected into the relevant
admin overview panel using the event hook system, ie. `admin/tax-categories/`.

### CLI commands

  - Get list if available importers

    ```bash
    $ bin/console sylius:import
    ```

  - Import a file using the `tax_category` importer

    ```bash
    $ bin/console sylius:import tax_category tests/Behat/Resources/fixtures/tax_categories.csv --format=csv
    ```

## Development

### Adding new importer types

  - Notes
  
    * Currently only CSV is supported until https://github.com/portphp/portphp/pull/63 is merged.
    * Replace `foobar` with the name of the type you want to implement in the following examples.

  - Implement the importer
 
    ```php
    class FoobarImporter extends AbstractImporter
    ```

 - Define service
 
   ```yaml
    sylius.importer.foobar.:
        class: FriendsOfSylius\SyliusImportExportPlugin\Importer\FoobarImporter
        arguments:
            - "@sylius.factory.csv_reader"
            - "@sylius.factory.payment_method"
            - "@sylius.repository.payment_method"
            - "@sylius.manager.payment_method"
        tags:
            - { name: sylius.importer, type: foobar, format: csv }
   ```

### Running plugin tests

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
