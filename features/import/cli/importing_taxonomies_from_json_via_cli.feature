@managing_taxonomies
Feature: Importing taxonomies from json with the command-line interface
    In order to have my taxonomies from external source
    As an Developer
    I want to be able to import data from json file from the commandline

    Background:
       Given I have a working command-line interface

    @cli_importer
    Scenario: Importing defined taxonomies with the cli-command
        When I import "taxonomy" data from json file "taxonomies.json" file with the cli-command
        Then I should see "Imported" in the output
        And I should have at least the following taxonomy codes in the database:
          | taxon1 |
          | taxon2 |
          | taxon11 |
