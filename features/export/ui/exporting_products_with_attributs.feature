@managing_products
Feature: Export Products with attributes from grid
  In order to have my products exported to an external target
  As an Administrator
  I want to be able to export product data and her attributes to csv file from backOffice

  Background:
    Given I am logged in as an administrator
    And the store has a select product attribute "Attribute select" with values "select1" and "select2"
    And the store has a product "T-shirt cool"
    And this product has text attribute "Attribute text" with value "Banana"
    And this product has textarea attribute "Attribute textarea" with value "Banana <br /> Bananaaaa !!!"
    And this product has percent attribute "Attribute percent" with value 22%

  @ui
  Scenario: Exporting products should export all of them
    When I open the product admin index page
    And I should see 1 products in the list
    Then I go to "/admin/export/sylius.product/csv" homepage
    And response should contain "Attribute_text,Attribute_textarea,Attribute_percent"
    And response should contain 'Banana,"Banana <br /> Bananaaaa !!!",0.22'
