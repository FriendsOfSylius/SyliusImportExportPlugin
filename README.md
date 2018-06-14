<p align="center">
    <a href="http://sylius.org" target="_blank">
        <img src="https://demo.sylius.com/assets/shop/img/logo.png" />
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
  - Run `composer require portphp/excel --no-update` to add Excel format support (also install the `zip` PHP extension)
  - Run `composer require dcarbone/json-writer-plus --no-update` to add JSON export support
  - Run `composer require pcrov/jsonreader --no-update` to add JSON import support

2. Require and install the plugin

  - Run `composer require friendsofsylius/sylius-import-export-plugin --dev`

3. Add plugin dependencies to your AppKernel.php file:

````php
public function registerBundles()
{
    return array_merge(parent::registerBundles(), [
        ...
        new FriendsOfSylius\SyliusImportExportPlugin\FOSSyliusImportExportPlugin(),
    ]);
}
````

## Configuration

### Application configuration:

```yaml
fos_sylius_import_export:
    importer:
      # set to false to not add an upload form to the entity overview pages
        web_ui:               true
        # set to an integer value to flush the object manager in regular intervals
        batch_size:           false
        # if incomplete rows (ie. missing required fields) should be considered failures
        fail_on_incomplete:   false
        # if to stop the import process in case of a failure
        stop_on_failure:      false
    exporter:
      # set to false to not add export buttons
        web_ui:               true      
```

### Routing configuration (only necessary if `web_ui` is set to `true`):

```yaml
sylius_import_export:
    resource: "@FOSSyliusImportExportPlugin/Resources/config/routing.yml"
    prefix: /admin
```

## Usage

### Available importer types

* country (csv, excel, json)
* customer_group (csv, excel, json)
* payment_method (csv, excel, json)
* tax_category (csv, excel, json)

### Available exporter types

* country (csv, excel, json)
* order (csv, excel, json)

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
   
  - Export data of resources to file using `country` exporter
    ```bash
    $ bin/console sylius:export country my/countries/export/csv/file.csv --format=csv
    ```

## Development

### Adding new importer types

  #### Notes
  
  - Replace `foo` with the name of the type you want to implement in the following examples.
  - Replace `bar` with the name of the format you want to implement in the following examples.
  - Note it is of course also possible to implement a dedicated importer for `foo` type and format `bar`,
    in case a generic type implementation is not possible.

#### Adding a ResourceImporter

##### Define Importer-Service for Generic Importer in services_bar.yml with ResourceImporter
 
```yaml
sylius.importer.foo.bar:
    class: FriendsOfSylius\SyliusImportExportPlugin\Importer\ResourceImporter
    arguments:
        - "@sylius.factory.bar_reader"
        - "@sylius.manager.foo"
        - "@sylius.processor.foo"
        - "@sylius.importer.result"
    tags:
        - { name: sylius.importer, type: foo, format: csv }
```
  
##### Alternatively implement a custom ResourceImporter _FooImporter_

```php
class FooImporter implements ImporterInterface
```

##### Define service instead of the above mentioned

```yaml
sylius.importer.foo.bar:
  class: FriendsOfSylius\SyliusImportExportPlugin\Importer\FooImporter
  arguments:
      - "@sylius.factory.bar_reader"
      - "@sylius.manager.foo"
      - "@sylius.processor.foo"
      - "@sylius.importer.result"
  tags:
      - { name: sylius.importer, type: foo, format: bar }
```

#### Adding a ResourceProcessor

##### Define processor service with generic ResourceProcessor in services.yml

```yaml
sylius.processor.foo:
    class: FriendsOfSylius\SyliusImportExportPlugin\Processor\ResourceProcessor
    arguments:
        - "@sylius.factory.foo"
        - "@sylius.repository.foo"
        - "@property_accessor"
        - "@sylius.importer.metadata_validator"
        - ["HeaderKey0", "HeaderKey1", "HeaderKey2"]
```

The fourth parameter represents the Headers of the data to import. For csv-files this would be the headers defined in 
its first line. These HeaderKeys have to be equal to the fields in the resource to import if the generic
ResourceProcessor is used, since the Keys are used for building dynamic Methodnames
    

##### Alternatively implement a custom ResourceProcessor _FooProcessor_
 
```php
class FooProcessor implements ResourceProcessorInterface
```
##### Define processor service with _FooProcessor_ in services.yml instead of the above mentioned generic one
 
```yaml
 sylius.processor.tax_categories:
     class: FriendsOfSylius\SyliusImportExportPlugin\Processor\FooProcessor
     arguments:
         - "@sylius.factory.foo"
         - "@sylius.repository.foo"
         - "@sylius.importer.metadata_validator"
         - ["HeaderKey0", "HeaderKey1", "HeaderKey2"]
```

