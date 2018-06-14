@managing_countries
Feature: exporting countries to json-file
  In order to have my countries exported to an external target
  As a Developer
  I want to be able to export country data to json file from the commandline

  Background:
    Given I have a working command-line-interface

  @cli_importer_exporter
  Scenario: Exporting countries to json-file
    When I export "country" data as "json" to the file "countries_export.json" with the cli-command
    Then I should see "Exported" in the output
