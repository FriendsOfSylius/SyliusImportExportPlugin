@managing_payment_methods
Feature: Importing payment methods from excel with the user interface
    In order to have my payment methods from external source
    As an Administrator
    I want to be able to import data from excel file

    Background:
        Given I am logged in as an administrator

    @ui
    Scenario: Importing payment methods based on a valid excel file
        When I browse payment methods
        And I import payment methods data from "payment-methods.xlsx" xlsx file
        Then I should see a notification that the import was successful
        And I should see 2 payment methods in the list
        And the payment method "Offline" should be in the registry
        And the payment method "PayPal" should be in the registry
