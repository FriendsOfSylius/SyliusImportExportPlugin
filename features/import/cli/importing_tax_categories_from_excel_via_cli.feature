@managing_tax_categories
Feature: Importing tax categories from excel with the command-line interface
    In order to have my tax categories from external source
    As an Developer
    I want to be able to import data from excel file from the commandline

    Background:
       Given I have a working command-line interface

    @cli_importer_exporter
    Scenario: Importing defined tax categories with the cli-command
        When I import "tax_category" data from xlsx file "tax_categories.xlsx" file with the cli-command
        Then I should see "Imported" in the output
        And I should have at least the following tax categories ids in the database:
          | BOOKS |
          | CARS  |
