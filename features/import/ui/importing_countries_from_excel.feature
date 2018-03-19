@managing_countries
Feature: Importing countries from excel
  In order to have my countries imported from an external source
  As an Administrator
  I want to be able to import data from excel file

  Background:
    Given I am logged in as an administrator

  @ui
  Scenario: Importing countries based on a valid excel-file
    When I open the country admin index page
    And I import country data from "countries.xlsx" excel file
    Then I should see a notification that the import was successful
    And I should see 2 countries in the list
    And the country "Germany" should appear in the registry
    And the country "Switzerland" should appear in the registry
