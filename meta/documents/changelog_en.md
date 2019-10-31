# Release Notes for Novalnet

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
