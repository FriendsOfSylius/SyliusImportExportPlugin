@managing_products
Feature: Import Products with attributes from grid
  In order to have my products exported to an external target
  As an Administrator
  I want to be able to import product data and her attributes to csv file from backOffice

  Background:
    Given I am logged in as an administrator
    Given the store has locale "en_US"
    And the store has a text product attribute "Attribute text"
    And the store has a textarea product attribute "Attribute textarea"
    And the store has a percent product attribute "Attribute percent"

  @ui
  Scenario: Exporting products should export all of them
    Given I am on product import page
    And I import data from "products_attr.csv" csv file
    Then I should see a notification that the import was successful
    When I browse products
    Then I should see 2 products in the list
    Then the product "Product 1" should appear in the registry
    And attribute "Attribute text" of product "Product 1" should be "Banana" in "en_US"
