@managing_products
Feature: Import Products from grid
  In order to have my products exported to an external target
  As an Administrator
  I want to be able to export product data to csv file from backOffice

  Background:
    Given I am logged in as an administrator

  @ui
  Scenario: Import products should update them
    When I open the product admin index page
    And I import product data from "products.csv" csv file
    Then I should see a notification that the import was successful
    And I import product data from "products_update.csv" csv file
    Then I should see a notification that the import was successful
    And I should see 2 products in the list
    Then I go to "/admin/export/sylius.product/csv" homepage
    And response should contain "Code;Name;Description;Short_description;Meta_description;Meta_keywords;Main_taxon"
    And response should contain '222333;"Product 2 update";"Description 2 update";"Short description 2 update";"Meta description 2 update";"Meta keywords 2 update";'
