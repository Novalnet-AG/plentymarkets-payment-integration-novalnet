<?php
/**
 * This module is used for handling the redirect Url process
 *
 * @author       Novalnet AG
 * @copyright(C) Novalnet
 * @license      https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 */

namespace Novalnet\Controllers;

use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Novalnet\Services\PaymentService;
use Novalnet\Helper\PaymentHelper;
use Novalnet\Services\SettingsService;
use Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Plugin\Templates\Twig;

/**
 * Class PaymentController
 *
 * @package Novalnet\Controllers
 */
class PaymentController extends Controller
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var PaymentService
     */
    private $paymentService;

    /**
     * @var PaymentHelper
     */
    private $paymentHelper;

    /**
     * @var SettingsService
    */
    private $settingsService;

    /**
     * @var FrontendSessionStorageFactoryContract
     */
    private $sessionStorage;

    /**
     * @var BasketRepositoryContract
     */
    private $basketRepository;

    /**
     * @var Twig
     */
    private $twig;

    /**
     * Constructor.
     *
     * @param Request $request
     * @param Response $response
     * @param PaymentService $paymentService
     * @param PaymentHelper $paymentHelper
     * @param SettingsService $settingsService
     * @param FrontendSessionStorageFactoryContract $sessionStorage
     * @param BasketRepositoryContract $basketRepository
     * @param Twig $twig
     */
    public function __construct(Request $request,
                                Response $response,
                                PaymentService $paymentService,
                                PaymentHelper $paymentHelper,
                                SettingsService $settingsService,
                                FrontendSessionStorageFactoryContract $sessionStorage,
                                BasketRepositoryContract $basketRepository,
                                Twig $twig
                               )
    {
        $this->request          = $request;
        $this->response         = $response;
        $this->paymentService   = $paymentService;
        $this->paymentHelper    = $paymentHelper;
        $this->settingsService  = $settingsService;
        $this->sessionStorage   = $sessionStorage;
        $this->basketRepository = $basketRepository;
        $this->twig             = $twig;
    }

    /**
     * Novalnet redirects to this page if the payment was executed successfully
     *
     */
    public function paymentResponse()
    {
        // Get the initial payment call response
        $paymentResponseData = $this->request->all();

        // Checksum validation for redirects
        if(!empty($paymentResponseData['tid'])) {
            if($paymentResponseData['status'] == 'SUCCESS') {
                // Checksum validation and transaction status call to retrieve the full response
                $paymentResponseData = $this->paymentService->validateChecksumAndGetTxnStatus($paymentResponseData);

                // Checksum validation is failure return back to the customer to confirmation page with error message
                if(!empty($paymentResponseData['nn_checksum_invalid'])) {
                    $this->paymentService->pushNotification($paymentResponseData['nn_checksum_invalid'], 'error', 100);
                    return $this->response->redirectTo($this->sessionStorage->getLocaleSettings()->language . '/confirmation');
                }

                // Retrieve the full payment response
                $paymentResponseData = $this->paymentService->getFullTxnResponse($paymentResponseData);
                $isPaymentSuccess = isset($paymentResponseData['result']['status']) && $paymentResponseData['result']['status'] == 'SUCCESS';
                if($isPaymentSuccess) {
                    $this->paymentService->pushNotification($paymentResponseData['result']['status_text'], 'success', 100);
                } else {
                    $this->paymentService->pushNotification($paymentResponseData['result']['status_text'], 'error', 100);
                    if($this->settingsService->getPaymentSettingsValue('novalnet_order_creation') != true) {
                        return $this->response->redirectTo('checkout');
                    }
                }
            } else {
                $this->paymentService->pushNotification($paymentResponseData['status_text'], 'error', 100);
                if($this->settingsService->getPaymentSettingsValue('novalnet_order_creation') != true) {
                  return $this->response->redirectTo('checkout');
                }
            }
            $paymentRequestData = $this->sessionStorage->getPlugin()->getValue('nnPaymentData');
            // Set the payment response in the session for the further processings
            $this->sessionStorage->getPlugin()->setValue('nnPaymentData', array_merge($paymentRequestData, $paymentResponseData));
            if($this->settingsService->getPaymentSettingsValue('novalnet_order_creation') != true && !isset($paymentResponseData['transaction']['order_no'])) {
                // Call the shop executePayment function
                return $this->response->redirectTo($this->sessionStorage->getLocaleSettings()->language . '/place-order');
            }
            // Handle the further process to the order based on the payment response
            $this->paymentService->HandlePaymentResponse();
            return $this->response->redirectTo($this->sessionStorage->getLocaleSettings()->language . '/confirmation');

        } else {
            $this->paymentService->pushNotification($paymentResponseData['status_text'], 'error', 100);
            if($this->settingsService->getPaymentSettingsValue('novalnet_order_creation') != true) {
                  return $this->response->redirectTo('checkout');
            } else {
                return $this->response->redirectTo($this->sessionStorage->getLocaleSettings()->language . '/confirmation');
            }
        }
    }

    /**
     * Process the Form payment
     *
     */
    public function processPayment()
    {
        // Get the payment form post data
        $paymentRequestPostData = $this->request->all();
        // Get the order amount
        $orderAmount = !empty($paymentRequestPostData['nn_order_amount']) ? $paymentRequestPostData['nn_order_amount'] : 0;
        // Get instalment selected option key value
        $selectedOption = $paymentRequestPostData['nn_instalment_cycle'];
        list($key, $value) = explode("-", $selectedOption);
        // Get the payment request params
        $paymentRequestData = $this->paymentService->generatePaymentParams($this->basketRepository->load(), $paymentRequestPostData['nn_payment_key'], $orderAmount);
        // Setting up the account data to the server for SEPA processing
        if(in_array($paymentRequestPostData['nn_payment_key'], ['NOVALNET_SEPA', 'NOVALNET_GUARANTEED_SEPA', 'NOVALNET_INSTALMENT_SEPA'])) {
            $paymentRequestData['paymentRequestData']['transaction']['payment_data'] = ['iban'  => $paymentRequestPostData['nn_sepa_iban']];
            if(!empty($paymentRequestPostData['nn_sepa_bic'])) {
                $paymentRequestData['paymentRequestData']['transaction']['payment_data']['bic'] = $paymentRequestPostData['nn_sepa_bic'];
            }
        }
        // Setting up the account data to the server for ACH processing
        if($paymentRequestPostData['nn_payment_key'] == 'NOVALNET_ACH') {
                $paymentRequestData['paymentRequestData']['transaction']['payment_data']['account_holder'] = $paymentRequestPostData['nn_ach_account_holder'];
                $paymentRequestData['paymentRequestData']['transaction']['payment_data']['account_number'] = $paymentRequestPostData['nn_ach_account_number'];
                $paymentRequestData['paymentRequestData']['transaction']['payment_data']['routing_number'] = $paymentRequestPostData['nn_ach_routing_number'];

        }
        // Setting up the mobile number to the server for MBWAY processing
        if($paymentRequestPostData['nn_payment_key'] == 'NOVALNET_MBWAY') {
            $paymentRequestData['paymentRequestData']['customer']['mobile'] = $paymentRequestPostData['nn_mbway_mobile_number'];
            $paymentRequestData['paymentRequestData']['transaction']['return_url'] = $this->paymentService->getReturnPageUrl();
            $this->sessionStorage->getPlugin()->setValue('nnDoRedirect', 'true');
        }
        // Setting up the birthday for guaranteed payments
        if(in_array($paymentRequestPostData['nn_payment_key'], ['NOVALNET_GUARANTEED_INVOICE', 'NOVALNET_GUARANTEED_SEPA']) && !empty($paymentRequestPostData['nn_show_dob'])) {
            $paymentRequestData['paymentRequestData']['customer']['birth_date'] = sprintf('%4d-%02d-%02d', $paymentRequestPostData['nn_guarantee_year'], $paymentRequestPostData['nn_guarantee_month'], $paymentRequestPostData['nn_guarantee_date']);
        }
        // Setting up the cycle and birht date for instalment payments
        if(in_array($paymentRequestPostData['nn_payment_key'], ['NOVALNET_INSTALMENT_INVOICE', 'NOVALNET_INSTALMENT_SEPA']) ) {
            $paymentRequestData['paymentRequestData']['instalment']['cycles'] = $key;
            if(!empty($paymentRequestPostData['nn_show_dob'])) {
                $paymentRequestData['paymentRequestData']['customer']['birth_date'] = sprintf('%4d-%02d-%02d', $paymentRequestPostData['nn_instalment_year'], $paymentRequestPostData['nn_instalment_month'], $paymentRequestPostData['nn_instalment_date']);
            }
        }
        // Setting up the alternative card data to the server for card processing
        if($paymentRequestPostData['nn_payment_key'] == 'NOVALNET_CC') {
            $paymentRequestData['paymentRequestData']['transaction']['payment_data'] = [
                'pan_hash'   => $paymentRequestPostData['nn_pan_hash'],
                'unique_id'  => $paymentRequestPostData['nn_unique_id']
            ];
            // Set the Do redirect value into session for the redirection
            $this->sessionStorage->getPlugin()->setValue('nnDoRedirect', $paymentRequestPostData['nn_cc3d_redirect']);
        }
        // Setting up the wallet token for the Google pay payment
        if($paymentRequestPostData['nn_payment_key'] == 'NOVALNET_GOOGLEPAY') {
            $paymentRequestData['paymentRequestData']['transaction']['payment_data'] = ['wallet_token'  => $paymentRequestPostData['nn_google_pay_token']];
            // Set the Do redirect value into session for the Google Pay redirection
             $this->sessionStorage->getPlugin()->setValue('nnGooglePayDoRedirect', $paymentRequestPostData['nn_google_pay_do_redirect']);
        }
        // Call the order creation function for the redirection
        if(!empty($paymentRequestPostData['nn_cc3d_redirect']) || $paymentRequestPostData['nn_payment_key'] == 'NOVALNET_MBWAY' || (!empty($paymentRequestPostData['nn_google_pay_do_redirect']) && (string) $paymentRequestPostData['nn_google_pay_do_redirect'] === 'true')) {
            $paymentRequestData['paymentRequestData']['transaction']['return_url'] = $this->paymentService->getReturnPageUrl();
            $this->sessionStorage->getPlugin()->setValue('nnPaymentData', $paymentRequestData);
            if(!empty($paymentRequestPostData['nn_reinitializePayment']) || $this->settingsService->getPaymentSettingsValue('novalnet_order_creation') != true) {
                return $this->response->redirectTo($this->sessionStorage->getLocaleSettings()->language . '/payment/novalnet/redirectPayment');
            }
                // Call the shop executePayment function
                return $this->response->redirectTo($this->sessionStorage->getLocaleSettings()->language . '/place-order');
        }
        // Set the payment requests in the session for the further processings
        $this->sessionStorage->getPlugin()->setValue('nnPaymentData', $paymentRequestData);
        if(!empty($paymentRequestPostData['nn_reinitializePayment'])) {
            $this->paymentService->performServerCall();
            return $this->response->redirectTo($this->sessionStorage->getLocaleSettings()->language . '/confirmation');
        } else {
            if($this->settingsService->getPaymentSettingsValue('novalnet_order_creation') != true) {
                $paymentResponseData = $this->paymentService->performServerCall();
                if(!empty($paymentResponseData) && $paymentResponseData['result']['status'] != 'SUCCESS') {
                    $this->sessionStorage->getPlugin()->setValue('nnDoRedirect', null);
            $this->sessionStorage->getPlugin()->setValue('nnGooglePayDoRedirect', null);
                    $this->paymentService->pushNotification($paymentResponseData['result']['status_text'], 'error', 100);
                    // return back to the customer on checkout page
                    return $this->response->redirectTo('checkout');
                }
            }
            // Call the shop executePayment function
            return $this->response->redirectTo($this->sessionStorage->getLocaleSettings()->language . '/place-order');
        }
    }

    /**
     * Process the direct payment methods when the change payment method option used
     *
     */
    public function directPaymentProcess()
    {
        $this->sessionStorage->getPlugin()->setValue('nnReinitiatePayment', '1');
        $this->paymentService->performServerCall();
    }

    /**
     * Process the redirect payment methods when the change payment method option used
     *
     */
    public function redirectPayment()
    {
        $postData = $this->request->all();
        if($postData['nnReinitiatePayment']) {
        $this->sessionStorage->getPlugin()->setValue('nnReinitiatePayment', '1');
        }
        $paymentRequestData = $this->sessionStorage->getPlugin()->getValue('nnPaymentData');
        if((empty($paymentRequestData['paymentRequestData']['customer']['first_name']) || empty($paymentRequestData['paymentRequestData']['customer']['last_name'])) || empty($paymentRequestData['paymentRequestData']['customer']['email'])) {
            $content = $this->paymentHelper->getTranslatedText('nn_first_last_name_error');
             $this->paymentService->pushNotification($content, 'error', 100);
           if(empty($paymentRequestData['paymentRequestData']['customer']['email'])) {
            $content = $this->paymentHelper->getTranslatedText('nn_email_error');
             $this->paymentService->pushNotification($content, 'error', 100);
            }
           return $this->response->redirectTo($this->sessionStorage->getLocaleSettings()->language . '/confirmation');
        }
        $paymentResponseData = $this->paymentService->performServerCall();
        $paymentKey = $this->sessionStorage->getPlugin()->getValue('paymentkey');
        $nnDoRedirect = $this->sessionStorage->getPlugin()->getValue('nnDoRedirect');
        $nnGooglePayDoRedirect = $this->sessionStorage->getPlugin()->getValue('nnGooglePayDoRedirect');
        $this->sessionStorage->getPlugin()->setValue('nnDoRedirect', null);
        $this->sessionStorage->getPlugin()->setValue('nnGooglePayDoRedirect', null);
        if($this->paymentService->isRedirectPayment($paymentKey) || !empty($nnDoRedirect) || (!empty($nnGooglePayDoRedirect) && (string) $nnGooglePayDoRedirect === 'true')) {
            if(!empty($paymentResponseData) && !empty($paymentResponseData['result']['redirect_url']) && !empty($paymentResponseData['transaction']['txn_secret'])) {
                // Transaction secret used for the later checksum verification
                $this->sessionStorage->getPlugin()->setValue('nnTxnSecret', $paymentResponseData['transaction']['txn_secret']);
                return $this->twig->render('Novalnet::NovalnetPaymentRedirectForm',
                        [
                            'nnPaymentUrl' => $paymentResponseData['result']['redirect_url']
                        ]);
            } else {
                // Redirect to confirmation page
                $this->paymentService->pushNotification($paymentResponseData['result']['status_text'], 'error', 100);
                return $this->response->redirectTo($this->sessionStorage->getLocaleSettings()->language . '/confirmation');
            }
        }
    }
}
