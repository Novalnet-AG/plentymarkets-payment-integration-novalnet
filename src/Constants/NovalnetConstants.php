<?php
/**
 * This module is used for real time processing of
 * Novalnet payment module of customers.
 * This free contribution made by request.
 * 
 * If you have found this script useful a small
 * recommendation as well as a comment on merchant form
 * would be greatly appreciated.
 *
 * @author       Novalnet AG
 * @copyright(C) Novalnet 
 * All rights reserved. https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 */
 
namespace Novalnet\Constants;

/**
 * Class NovalnetConstants
 *
 * @package Novalnet\Constants
 */
class NovalnetConstants
{
    const PLUGIN_VERSION = '7.0.0-NN(2.0.10)';
    const PAYPORT_URL    = 'https://payport.novalnet.de/paygate.jsp';
    const CC3D_PAYMENT_URL = 'https://payport.novalnet.de/pci_payport';
    const GIROPAY_PAYMENT_URL = 'https://payport.novalnet.de/giropay';
    const PAYPAL_PAYMENT_URL = 'https://payport.novalnet.de/paypal_payport';
    const PRZELEWY_PAYMENT_URL = 'https://payport.novalnet.de/globalbank_transfer';
    const SOFORT_PAYMENT_URL = 'https://payport.novalnet.de/online_transfer_payport';
    const BARZAHLEN_LIVEURL = 'https://cdn.barzahlen.de/js/v2/checkout.js';
    const BARZAHLEN_TESTURL = 'https://cdn.barzahlen.de/js/v2/checkout-sandbox.js';
}
