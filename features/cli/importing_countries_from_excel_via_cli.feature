@managing_countries
Feature: Importing countries from excel with the command-line-interface
    In order to have my countries from external source
    As an Developer
    I want to be able to import data from excel file from the commandline

    Background:
       Given I have a working command-line-interface

    @cli_importer
    Scenario: Importing defined countries with the cli-command
        When I import "country" data from excel file "countries.xlsx" file with the cli-command
        Then I should see "Successfully imported" in the output
        And I should have at least the following country ids in the database:
          | DE |
          | CH |
