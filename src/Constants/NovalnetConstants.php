<?php
/**
 * This file is used to define the constants
 *
 * @author       Novalnet AG
 * @copyright(C) Novalnet
 * @license      https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 */
namespace Novalnet\Constants;

/**
 * Class NovalnetConstants
 *
 * @package Novalnet\Constants
 */
class NovalnetConstants
{
    const PLUGIN_VERSION                    = '7.0.0-NN(12.1.0)';
    const PAYMENT_URL                       = 'https://payport.novalnet.de/v2/payment';
    const PAYMENT_AUTHORIZE_URL             = 'https://payport.novalnet.de/v2/authorize';
    const TXN_RESPONSE_URL                  = 'https://payport.novalnet.de/v2/transaction_details';
    const SEAMLESS_PAYMENT_URL              = 'https://payport.novalnet.de/v2/seamless/payment';
    const PAYMENT_CAPTURE_URL               = 'https://payport.novalnet.de/v2/transaction/capture';
    const PAYMENT_VOID_URL                  = 'https://payport.novalnet.de/v2/transaction/cancel';
    const INSTALMENT_VOID_URL               = 'https://payport.novalnet.de/v2/instalment/cancel';
    const PAYMENT_REFUND_URL                = 'https://payport.novalnet.de/v2/transaction/refund';
    const TXN_UPDATE                        = 'https://payport.novalnet.de/v2/transaction/update';
    const MERCHANT_DETAILS                  = 'https://payport.novalnet.de/v2/merchant/details';
    const SEAMLESS_PAYMENT_AUTHORIZE_URL    = 'https://payport.novalnet.de/v2/seamless/authorize';
}
