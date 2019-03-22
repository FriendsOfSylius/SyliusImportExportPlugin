@managing_customers
Feature: Exporting customers to csv-file
    In order to have my customers exported to an external target
    As a Developer
    I want to be able to export customer data to csv file from the commandline

    Background:
        Given I have a working command-line interface

    @cli_importer_exporter
    Scenario: Exporting customers to csv-file
        When I export "customer" data as "csv" to the file "customers_export.csv" with the cli-command
        Then I should see "Exported" in the output
