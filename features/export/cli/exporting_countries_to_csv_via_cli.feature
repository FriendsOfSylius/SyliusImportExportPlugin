@managing_countries
Feature: exporting countries to csv-file
  In order to have my countries exported to an external target
  As a developer
  I want to be able to export country data to csv file from the commandline

  Background:
    Given I have a working command-line interface

  @cli_importer_exporter
  Scenario: Exporting countries to csv-file
    When I export "country" data as "csv" to the file "countries_export.csv" with the cli-command
    Then I should see "Exported" in the output