#### Validating Metadata
Each Processor has defined mandatory 'HeaderKeys'. For basic validation of these HeaderKeys you can use 
"@sylius.importer.metadata_validator". Of course it is also possible to implement you own Validator, by implementing the 
MetadataValidatorInterface and injecting it in your FooProcessor instead of the generic one.

### Defining new Exporters
#### Notes
  
  - Replace `foo` with the name of the type you want to implement in the following examples.
  - Replace `bar` with the name of the format you want to implement in the following examples.
  - Note it is of course also possible to implement a dedicated exporter for `foo` type and format `bar`,
    in case a generic type implementation is not possible.

### Exporters
#### Adding a ResourceExporter

Define your ResourceExporter in services_bar.yml (at the moment only csv is supported for export)

```yaml
  sylius.exporter.foo.bar:
     class: FriendsOfSylius\SyliusImportExportPlugin\Exporter\ResourceExporter
     arguments:
        - "@sylius.exporter.bar_writer"
        - "@sylius.exporter.pluginpool.foo"
        - ["HeaderKey0", "HeaderKey1" ,"HeaderKey2"]
        - "@sylius.exporters_transformer_pool" # Optional
     tags:
        - { name: sylius.exporter, type: app.foo, format: bar }
```

Note that `app.foo` is the alias as you have named your resource:

```yaml
sylius_resource:
    resources:
        app.foo:
```

Define the PluginPool for your ResourceExporter in services.yml

```yaml
# PluginPools for Exporters. Can contain multiple Plugins
  sylius.exporter.pluginpool.foo:
      class: FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\PluginPool
      arguments:
          - ["@sylius.exporter.plugin.resource.foo"]
          - ["HeaderKey0", "HeaderKey1" ,"HeaderKey2"]
```

Define the Plugin for your FooResource in services.yml

```yaml
  # Plugins for Exporters
  sylius.exporter.plugin.resource.foo:
      class: FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\ResourcePlugin
      arguments:
          - "@sylius.repository.foo"
          - "@property_accessor"
          - "@doctrine.orm.entity_manager"
```

In case you want to use the grid filters (in the admin) to filter your output, add to your routing:

```yaml
app_export_data_foo:
    path: /admin/export/sylius.resource/{format}
    methods: [GET]
    defaults:
        resource: sylius.foo
        _controller: sylius.controller.export_data:exportAction
        _sylius:
            filterable: true
            grid: sylius_admin_foo # Name of defined grid here
```

In case you don't add it, the UI exporters will still function. They will simply load all data of that resource for the export (similar as CLI).

### A real example
Define the Countries-Exporter in services_csv.yml
```yaml
  sylius.exporter.countries.csv:
     class: FriendsOfSylius\SyliusImportExportPlugin\Exporter\ResourceExporter
     arguments:
        - "@sylius.exporter.csv_writer"
        - "@sylius.exporter.pluginpool.countries"
        - ["Id", "Code" ,"Enabled"]
        - "@sylius.exporters_transformer_pool" # Optional
     tags:
        - { name: sylius.exporter, type: sylius.country, format: csv }
```

Define the PluginPool for the Countries-Exporter in services.yml

```yaml
# PluginPools for Exporters. Can contain multiple Plugins
  sylius.exporter.pluginpool.countries:
      class: FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\PluginPool
      arguments:
          - ["@sylius.exporter.plugin.resource.country"]
          - ["Id", "Code" ,"Enabled"]
```

Define the Plugin for the Country-Resource in services.yml

```yaml
  # Plugins for Exporters
  sylius.exporter.plugin.resource.country:
      class: FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\ResourcePlugin
      arguments:
          - "@sylius.repository.country"
          - "@property_accessor"
          - "@doctrine.orm.entity_manager"

```

The exporter will instantly be available as a exporter for the command line.

   ```bash
   $ bin/console sylius:export country my/countries/export/csv/file.csv --format=csv
   ```
   
Optional add the routing:

```yaml
app_export_data_country:
    path: /admin/export/sylius.country/{format}
    methods: [GET]
    defaults:
        resource: sylius.country
        _controller: sylius.controller.export_data:exportAction
        _sylius:
            filterable: true
            grid: sylius_admin_country
```   
   

### PluginPool
The idea behind the plugin pool is, to be able to have different kind of plugins, which could possibly
provide data based on a custom sql that queries additional data for the exported resource, such as the 
preferred brand of a customer. 
At the moment there are only 'ResourcePlugin's, which allow the complete export of all data of one resource at the moment.
With the provided keys you can influence which fields of a resource are exported.

### Running plugin tests

  - Test application install

    ```bash
    $ composer install
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
