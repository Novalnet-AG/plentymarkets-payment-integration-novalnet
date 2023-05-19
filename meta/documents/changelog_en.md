# Release Notes for Novalnet

## v12.0.2 (2023-05-19)

### Fix

- Error message 'Missing Input Data' cleared when the address fields were filled
- Credit/Debit Cards iframe loading is optimized to avoid page load delay
- cURL error log during the payment process
- Data provider's file should be executed only for Novalnet payments while displaying the payments on the checkout page

### Remove

- Google Pay button theme configuration

## v12.0.1 (2022-12-02)

### Fix

- Page load delay for Credit/Debit Cards iframe
- Rejected error messages were displayed on the checkout page, when the order creation option is deactivated

### Removed

- "isBackendActive" function as per the coding standard review

## v12.0.0 (2022-10-20)

### New

- Payment plugin is optimized to support the assistant structure with a new v2 API structure, additional payment methods integration, and enhanced usability

## v2.3.1 (2022-06-28)

### Enhanced

- Compatible for PHP 8.0 version

## v2.3.0 (2022-06-01)

### New

- Implemented Apple Pay, Online bank transfer, Bancontact, Multibanco, Post-finance Card and Post-finance E-Finance payment methods
- Order creation process handled from the shop admin panel
- Payment filter added to trigger events for Novalnet transactions

### Enhanced

- Updated payment logo for Sofort payment

## v2.2.4 (2022-03-30)

### Enhanced

- Orders initially placed with default payment methods, reinitiate payment option will be displayed during the change payment method process
- Code optimization in the confirmation page to avoid the 404 error

## v2.2.3 (2022-02-18)

### Fix

- Credit note refund for reinitiate payment option
- Language translation handled in the order success page
- Order History view icon display
- Currency conversion handled while using events in the shop backend
- Restricted double booking transaction during communication break
- B2B guarantee payments now supported for users from Denmark and France

## v2.2.2 (2021-12-10)

### Enhanced

- Multilanguage support for Payment names, Payment descriptions, and Payment form (template) as per shop default

### Fix

- Transaction amount mismatch for Credit Card payment during VAT calculation for Non-EU countries
- Payment credit information not updated in the shop system during payment method change

## v2.2.1 (2021-10-26)

### Fix

- Refund details displayed for the child order during Refund via Credit note for the payments - Credit Card, Direct Debit SEPA, Direct Debit SEPA with payment guarantee, Invoice, Invoice with payment guarantee, Instant Bank Transfer, iDEAL, PayPal, eps, giropay and Przelewy24
- Error message displayed in the order confirmation page for cancelled orders, when clicking the staus link in the End customer email

## v2.2.0 (2021-09-30)

### New

- Implemented Change payment method and Initiate payment feature for the failure and rejection orders

## v2.1.1 (2021-09-08)

### Fix

- Error message display for rejected transactions

## v2.1.0 (2021-08-20)

### New

- Implemented enforce 3D secure payment for credit card for countries outside EU
- Implemented Payment duration for Prepayment

### Enhanced

- Order status has been optimized as per shop default structure
- Credit/Debit Cards payment method description
- Semantic versioning standards optimized in plugin.json
- Barzahlen payment method name and logo

### Fix

- Payment status percentage will not reduced during the Refund via Credit note and Status change event
- During the initial order status, Novalnet transaction details will display in the Invoice PDF
- Restricted double booking for redirect payments

### Removed

- Proxy server configuration
- Gateway timeout configuration
- Referrer Id configuration
- BCC field for Webhook Email notification

## v2.0.14 (2021-06-21)

### Fix

- Updated IP address to get the actual IP of Novalnet server
- Handled initial level payment types in callback process for the follow-up and communication process

## v2.0.13 (2020-06-03)

### Enhanced

- Default order creation before executing payment call which is applicable for Card and Account-Based payments. This is to avoid the missing orders during payment completion for non-return of end user if the end user's browser closed or if session timeouts at the time of payment, etc.!

## v2.0.12 (2020-04-29)

### Enhanced

