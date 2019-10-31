<div class="alert alert-warning" role="alert">
   <strong><i>Note:</i></strong> The Novalnet plugin has been developed for use with the online store Ceres and only works with its structure or other template plugins. IO plugin is required.
</div>

# Novalnet payment plugin for plentymarkets

Novalnet payment plugin for plentymarkets simplifies your daily work by automating the entire payment process from checkout till collection. This plugin is designed to help you increase your sales by offering various international and local payment methods. The plugin which is perfectly adjusted to plentymarkets and the top-quality range of services of the payment provider Novalnet.

The various range of payment methods includes **Credit Card**, **Direct Debit SEPA**, **PayPal**, **Invoice**, **Online transfer**, **iDeal** and so on.

## Opening a Novalnet merchant account

You need to open an merchant account with Novalnet before you can set up the payment plugin in the plentymarket. You will then receive the credentials that is required to install and configure the payment method. Please contact Novalnet at [sales@novalnet.de](mailto:sales@novalnet.de) for getting your own merchant account.

## Configuring Novalnet in plentymarkets

To set up the merchant credentials, navigate to the path **Plugins -> Plugin overview -> Novalnet -> Configuration**

### Novalnet Global Configuration

- Fill in your Novalnet merchant account details to make the payment method appear in the online store.
- The fields **Merchant ID**, **Authentication code**, **Project ID**, **Tariff ID** and **Payment access key** are necessary and also marked mandatory.
- These values can be retrieved from the [Novalnet Merchant Administration Portal](https://admin.novalnet.de/).
- After filling those values in the respective fields, you must check the option **Enable payment method** to complete the Novalnet payment setup in your store.

##### Retrieving Novalnet merchant account details:

1. Login into your merchant account.
2. Navigate to the tab **PROJECTS**.
3. Select the corresponding product.
4. Under the **Shop Parameters**, you will find the details required.
5. On an important note, you might find mutiple **Tarif ID's** (if created more than one for your project). Get the Tarif ID which you wish to use it in the online store.

### Novalnet configuration along with it's descriptions

<table>
    <thead>
        <th>
            Setting
        </th>
        <th>
            Description
        </th>
    </thead>
    <tbody>
        <tr>
        <td class="th" align=CENTER colspan="2">General</td>
        </tr>        
        <tr>
            <td><b>Merchant ID</b></td>
            <td>A merchant identification number is provided by Novalnet after opening a merchant account at Novalnet. Please contact Novalnet at <a href="mailto:sales@novalnet.de" target="_blank">sales@novalnet.de</a> for getting your own merchant account.</td>
        </tr>
        <tr>
            <td><b>Authentication code</b></td>
            <td>Merchant authentication code is provided by Novalnet after opening a merchant account at Novalnet.</td>
        </tr>
        <tr>
            <td><b>Project ID</b></td>
            <td>Project identification number is an unique ID of merchant project. The merchant can create N number of projects through <a href="https://admin.novalnet.de/" target="_blank">Novalnet Merchant Administration Portal.</a></td>
        </tr>
        <tr>
            <td><b>Tariff ID</b></td>
            <td>Tariff identification number is an unique ID for each merchant project. The merchant can create N number of tariffs through <a href="https://admin.novalnet.de/" target="_blank">Novalnet Merchant Administration Portal.</a></td>
        </tr>
        <tr>
            <td><b>Payment access key</b></td>
            <td>This is the secure public key for encryption and decryption of transaction parameters. This is mandatory value for all online transfers, Credit Card-3D secure and wallet systems. </td>
        </tr>
        <tr>
            <td><b>Gateway timeout (in seconds)</b></td>
            <td>
                In case the order processing time exceeds the gateway timeout, the order will not be placed.
            </td>
        </tr>
        <tr>
            <td><b>Proxy server</b></td>
            <td>
                Enter the IP address of your proxy server along with the port number in the following format IP Address : Port Number (if applicable)
            </td>
        </tr>
        <tr>
        <td class="th" align=CENTER colspan="2"><b>Payment settings</b></td>
        </tr>
        <tr>
        <td class="th" align=CENTER colspan="2">General</td>
        </tr>
        <tr>
            <td><b>Enable payment method</b></td>
            <td>Use this option to enable / disable payment methods.</td>
        </tr>
        <tr>
            <td><b>Enable test mode</b></td>
            <td>The payment will be processed in the test mode therefore amount for this transaction will not be charged.</td>
        </tr>
        <tr>
            <td><b>Upload payment logo</b></td>
            <td>The payment method logo will be displayed on the checkout page.</td>
        </tr>
        <tr>
            <td><b>Minimum order amount</b></td>
            <td>Minimum Order Amount to offer this Payment (in minimum unit ofcurrency. E.g. enter 100 which is equal to 1.00)</td>
        </tr>
        <tr>
            <td><b>Maximum order amount</b></td>
            <td>Maximum Order Amount to offer this Payment (in minimum unit of currency. E.g. enter 100 which is equal to 1.00)</td>
        </tr>
        <tr>
            <td><b>Allowed countries</b></td>
            <td>This Payment method will be allowed for the mentioned country. Enter the countries in the following format E.g. DE, AT, CH. In case if the field is empty, all the countries will be allowed.</td>
        </tr>
        <td class="th" align=CENTER colspan="2">Credit Card</td>
        </tr>
        <tr>
            <td><b>Enable 3D secure</b></td>
            <td>The 3D-Secure will be activated for credit cards. The issuing bank prompts the buyer for a password what, in turn, help to prevent a fraudulent payment. It can be used by the issuing bank as evidence that the buyer is indeed their card holder. This is intended to help decrease a risk of charge-back.</td>
        </tr>
        <tr>
            <td><b>Force 3D secure on predefined conditions</b></td>
            <td>If 3D secure is not enabled in the above field, then force 3D secure process as per the "Enforced 3D secure (as per predefined filters & settings)" module configuration at the <a href="https://admin.novalnet.de/" target="_blank">Novalnet Merchant Administration Portal.</a> If the predefined filters & settings from Enforced 3D secure module are met, then the transaction will be processed as 3D secure transaction otherwise it will be processed as non 3D secure. Please note that the "Enforced 3D secure (as per predefined filters & settings)" module should be configured at <a href="https://admin.novalnet.de/" target="_blank">Novalnet Merchant Administration Portal</a> prior to the activation here. For further information, please refer the description of this fraud module at "Fraud Modules" tab, below "Projects" menu, under the selected project in <a href="https://admin.novalnet.de/" target="_blank">Novalnet Merchant Administration Portal</a> or contact Novalnet support team.</td>
        </tr>
        <tr>
            <td><b>Payment action</b></td>
            <td>Capture / Authorize</td>
        </tr>
        <tr>
            <td><b>Minimum transaction limit for authorization</b></td>
            <td>In case the order amount exceeds the mentioned limit, the transaction will be set on-hold till your confirmation of the transaction. You can leave the field empty if you wish to process all the transactions as on-hold.</td>
        </tr>
        <tr>
        <td class="th" align=CENTER colspan="2">Direct Debit SEPA</td>
        </tr>
        <tr>
            <td><b>SEPA payment duration (in days)</b></td>
            <td>Enter the number of days after which the payment should be processed (must be between 2 and 14 days)</td>
        </tr>
        <tr>
            <td><b>Payment action</b></td>
            <td>Capture / Authorize</td>
        </tr>
        <tr>
            <td><b>Minimum transaction limit for authorization</b></td>
            <td>In case the order amount exceeds mentioned limit, the transaction will be set on hold till your confirmation of transaction.You can leave the field empty if you wish to process all the transactions as on-hold.</td>
        </tr>
        <tr>
            <td><b>Enable payment guarantee</b></td>
            <td><b>Basic requirements for payment guarantee:</b><br > -> Allowed countries: AT, DE, CH. <br > -> Allowed currency: EUR.<br > -> Minimum amount of order >= 9,99 EUR.<br > -> Minimum age of end customer >= 18 Years.<br > -> The billing address must be the same as the shipping address.<br > -> Gift certificates/ vouchers are not allowed.</td>
        </tr>
        <tr>
            <td><b>Minimum order amount (in minimum unit of currency. E.g. enter 100 which is equal to 1.00)</b></td>
            <td>This setting will override the default setting made in the minimum order amount. Note: Minimum amount should be greater than or equal to 9,99 EUR.</td>
        </tr>
        <tr>
            <td><b>Force Non-Guarantee payment</b></td>
            <td>If the payment guarantee is activated (True), but the above mentioned requirements are not met, the payment should be processed as non-guarantee payment.</td>
        </tr>
        <td class="th" align=CENTER colspan="2">Invoice</td>
        <tr>
            <td><b>Payment due date (in days)</b></td>
            <td>Enter the Number of days to transfer the payment amount to Novalnet (must be greater than 7 days). In case if the field is empty, 14 days will be set as due date by default.</td>
        </tr>
        <tr>
            <td><b>Payment action</b></td>
            <td>Capture / Authorize</td>
        </tr>
        <tr>
            <td><b>Minimum transaction limit for authorization</b></td>
            <td>In case the order amount exceeds mentioned limit, the transaction will be set on hold till your confirmation of transaction.You can leave the field empty if you wish to process all the transactions as on-hold.</td>
        </tr>
        <tr>
            <td><b>Enable payment guarantee</b></td>
            <td><b>Basic requirements for payment guarantee:</b><br > -> Allowed countries: AT, DE, CH. <br > -> Allowed currency: EUR.<br > -> Minimum amount of order >= 9,99 EUR.<br > -> Minimum age of end customer >= 18 Years.<br > -> The billing address must be the same as the shipping address.<br > -> Gift certificates/ vouchers are not allowed.</td>
        </tr>
        <tr>
            <td><b>Minimum order amount (in minimum unit of currency. E.g. enter 100 which is equal to 1.00)</b></td>
            <td>This setting will override the default setting made in the minimum order amount. Note: Minimum amount should be greater than or equal to 9,99 EUR.</td>
        </tr>
        <tr>
            <td><b>Force Non-Guarantee payment</b></td>
            <td>If the payment guarantee is activated (True), but the above mentioned requirements are not met, the payment should be processed as non-guarantee payment.</td>
        </tr>
        <td class="th" align=CENTER colspan="2">Barzahlen</td>
        <tr>
            <td><b>Slip expiry date (in days)</b></td>
            <td>Enter the number of days to pay the amount at store near you. If the field is empty, 14 days will be set as default.</td>
        </tr>
        <td class="th" align=CENTER colspan="2">Paypal</td>
        <tr>
            <td><b>Payment action</b></td>
            <td>Capture / Authorize</td>
        </tr>
        <tr>
            <td><b>Minimum transaction limit for authorization</b></td>
            <td>In case the order amount exceeds mentioned limit, the transaction will be set on hold till your confirmation of transaction(In order to use this option you must have billing agreement option enabled in your PayPal account. Please contact your account manager at PayPal)</td>
        </tr>
    </tbody>
</table>

## Event creation for  Confirm / Cancel / Refund a Novalnet transactions

Set up an event procedure to Confirm, Cancel and Refund the Novalnet transactions

##### Setting up an event procedure:

1. Go to **System » Orders » Events**.
2. Click on **Add event procedure**. <br > → The **Create new event procedure** window opens.
3. Enter a name.
4. Select the event according to tables 1-3.
5. **Save** the settings. <br > → The event procedure is created.
6. Carry out the further settings according to tables 1-3.
7. Place a check mark next to the option **Active**.
8. **Save** the settings. <br > → The event procedure is saved. 

<table>
   <thead>
    </tr>
      <th>
         Setting
      </th>
      <th>
         Option
      </th>
      <th>
         Selection
      </th>
    </tr>
   </thead>
   <tbody>
      <tr>
         <td><strong>Event</strong></td>
         <td>Select the event to trigger a confirm procedure.</td>
         <td></td>
      </tr>
      <tr>
         <td><strong>Filter 1</strong></td>
         <td><strong>Order > Payment method</strong></td>
         <td><strong>Plugin: Novalnet Invoice</strong></td>
      </tr>
      <tr>
        <td><strong>Procedure</strong></td>
        <td><strong>Plugins > Novalnet | Confirm</strong></td>
        <td></td>
      </tr>
    </tbody>
    <caption>
    Table 1: Event procedure to confirm Novalnet transaction
    </caption>
</table>


<table>
   <thead>
    </tr>
      <th>
         Setting
      </th>
      <th>
         Option
      </th>
      <th>
         Selection
      </th>
    </tr>
   </thead>
   <tbody>
      <tr>
         <td><strong>Event</strong></td>
         <td>Select the event to trigger a cancel procedure.</td>
         <td></td>
      </tr>
      <tr>
         <td><strong>Filter 1</strong></td>
         <td><strong>Order > Payment method</strong></td>
         <td><strong>Plugin: Novalnet Invoice</strong></td>
      </tr>
      <tr>
        <td><strong>Procedure</strong></td>
        <td><strong>Plugins > Novalnet | Cancel</strong></td>
        <td></td>
      </tr>
    </tbody>
    <caption>
    Table 2: Event procedure to cancel Novalnet transaction
    </caption>
</table>


<table>
   <thead>
    </tr>
      <th>
         Setting
      </th>
      <th>
         Option
      </th>
      <th>
         Selection
      </th>
    </tr>
   </thead>
   <tbody>
      <tr>
         <td><strong>Event</strong></td>
         <td>Select the event to trigger a refund procedure.</td>
         <td></td>
      </tr>
      <tr>
         <td><strong>Filter 1</strong></td>
         <td><strong>Order > Payment method</strong></td>
         <td><strong>Plugin: Novalnet Invoice</strong></td>
      </tr>
      <tr>
        <td><strong>Procedure</strong></td>
        <td><strong>Plugins > Novalnet | Refund</strong></td>
        <td></td>
      </tr>
    </tbody>
    <caption>
    Table 3: Event procedure to refund Novalnet transaction
    </caption>
</table>

## Displaying the payment transaction details on the invoice pdf

Generating invoice for the orders to display the payment transaction details in invoice pdf except for the initial order created events.

## Displaying the payment transaction details on the order confirmation page

To display the payment transaction details on the order confirmation page, perform the steps as follows.

##### Displaying transaction details:

1. Go to **CMS » Container links**..
3. Go to the **Novalnet payment details** area.
4. Activate the container **Order confirmation: Additional payment information**.
5. **Save** the settings.<br />→ The payment transaction details will be displayed on the order confirmation page.

## Update of Vendor Script URL

Vendor script URL is required to keep the merchant’s database/system up-to-date and synchronized with Novalnet transaction status. It is mandatory to configure the Vendor Script URL in [Novalnet Merchant Administration Portal](https://admin.novalnet.de/).

Novalnet system (via asynchronous) will transmit the information on each transaction and its status to the merchant’s system.

To configure Vendor Script URL,

1. Login into your merchant account.
2. Navigate to the tab **PROJECTS**.
3. Select the corresponding product.
4. Under the tab **Project Overview**.
5. Set up the **Vendor script URL** fof your store. In general the vendor script URL will be like **YOUR SITE URL/payment/novalnet/callback**.

## Further reading

To know more information about the Novalnet and it's features, please contact at  [sales@novalnet.de](mailto:sales@novalnet.de)
