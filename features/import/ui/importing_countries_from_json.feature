@managing_countries
Feature: Importing countries from json
  In order to have my countries imported from an external source
  As an Administrator
  I want to be able to import data from json file

  Background:
    Given I am logged in as an administrator

  @ui
  Scenario: Importing countries based on a valid json-file
    When I open the country admin index page
    Then I should see an import button
    When I click an import button
    Then I should be on country import page
    When I import data from "countries.json" json file
    Then I should see a notification that the import was successful
    When I open the country admin index page
    Then I should see 2 countries in the list
    And the country "Germany" should appear in the registry
    And the country "Switzerland" should appear in the registry
