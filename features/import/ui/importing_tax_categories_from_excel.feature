@managing_tax_categories
Feature: Importing tax categories from excel with the user interface
    In order to have my tax categories imported from an external source
    As an Administrator
    I want to be able to import data from excel file

    Background:
        Given I am logged in as an administrator

    @ui
    Scenario: Importing defined tax categories
        When I browse all tax categories
        And I import tax category data from "tax_categories.xlsx" excel file
        Then I should see a notification that the import was successful
        And I should see 2 tax categories in the list
        And the tax category "books" should appear in the registry
        And the tax category "cars" should appear in the registry
