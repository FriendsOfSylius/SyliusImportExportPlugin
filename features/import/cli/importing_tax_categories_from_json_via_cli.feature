@managing_tax_categories
Feature: Importing tax categories from json with the command-line interface
    In order to have my tax categories from external source
    As an Developer
    I want to be able to import data from json file from the commandline

    Background:
       Given I have a working command-line interface

    @cli_importer_exporter
    Scenario: Importing defined tax categories with the cli-command
        When I import "tax_category" data from json file "tax_categories.json" file with the cli-command
        Then I should see "Imported" in the output
        And I should have at least the following tax categories ids in the database:
          | BOOKS |
          | CARS  |
