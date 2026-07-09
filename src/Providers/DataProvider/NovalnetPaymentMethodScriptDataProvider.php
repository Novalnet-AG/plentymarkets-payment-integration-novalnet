<?php
/**
 * This file is used for customer reinitialize payment process
 *
 * @author       Novalnet AG
 * @copyright(C) Novalnet
 * @license      https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 */
namespace Novalnet\Providers\DataProvider;

use Plenty\Plugin\Templates\Twig;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;
use Plenty\Modules\Payment\Method\Models\PaymentMethod;
use Novalnet\Services\PaymentService;

/**
 * Class NovalnetPaymentMethodScriptDataProvider
 *
 * @package Novalnet\Providers\DataProvider
 */
class NovalnetPaymentMethodScriptDataProvider
{
    /**
     * Script for displaying the reinitiate payment button
     *
     * @param Twig $twig
     *
     * @return string
     */
    public function call(Twig $twig)
    {
        // Load the all Novalnet payment methods
        $paymentMethodRepository = pluginApp(PaymentMethodRepositoryContract::class);
        $paymentMethods          = $paymentMethodRepository->allForPlugin('plenty_novalnet');
        $paymentService          = pluginApp(PaymentService::class);
        if(!is_null($paymentMethods)) {
            $paymentMethodIds              = [];
            $nnPaymentMethodKey = $nnPaymentMethodId = '';
            foreach($paymentMethods as $paymentMethod) {
                if($paymentMethod instanceof PaymentMethod) {
                    $paymentMethodIds[] = $paymentMethod->id;
                    if($paymentMethod->paymentKey == 'NOVALNET_APPLEPAY') {
                        $nnPaymentMethodKey = $paymentMethod->paymentKey;
                        $nnPaymentMethodId = $paymentMethod->id;
                    }
                }
            }
            return $twig->render('Novalnet::NovalnetPaymentMethodScriptDataProvider',
                                    [
                                        'paymentMethodIds'      => $paymentMethodIds,
                                        'nnPaymentMethodKey'    => $nnPaymentMethodKey,
                                        'nnPaymentMethodId'     => $nnPaymentMethodId,
                                        'redirectUrl'           => $paymentService->getRedirectPaymentUrl(),
                                    ]);
        } else {
            return '';
        }      
    }
}
