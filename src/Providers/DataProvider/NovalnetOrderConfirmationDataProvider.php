<?php
/**
 * This file is used for displaying transaction comments in the
 * order confirmation page
 *
 * @author       Novalnet AG
 * @copyright(C) Novalnet
 * @license      https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 */
namespace Novalnet\Providers\DataProvider;

use Plenty\Plugin\Templates\Twig;
use Novalnet\Helper\PaymentHelper;
use Novalnet\Services\PaymentService;
use Plenty\Modules\Payment\Contracts\PaymentRepositoryContract;
use Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract;

/**
 * Class NovalnetOrderConfirmationDataProvider
 *
 * @package Novalnet\Providers\DataProvider
 */
class NovalnetOrderConfirmationDataProvider
{
    /**
     * Displaying transaction comments in the order confirmation page
     *
     * @param Twig $twig
     * @param PaymentRepositoryContract $paymentRepositoryContract
     * @param Arguments $arg
     *
     * @return string
     */
    public function call(Twig $twig,
                         PaymentRepositoryContract $paymentRepositoryContract,
                         $arg
                        )
    {
        $order = $arg[0];
        $paymentHelper  = pluginApp(PaymentHelper::class);
        $paymentService = pluginApp(PaymentService::class);
        $sessionStorage = pluginApp(FrontendSessionStorageFactoryContract::class);
        // Define the variables
        $transactionComment = $cashpaymentToken = $cashpaymentUrl = '';
        if(!empty($order['id'])) {
            // Loads the payments for an order
            $payments = $paymentRepositoryContract->getPaymentsByOrderId($order['id']);
            foreach($payments as $payment) {
                    // Check it is Novalnet Payment method order
                    if($paymentHelper->getPaymentKeyByMop($payment->mopId)) {
                        // Load the order property and get the required details
                        $orderProperties = $payment->properties;
                        foreach($orderProperties as $orderProperty) {
                            if($orderProperty->typeId == 21) { // Loads the bank details from the payment object for previous payment plugin versions
                                $invoiceDetails = $orderProperty->value;
                            }
                            if($orderProperty->typeId == 30) { // Load the transaction status
                                $txStatus = $orderProperty->value;
                            }
                            if($orderProperty->typeId == 22) { // Loads the cashpayment comments from the payment object for previous payment plugin versions
                                $cashpaymentComments = $orderProperty->value;
                            }
                        }
                        // Get the cashpayment token and checkout URL
                        if($payment->method['paymentKey'] == 'NOVALNET_CASHPAYMENT') {
                            $cashpaymentToken = html_entity_decode((string)$sessionStorage->getPlugin()->getValue('novalnetCheckoutToken'));
                            $cashpaymentUrl = html_entity_decode((string)$sessionStorage->getPlugin()->getValue('novalnetCheckoutUrl'));
                        }
                        // Get Novalnet transaction details from the Novalnet database table
                        $nnDbTxDetails = $paymentService->getDatabaseValues($order['id']);
                        // Get the transaction status as string for the previous payment plugin version
                        $nnDbTxDetails['tx_status'] = $paymentService->getTxStatusAsString($txStatus, $nnDbTxDetails['payment_id']);
                        // Set the cashpayment comments into array
                        $nnDbTxDetails['cashpayment_comments'] = !empty($cashpaymentComments) ? $cashpaymentComments : '';
                        // Form the Novalnet transaction comments
                        $transactionComments = $paymentService->formTransactionComments($nnDbTxDetails);
                    } else {
                        return '';
                    }
            }
            $transactionComment .= (string) $transactionComments;
            // Replace PHP_EOL as break tag for the alignment
            $transactionComment = str_replace(PHP_EOL, '<br>', $transactionComment);
            // Render the transaction comments
            return $twig->render('Novalnet::NovalnetOrderConfirmationDataProvider',
                            [
                                'transactionComments' => html_entity_decode($transactionComment),
                                'cashpaymentToken' => $cashpaymentToken,
                                'cashpaymentUrl' => $cashpaymentUrl,
                                'txStatus' => $nnDbTxDetails['tx_status']
                            ]);
        } else {
            return '';
        }
    }
}
