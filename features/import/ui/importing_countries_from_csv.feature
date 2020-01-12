@managing_countries
Feature: Importing countries from csv
  In order to have my countries imported from an external source
  As an Administrator
  I want to be able to import data from csv file

  Background:
    Given I am logged in as an administrator

  @ui
  Scenario: Importing countries based on a valid csv-file
    When I open the country admin index page
    Then I should see an import button
    When I click an import button
    Then I should be on country import page
    When I import data from "countries.csv" csv file
    Then I should see a notification that the import was successful
    When I open the country admin index page
    Then I should see 10 countries in the list
    And I open the country admin index second page
    And I should see 3 countries in the list
    And the country "Andorra" should appear in the registry
    And the country "Belize" should appear in the registry
