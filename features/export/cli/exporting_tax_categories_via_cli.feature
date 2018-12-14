@managing_tax_categories
Feature: exporting tax categories to csv-file
  In order to have my tax categories exported to an external target
  As a Developer
  I want to be able to export tax categories data to csv file from the commandline

  Background:
    Given I have a working command-line interface
    And the store has a tax category "cloth"
    And this tax category name is "Cloth"
    And this tax category description is "Shirts and Jeans"

  @cli_importer_exporter
  Scenario: Exporting tax categories to csv-file
    When I export "tax_category" data as "csv" to the file "tax_categories_export.csv" with the cli-command
    Then I should see "Exported" in the output
    And I should see in the file:
      | Code  | Name  | Description      |
      | cloth | Cloth | Shirts and Jeans |
