@managing_products
Feature: Export Products from grid
  In order to have my products exported to an external target
  As an Administrator
  I want to be able to export product data to csv file from backOffice

  Background:
    Given I am logged in as an administrator
    And the store has a product "T-shirt cool"

  @ui
  Scenario: Exporting products should export all of them
    When I open the product admin index page
    And I should see 1 products in the list
    Then I go to "/admin/export/sylius.product/csv" homepage
    And response should contain "Code,Locale,Name,Description,Short_description,Meta_description,Meta_keywords,Main_taxon,Taxons,Channels,Enabled"
    And response should contain 'T_SHIRT_COOL,en_US,"T-shirt cool",,,,,,,,1'
