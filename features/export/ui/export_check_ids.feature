@managing_countries
Feature: Export links on Countries grid
  In order to have my countries exported to an external target
  As an Administrator
  I want to be able to export country data to csv file from the commandline

  Background:
    Given I am logged in as an administrator

  @ui
  Scenario: Exporting countries should export all of them
    When I open the country admin index page
    And I import country data from "countries.csv" csv file
    Then I should see a notification that the import was successful
    And I should see 10 countries in the list
    Then I go to "/admin/export/sylius.country/csv" homepage
    And response should contain "Id;Code;Enabled"
    And response should contain "1;AD;1"
    And response should contain "13;US;1"
