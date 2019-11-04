@managing_imports
Feature: Detecting the file extension to auto-set format
    In order to simplify imports
    As a developer
    I want to be able to don't select the format manually

    Background:
       Given I have a working command-line interface

    @cli_importer_exporter
    Scenario: Detecting the file extension csv with the cli-command
        When I import "country" data from file "countries.csv" file with the cli-command
        Then I should see "The csv format has been detected." in the output

    @cli_importer_exporter
    Scenario: Detecting the file extension json with the cli-command
        When I import "country" data from file "countries.json" file with the cli-command
        Then I should see "The json format has been detected." in the output

    @cli_importer_exporter
    Scenario: Detecting the file extension xlsx with the cli-command
        When I import "country" data from file "countries.xlsx" file with the cli-command
        Then I should see "The xlsx format has been detected." in the output
