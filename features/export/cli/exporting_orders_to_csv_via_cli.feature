@managing_orders
Feature: exporting orders to csv-file
  In order to have my orders exported to an external target
  As a Developer
  I want to be able to export order data to csv file from the commandline

  Background:
    Given I have a working command-line interface

  @cli_importer_exporter
  Scenario: Exporting orders to csv-file
    When I export "order" data as "csv" to the file "orders_export.csv" with the cli-command
    Then I should see "Exported" in the output
