@managing_customer_groups
Feature: Importing customer-groups from json
  In order to have my customer-groups imported from an external source
  As an Administrator
  I want to be able to import data from json file

  Background:
    Given I am logged in as an administrator

  @ui
  Scenario: Importing customer-groups based on a valid json-file
    When I import customer-group data from "customer_groups.json" json file
    Then I should see a notification that the import was successful
    And I should see 2 customer groups in the list
    And I should see the customer group "premium" in the list
    And I should see the customer group "basic" in the list
