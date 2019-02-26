@managing_products
Feature: exporting products to csv-file
  In order to have my products exported to an external target
  As a developer
  I want to be able to export product data to csv file from the commandline

  Background:
    Given I have a working command-line interface

  @cli_importer_exporter
  Scenario: Exporting products to csv-file
    When I export "product" data as "csv" to the file "products_export.csv" with the cli-command
    Then I should see "Exported" in the output
