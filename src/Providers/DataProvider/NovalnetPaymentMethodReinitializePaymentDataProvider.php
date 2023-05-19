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
use Novalnet\Services\PaymentService;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract;
use Novalnet\Helper\PaymentHelper;
use Novalnet\Services\SettingsService;
use Plenty\Modules\Helper\Services\WebstoreHelper;

/**
 * Class NovalnetPaymentMethodReinitializePaymentDataProvider
 *
 * @package Novalnet\Providers\DataProvider
 */
class NovalnetPaymentMethodReinitializePaymentDataProvider
{
    /**
     * Display the reinitiate payment button
     *
     * @param Twig $twig
     * @param Arguments $arg
     *
     * @return string
     */
    public function call(Twig $twig, WebstoreHelper $webstoreHelper, $arg)
    {
        $order = $arg[0];
        $paymentService     = pluginApp(PaymentService::class);
        $basketRepository   = pluginApp(BasketRepositoryContract::class);
        $sessionStorage     = pluginApp(FrontendSessionStorageFactoryContract::class);
        $paymentHelper      = pluginApp(PaymentHelper::class);
        $settingsService    = pluginApp(SettingsService::class);
        // Get the Novalnet payment method Id
        foreach($order['properties'] as $orderProperty) {
            if($orderProperty['typeId'] == 3)
            {
                $mopId = $orderProperty['value'];
            }
        }
        // Get the payment method key
        $paymentKey = $paymentHelper->getPaymentKeyByMop($mopId);
        // Check if the order is Novalnet payment method
        if(strpos($paymentKey, 'NOVALNET') !== false) {
            // Get the Novalnet payment key and MOP Id
            $transactionDetails = $paymentService->getDetailsFromPaymentProperty($order['id']);
            // Build the payment request paramters
            if(!empty($basketRepository->load())) {
                // Assign the billing and shipping address Id
                $basketRepository->load()->customerInvoiceAddressId = !empty($basketRepository->load()->customerInvoiceAddressId) ? $basketRepository->load()->customerInvoiceAddressId : $order['billingAddress']['id'];
                $basketRepository->load()->customerShippingAddressId = !empty($basketRepository->load()->customerShippingAddressId) ? $basketRepository->load()->customerShippingAddressId : $order['deliveryAddress']['id'];

                // Get the proper order amount even the system currency and payment currency are differ
                if(count($order['amounts']) > 1) {
                     foreach($order['amounts'] as $orderAmount) {
                        if($basketRepository->load()->currency == $orderAmount['currency']) {
                            $invoiceAmount = $paymentHelper->convertAmountToSmallerUnit($orderAmount['invoiceTotal']);
                        }
                    }
                } else {
                    $invoiceAmount = $paymentHelper->convertAmountToSmallerUnit($order['amounts'][0]['invoiceTotal']);
                }
                // Set the required values into session
                $sessionStorage->getPlugin()->setValue('nnOrderNo', $order['id']);
                $sessionStorage->getPlugin()->setValue('nnOrderCreator', $order['ownerId']);
                $sessionStorage->getPlugin()->setValue('nnBillingAddressId', $order['billingAddress']['id']);
                $sessionStorage->getPlugin()->setValue('nnShippingAddressId', $order['deliveryAddress']['id']);
                $sessionStorage->getPlugin()->setValue('nnOrderAmount', $invoiceAmount);
                $sessionStorage->getPlugin()->setValue('paymentkey', $paymentKey);
                $sessionStorage->getPlugin()->setValue('mop', $mopId);

                // Build the payment request parameters
                $paymentRequestData = $paymentService->generatePaymentParams($basketRepository->load(), $paymentKey, $invoiceAmount);

                // Set the payment request parameters into session
                $sessionStorage->getPlugin()->setValue('nnPaymentData', $paymentRequestData);

                // Get the Credit card form loading parameters
                if ($paymentKey == 'NOVALNET_CC') {
                     $ccFormDetails = $paymentService->getCreditCardAuthenticationCallData($basketRepository->load(), strtolower($paymentKey), $invoiceAmount);
                     $ccCustomFields = $paymentService->getCcFormFields();
                }
                // Get the required details for Google Pay payment
                if($paymentKey == 'NOVALNET_GOOGLEPAY') {
                    // Get the seller name from the shop configuaration
                    $sellerName = $settingsService->getPaymentSettingsValue('business_name', 'novalnet_googlepay');
                    $googlePayData = [
                                        'clientKey'           => trim($settingsService->getPaymentSettingsValue('novalnet_client_key')),
                                        'merchantId'          => $settingsService->getPaymentSettingsValue('payment_active', 'novalnet_googlepay'),
                                        'sellerName'          => !empty($sellerName) ? $sellerName : $webstoreHelper->getCurrentWebstoreConfiguration()->name,
                                        'enforce'             => $settingsService->getPaymentSettingsValue('enforce', 'novalnet_googlepay'),
                                        'buttonType'          => $settingsService->getPaymentSettingsValue('button_type', 'novalnet_googlepay'),
                                        'buttonHeight'        => $settingsService->getPaymentSettingsValue('button_height', 'novalnet_googlepay'),
                                        'testMode'            => ($settingsService->getPaymentSettingsValue('test_mode', 'novalnet_googlepay') == true) ? 'SANDBOX' : 'PRODUCTION'
                                     ];
                 }

                // Check if the birthday field needs to show for guaranteed payments
                $showBirthday = ((!isset($paymentRequestData['paymentRequestData']['customer']['billing']['company']) && !isset($paymentRequestData['paymentRequestData']['customer']['birth_date'])) || (isset($paymentRequestData['paymentRequestData']['customer']['birth_date']) && time() < strtotime('+18 years', strtotime($paymentRequestData['paymentRequestData']['customer']['birth_date'])))) ? true : false;
                // If the Novalnet payments are rejected do the reinitialize payment
                if((!empty($transactionDetails['tx_status']) && !in_array($transactionDetails['tx_status'], ['PENDING', 'ON_HOLD', 'CONFIRMED', 'DEACTIVATED'])) || empty($transactionDetails['tx_status'])) {
                    return $twig->render('Novalnet::NovalnetPaymentMethodReinitializePaymentDataProvider',
                                [
                                    'order' => $order,
                                    'paymentMethodId' => $mopId,
                                    'paymentMopKey' => $paymentKey,
                                    'reinitializePayment' => 1,
                                    'nnPaymentProcessUrl' => $paymentService->getProcessPaymentUrl(),
                                    'paymentName' => $paymentHelper->getCustomizedTranslatedText('template_' . strtolower($paymentKey)),
                                    'transactionData' => !empty($ccFormDetails) ? $ccFormDetails : '',
                                    'customData' => !empty($ccCustomFields) ? $ccCustomFields : '',
                                    'showBirthday' => $showBirthday,
                                    'orderAmount' => $invoiceAmount,
                                    'redirectPayment' => $paymentService->isRedirectPayment($paymentKey),
                                    'redirectUrl' => $paymentService->getRedirectPaymentUrl(),
                                    'orderLang'   => $paymentRequestData['paymentRequestData']['custom']['lang'],
                                    'countryCode' => $paymentRequestData['paymentRequestData']['customer']['billing']['country_code'],
                                    'orderCurrency'  => $basketRepository->load()->currency,
                                    'googlePayData' => !empty($googlePayData) ? $googlePayData : ''
                                ]);
               }
            }
        } else {
            return '';
        }
    }
}
