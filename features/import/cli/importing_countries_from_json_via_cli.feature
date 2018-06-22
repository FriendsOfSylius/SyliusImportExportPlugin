@managing_countries
Feature: Importing countries from json with the command-line interface
    In order to have my countries from external source
    As a developer
    I want to be able to import data from json file from the commandline

    Background:
       Given I have a working command-line interface

    @cli_importer_exporter
    Scenario: Importing defined countries with the cli-command
        When I import "country" data from json file "countries.json" file with the cli-command
        Then I should see "Imported" in the output
        And I should have at least the following country ids in the database:
          | DE |
          | CH |
