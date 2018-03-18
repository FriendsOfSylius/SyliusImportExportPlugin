@managing_countries
Feature: Export links on Countries grid
  In order to have my countries exported to an external target
  As an Administrator
  I want to be able to export country data to csv file from the commandline

  Background:
    Given I am logged in as an administrator

  @ui
  Scenario: Exporting countries should generate export links on grid
    When I open the country admin index page
    Then I should see an export button
    And I should see a link to export countries to CSV
