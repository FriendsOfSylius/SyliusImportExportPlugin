@managing_products
Feature: Import Products from grid
  In order to have my products exported to an external target
  As an Administrator
  I want to be able to import product data to csv file from backOffice

  Background:
    Given I am logged in as an administrator

  @ui
  Scenario: Import products should update them
    Given I am on product import page
    And I import data from "products.csv" csv file
    Then I should see a notification that the import was successful
    And I import data from "products_update.csv" csv file
    Then I should see a notification that the import was successful
    When I browse products
    Then I should see 2 products in the list
    And the first product on the list should have name "Product 1"
    And the last product on the list should have name "Product 2 update"
