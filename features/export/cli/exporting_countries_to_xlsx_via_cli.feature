@managing_countries
Feature: exporting countries to xlsx-file
  In order to have my countries exported to an external target
  As a Developer
  I want to be able to export country data to xlsx file from the commandline

  Background:
    Given I have a working command-line interface

  @cli_importer_exporter
  Scenario: Exporting countries to xlsx-file
    When I export "country" data as "xlsx" to the file "countries_export.xlsx" with the cli-command
    Then I should see "Exported" in the output
