<?php
/**
 * This file is used for creating payment methods
 *
 * @author       Novalnet AG
 * @copyright(C) Novalnet
 * @license      https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 */
 namespace Novalnet\Migrations;

use Novalnet\Helper\PaymentHelper;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;

/**
 * Class CreateNovalnetPaymentMethods1210
 *
 * @package Novalnet\Migrations
 */
class CreateNovalnetPaymentMethods1210
{
    /**
     * @var PaymentMethodRepositoryContract
     */
    private $paymentMethodRepository;

    /**
     * @var PaymentHelper
     */
    private $paymentHelper;

    /**
     * Constructor.
     *
     * @param PaymentMethodRepositoryContract $paymentMethodRepository
     * @param PaymentHelper $paymentHelper
     */
    public function __construct(PaymentMethodRepositoryContract $paymentMethodRepository,
                                PaymentHelper $paymentHelper)
    {
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->paymentHelper = $paymentHelper;
    }

    /**
     * Run on plugin build
     *
     * Create Method of Payment ID for Novalnet payment if they don't exist
     */
    public function run()
    {
        $paymentMethods = [
            'NOVALNET_SEPA'                     => 'Novalnet Direct Debit SEPA',
            'NOVALNET_INVOICE'                  => 'Novalnet Invoice',
            'NOVALNET_PREPAYMENT'               => 'Novalnet Prepayment',
            'NOVALNET_GUARANTEED_INVOICE'       => 'Novalnet Invoice with payment guarantee',
            'NOVALNET_GUARANTEED_SEPA'          => 'Novalnet Direct debit SEPA with payment guarantee',
            'NOVALNET_CC'                       => 'Novalnet Credit/Debit Cards',
            'NOVALNET_GOOGLEPAY'                => 'Novalnet Google Pay',
            'NOVALNET_APPLEPAY'                 => 'Novalnet ApplePay',
            'NOVALNET_IDEAL'                    => 'Novalnet iDEAL',
            'NOVALNET_SOFORT'                   => 'Novalnet Sofort',
            'NOVALNET_GIROPAY'                  => 'Novalnet giropay',
            'NOVALNET_CASHPAYMENT'              => 'Novalnet Barzahlen/viacash',
            'NOVALNET_PRZELEWY24'               => 'Novalnet Przelewy24',
            'NOVALNET_EPS'                      => 'Novalnet eps',
            'NOVALNET_INSTALMENT_INVOICE'       => 'Novalnet Instalment Invoice',
            'NOVALNET_INSTALMENT_SEPA'          => 'Novalnet Instalment Direct debit SEPA',
            'NOVALNET_PAYPAL'                   => 'Novalnet PayPal',
            'NOVALNET_POSTFINANCE_CARD'         => 'Novalnet PostFinance Card',
            'NOVALNET_POSTFINANCE_EFINANCE'     => 'Novalnet PostFinance E-Finance',
            'NOVALNET_BANCONTACT'               => 'Novalnet Bancontact',
            'NOVALNET_MULTIBANCO'               => 'Novalnet Multibanco',
            'NOVALNET_ONLINE_BANK_TRANSFER'     => 'Novalnet Online bank transfer',
            'NOVALNET_ALIPAY'                   => 'Novalnet Alipay',
            'NOVALNET_WECHAT_PAY'               => 'Novalnet WeChat Pay',
            'NOVALNET_TRUSTLY'                  => 'Novalnet Trustly',
            'NOVALNET_BLIK'                     => 'Novalnet Blik',
            'NOVALNET_PAYCONIQ'                 => 'Novalnet Payconiq',
            'NOVALNET_MBWAY'                    => 'Novalnet MBway',
            'NOVALNET_ACH'                      => 'Novalnet Direct Debit ACH',

        ];

        foreach($paymentMethods as $paymentMethodKey => $paymentMethodName) {
            $this->createPaymentMethodByPaymentKey($paymentMethodKey, $paymentMethodName);
        }
    }

    /**
     * Create payment method with given parameters if it doesn't exist
     *
     * @param string $paymentKey
     * @param string $name
     */
    private function createPaymentMethodByPaymentKey($paymentKey, $name)
    {
        $payment_data = $this->paymentHelper->getPaymentMethodByKey($paymentKey);
        if($payment_data == 'no_paymentmethod_found') {
            $paymentMethodData = [
                'pluginKey'  => 'plenty_novalnet',
                'paymentKey' => $paymentKey,
                'name'       => $name
            ];
            $this->paymentMethodRepository->createPaymentMethod($paymentMethodData);
        } elseif($payment_data[1] == $paymentKey) {
            $paymentMethodData = [
                'pluginKey'  => 'plenty_novalnet',
                'paymentKey' => $paymentKey,
                'name'       => $name,
                'id'         => $payment_data[0]
            ];
            $this->paymentMethodRepository->updateName($paymentMethodData);
        }
    }
}