- Credit note event added to the refund payments (Credit Card, Direct Debit SEPA, Direct Debit SEPA with payment guarantee, Invoice, Invoice with payment guarantee, Instant Bank Transfer, iDEAL, PayPal, eps, giropay and Przelewy24)

## v2.0.11 (2020-04-09)

### Fix

- Adjusted validation in the checkout page for First name and Last name field
- Transaction detail are not displayed in Invoice PDF and shop front order history for Invoice, Invoice with payment guarantee and Prepayment
- For Guarantee invoice payment while capturing the order, amount is not displayed in the transaction details
- Due date update notification in the shop, on activation of transaction for Invoice, Invoice with payment guarantee and Prepayment
- Basket amount is incorrectly displayed on including Tax

## v2.0.10 (2019-10-31)

### Fix

- Payment plugin adapted to avoid creating duplicate entries in the order history for the same TID

### New

- Customized date of birth field in checkout page

## v2.0.9 (2019-09-13)

### Fix

- Issue in displaying Novalnet transaction details in invoice pdf using the event OrderPdfGeneration
- Issue with activation of transaction for Invoice, Invoice with payment guarantee and Prepayment

## v2.0.8 (2019-08-30)

### New

- Display Novalnet transaction details in invoice pdf using the event OrderPdfGeneration

### Removed

- Unused migration files

## v2.0.7 (2019-06-24)

### Fix

- Adjusted payment plugin to restrict the double booking by the end-user

## v2.0.6 (2019-05-24)

### Fix

- Added IO in plugin.json

## v2.0.5 (2019-05-14)

### Enhanced

- Novalnet bank details will be displayed in invoice for on-hold transactions in Invoice, Invoice with payment guarantee and Prepayment

## v2.0.4 (2019-04-24)

### Fix

- Display payment types only for allowed countries feature

### New

- Payment method will displayed to end customer based on minimum and maximum order amount

### Enhanced

- Novalnet payment plugin has been optimized as per new testcase

## v2.0.3 (2019-04-02)

### Enhanced

- Novalnet payment module has been optimized as per new testcase

## v2.0.2 (2019-03-19)

### New

- Event creation for Authorize and Capture process for on-hold transaction payments (Credit Card, Direct Debit SEPA, Direct Debit SEPA with payment guarantee, Invoice, Invoice with payment guarantee and PayPal)
- Event creation for Refund process for the payments (Credit Card, Direct Debit SEPA, Direct Debit SEPA with payment guarantee, Invoice, Invoice with payment guarantee, Instant Bank Transfer, iDEAL, PayPal, eps, giropay and Przelewy24)
- Allowing payments for configured countries
- Customized the payment logo

### Enhanced

- Novalnet payment module has been optimized as per new testcase

### Fix

- Error message display for rejected transactions
- Amount format issue in callback notification mail

## v2.0.1 (2019-01-23)

### Fix

- Issue while updating the payment plugin from Marketplace

## v2.0.0 (2018-12-24)

### New

- Guaranteed payment pending status has been implemented
- Guaranteed payment minimum amount reduced to 9.99 EUR
- Force 3D secure process has been implemented as per predefined filters and settings in the Novalnet admin portal
- Custom checkout overlay for Barzahlen
- Transaction reference has been implemented

### Enhanced

- On-hold transaction configuration has been implemented for Credit Card, Direct Debit SEPA, Direct Debit SEPA with payment guarantee, Invoice, Invoice with payment guarantee and PayPal
- Creation of order as default before executing payment call in the shopsystem (for all redirect payment methods: online bank transfers, Credit Card-3D secure and wallet systems), to avoid the missing orders on completion of payment on non-return of end user due to end user closed the browser or time out at payment, etc.!

### Fix

- Transaction information alignment in Invoice

## v1.0.3 (2018-08-22)

### New

- Payment logo customization option implemented

## v1.0.2 (2018-06-01)

### Enhanced

- Adjusted payment plugin for the new configuration structure and multilingual support

## v1.0.1 (2018-01-17)

### Enhanced

- Display error message without error code

## v1.0.0 (2017-12-08)

- New release
