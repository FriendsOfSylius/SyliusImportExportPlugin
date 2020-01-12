@managing_payment_methods
Feature: Importing payment methods from csv with the user interface
    In order to have my payment methods from external source
    As an Administrator
    I want to be able to import data from csv file

    Background:
        Given I am logged in as an administrator

    @ui
    Scenario: Importing payment methods based on a valid csv-file
        Given I am on payment method import page
        When I import data from "payment-methods.csv" csv file
        Then I should see a notification that the import was successful
        When I browse payment methods
        Then I should see 2 payment methods in the list
        And the payment method "Offline" should be in the registry
        And the payment method "PayPal" should be in the registry
