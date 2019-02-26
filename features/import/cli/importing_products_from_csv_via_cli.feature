@managing_products
Feature: Importing products from csv with the command-line interface
    In order to have my products from external source
    As a developer
    I want to be able to import data from csv file from the commandline

    Background:
       Given I have a working command-line interface

    @cli_importer_exporter
    Scenario: Importing defined products with the cli-command
        When I import "product" data from csv file "products.csv" file with the cli-command
        Then I should see "Imported" in the output
        And I should have at least the following product ids in the database:
          | 123456 |
          | 222333 |
