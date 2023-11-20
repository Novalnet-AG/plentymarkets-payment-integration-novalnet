<?php
/**
 * This file is used for registering the Novalnet payment methods
 * and Event procedures
 *
 * @author       Novalnet AG
 * @copyright(C) Novalnet
 * @license      https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 */
namespace Novalnet\Providers;

use Novalnet\Helper\PaymentHelper;
use Novalnet\Services\PaymentService;
use Novalnet\Assistants\NovalnetAssistant;
use Novalnet\Methods\NovalnetPaymentAbstract;
use Novalnet\Constants\NovalnetConstants;
use Novalnet\Services\SettingsService;
use Plenty\Plugin\ServiceProvider;
use Plenty\Modules\Basket\Events\Basket\AfterBasketCreate;
use Plenty\Modules\Basket\Events\Basket\AfterBasketChanged;
use Plenty\Modules\Basket\Events\BasketItem\AfterBasketItemAdd;
use Plenty\Plugin\Events\Dispatcher;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Payment\Events\Checkout\ExecutePayment;
use Plenty\Modules\Payment\Events\Checkout\GetPaymentMethodContent;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodContainer;
use Plenty\Modules\Wizard\Contracts\WizardContainerContract;
use Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract;
use Plenty\Plugin\Templates\Twig;
use Plenty\Modules\EventProcedures\Services\Entries\ProcedureEntry;
use Plenty\Modules\EventProcedures\Services\EventProceduresService;
use Plenty\Modules\Order\Pdf\Events\OrderPdfGenerationEvent;
use Plenty\Modules\Order\Pdf\Models\OrderPdfGeneration;
use Plenty\Modules\Document\Models\Document;
use Plenty\Modules\Payment\Contracts\PaymentRepositoryContract;
use Plenty\Plugin\Log\Loggable;

/**
 * Class NovalnetServiceProvider
 *
 * @package Novalnet\Providers
 */
class NovalnetServiceProvider extends ServiceProvider
{
    use Loggable;

    /**
     * Register the route service provider
     */
    public function register()
    {
        $this->getApplication()->register(NovalnetRouteServiceProvider::class);
    }

    /**
     * Boot additional services for the payment method
     *
     * @param Dispatcher $eventDispatcher
     * @param BasketRepositoryContract $basketRepository
     * @param PaymentMethodContainer $payContainer
     * @param PaymentHelper $paymentHelper
     * @param PaymentService $paymentService
     * @param FrontendSessionStorageFactoryContract $sessionStorage
     * @param Twig $twig
     * @param EventProceduresService $eventProceduresService
     * @param PaymentRepositoryContract $paymentRepository
     * @param SettingsService $settingsService
     */
    public function boot(Dispatcher $eventDispatcher,
                        BasketRepositoryContract $basketRepository,
                        PaymentMethodContainer $payContainer,
                        PaymentHelper $paymentHelper,
                        PaymentService $paymentService,
                        FrontendSessionStorageFactoryContract $sessionStorage,
                        Twig $twig,
                        EventProceduresService $eventProceduresService,
                        PaymentRepositoryContract $paymentRepository,
                        SettingsService $settingsService
                        )
    {
        // Register the payment methods
        $this->registerPaymentMethods($payContainer);
        // Render the payment methods
        $this->registerPaymentRendering($eventDispatcher, $basketRepository, $paymentHelper, $paymentService, $sessionStorage, $twig, $settingsService);
        // Assign the payments
        $this->registerPaymentExecute($eventDispatcher, $paymentHelper, $paymentService, $sessionStorage, $settingsService);
        // Register the event procedures
        $this->registerEvents($eventProceduresService);
        // Call the invoice generation event
        $this->invoicePdfGenerationEvent($eventDispatcher, $paymentService, $paymentHelper, $paymentRepository);
        // Set the Novalnet assistant
        pluginApp(WizardContainerContract::class)->register('payment-novalnet-assistant', NovalnetAssistant::class);
    }

    /**
     * Register the Novalnet payment methods in the payment method container
     *
     * @param PaymentMethodContainer $payContainer
     *
     * @return none
     */
    protected function registerPaymentMethods(PaymentMethodContainer $payContainer)
    {
        foreach(PaymentHelper::getPaymentMethods() as $paymentMethodKey => $paymentMethodClass) {
            $payContainer->register('plenty_novalnet::' . $paymentMethodKey, $paymentMethodClass,
            [
                AfterBasketChanged::class,
                AfterBasketItemAdd::class,
                AfterBasketCreate::class
            ]);
        }
    }

    /**
     * Rendering the Novalnet payment method content
     *
     * @param Dispatcher $eventDispatcher
     * @param BasketRepositoryContract $basketRepository
     * @param PaymentHelper $paymentHelper
     * @param PaymentService $paymentService
     * @param FrontendSessionStorageFactoryContract $sessionStorage
     * @param Twig $twig
     * @param SettingsService $settingsService
     *
     * @return none
     */
    protected function registerPaymentRendering(Dispatcher $eventDispatcher,
                                                BasketRepositoryContract $basketRepository,
                                                PaymentHelper $paymentHelper,
                                                PaymentService $paymentService,
                                                FrontendSessionStorageFactoryContract $sessionStorage,
                                                Twig $twig,
                                                SettingsService $settingsService
                                                )
    {
        // Listen for the event that gets the payment method content
        $eventDispatcher->listen(
            GetPaymentMethodContent::class,
            function(GetPaymentMethodContent $event) use($basketRepository, $paymentHelper, $paymentService, $sessionStorage, $twig, $settingsService) {
                $paymentKey = $paymentHelper->getPaymentKeyByMop($event->getMop());
                if($paymentKey) {
                    $sessionStorage->getPlugin()->setValue('orderCurrency', null);
                    $paymentRequestData = $paymentService->generatePaymentParams($basketRepository->load(), $paymentKey);
                if((empty($paymentRequestData['paymentRequestData']['customer']['first_name']) && empty($paymentRequestData['paymentRequestData']['customer']['last_name'])) || empty($paymentRequestData['paymentRequestData']['customer']['email'])) {
                    $content = $paymentHelper->getTranslatedText('nn_first_last_name_error');
                    $contentType = 'errorCode';
                   if(empty($paymentRequestData['paymentRequestData']['customer']['email'])){
                    $content = $paymentHelper->getTranslatedText('nn_email_error');
                    $contentType = 'errorCode';   
                    }
                } else {
                    // Check if the birthday field needs to show for guaranteed payments
                     $showBirthday = ($settingsService->getPaymentSettingsValue('allow_b2b_customer', strtolower($paymentKey)) == false || (!isset($paymentRequestData['paymentRequestData']['customer']['billing']['company']) && !isset($paymentRequestData['paymentRequestData']['customer']['birth_date'])) ||  (isset($paymentRequestData['paymentRequestData']['customer']['birth_date']) && time() < strtotime('+18 years', strtotime($paymentRequestData['paymentRequestData']['customer']['birth_date'])))) ? true : false;
                    // Handle the Direct, Redirect and Form payments content type
                    if(in_array($paymentKey, ['NOVALNET_INVOICE', 'NOVALNET_PREPAYMENT', 'NOVALNET_CASHPAYMENT', 'NOVALNET_MULTIBANCO'])
                    || $paymentService->isRedirectPayment($paymentKey)
                    || ($paymentKey == 'NOVALNET_GUARANTEED_INVOICE' && $showBirthday == false)) {
                        $content = '';
                        $contentType = 'continue';
                    } elseif(in_array($paymentKey, ['NOVALNET_SEPA', 'NOVALNET_GUARANTEED_SEPA'])) {
                        $content = $twig->render('Novalnet::PaymentForm.NovalnetSepa',
                        [
                            'nnPaymentProcessUrl'   => $paymentService->getProcessPaymentUrl(),
                            'paymentMopKey'         => $paymentKey,
                            'paymentName'           => $paymentHelper->getCustomizedTranslatedText('template_' . strtolower($paymentKey)),
                            'showBirthday'          => $showBirthday
                        ]);
                        $contentType = 'htmlContent';
                    } elseif($paymentKey == 'NOVALNET_GUARANTEED_INVOICE' && $showBirthday == true) {
                        $content = $twig->render('Novalnet::PaymentForm.NovalnetGuaranteedInvoice',
                        [
                            'nnPaymentProcessUrl'   => $paymentService->getProcessPaymentUrl(),
                            'paymentMopKey'         => $paymentKey,
                            'paymentName'           => $paymentHelper->getCustomizedTranslatedText('template_' . strtolower($paymentKey)),
                        ]);
                        $contentType = 'htmlContent';
                    } elseif($paymentKey == 'NOVALNET_CC') {
                        $content = $twig->render('Novalnet::PaymentForm.NovalnetCc',
                        [
                            'nnPaymentProcessUrl'   => $paymentService->getProcessPaymentUrl(),
                            'paymentMopKey'         => $paymentKey,
                            'paymentName'           => $paymentHelper->getCustomizedTranslatedText('template_' . strtolower($paymentKey)),
                            'transactionData'       => $paymentService->getCreditCardAuthenticationCallData($basketRepository->load(), strtolower($paymentKey)),
                            'customData'            => !empty($paymentService->getCcFormFields()) ? $paymentService->getCcFormFields() : ''
                        ]);
                        $contentType = 'htmlContent';
                    } elseif($paymentKey == 'NOVALNET_MBWAY') {
                        $content = $twig->render('Novalnet::PaymentForm.NovalnetMBway',
                        [
                            'nnPaymentProcessUrl'   => $paymentService->getProcessPaymentUrl(),
                            'paymentMopKey'         => $paymentKey,
                            'paymentName'           => $paymentHelper->getCustomizedTranslatedText('template_' . strtolower($paymentKey)),
                        ]);
                        $contentType = 'htmlContent';
                    } elseif($paymentKey == 'NOVALNET_ACH') {
                        $content = $twig->render('Novalnet::PaymentForm.NovalnetACH',
                        [
                            'nnPaymentProcessUrl'   => $paymentService->getProcessPaymentUrl(),
                            'paymentMopKey'         => $paymentKey,
                            'paymentName'           => $paymentHelper->getCustomizedTranslatedText('template_' . strtolower($paymentKey)),
                            'AccountHolderName'     => $paymentRequestData['paymentRequestData']['customer']['first_name'] . ' ' . $paymentRequestData['paymentRequestData']['customer']['last_name'],
                        ]);
                        $contentType = 'htmlContent';
                    } elseif(in_array($paymentKey, ['NOVALNET_INSTALMENT_SEPA'])) {
                        $currency = $basketRepository->load()->currency;
                        // Instalment cycle amount information for the payment methods
                        $instalmentCycles = $settingsService->getPaymentSettingsValue('instament_cycles', strtolower($paymentKey));
                        $instalmentCyclesAmount = [];
                        foreach ($instalmentCycles as $cycle) {
                            $cycleAmount = ($paymentHelper->convertAmountToSmallerUnit($basketRepository->load()->basketAmount) / $cycle);
                            // Assign the cycle amount if th cycle amount greater than
                            if ($cycleAmount > 999) {
                                $instalmentCyclesAmount[$cycle] = str_replace('.', ',', sprintf('%0.2f', (($paymentHelper->convertAmountToSmallerUnit($basketRepository->load()->basketAmount) / $cycle ) / 100)));
                            }
                        }
                        $content = $twig->render('Novalnet::PaymentForm.NovalnetInstalmentSepa',
                        [
                            'nnPaymentProcessUrl'               => $paymentService->getProcessPaymentUrl(),
                            'paymentMopKey'                     => $paymentKey,
                            'paymentName'                       => $paymentHelper->getCustomizedTranslatedText('template_' . strtolower($paymentKey)),
                            'showBirthday'                      => $showBirthday,
                            'instalmentCyclesAmount'            => $instalmentCyclesAmount,
                            'currency'                          => $currency,
                            'netAmount'                         => $basketRepository->load()->basketAmount
                        ]);
                        $contentType = 'htmlContent';
                    } elseif(($paymentKey == 'NOVALNET_INSTALMENT_INVOICE' && $showBirthday == true) || ($paymentKey == 'NOVALNET_INSTALMENT_INVOICE' && $showBirthday == false && isset($paymentRequestData['paymentRequestData']['customer']['billing']['company']))) {
                        $currency = $basketRepository->load()->currency;
                        // Instalment cycle amount information for the payment methods
                        $instalmentCycles = $settingsService->getPaymentSettingsValue('instament_cycles', strtolower($paymentKey));
                        $instalmentCyclesAmount = [];
                        foreach ($instalmentCycles as $cycle) {
                            $cycleAmount = ($paymentHelper->convertAmountToSmallerUnit($basketRepository->load()->basketAmount) / $cycle);
                            // Assign the cycle amount if th cycle amount greater than
                            if ($cycleAmount > 999) {
                                $instalmentCyclesAmount[$cycle] = str_replace('.', ',', sprintf('%0.2f', (($paymentHelper->convertAmountToSmallerUnit($basketRepository->load()->basketAmount) / $cycle ) / 100)));
                            }
                        }
                        $content = $twig->render('Novalnet::PaymentForm.NovalnetInstalmentInvoice',
                        [
                            'nnPaymentProcessUrl'               => $paymentService->getProcessPaymentUrl(),
                            'paymentMopKey'                     => $paymentKey,
                            'paymentName'                       => $paymentHelper->getCustomizedTranslatedText('template_' . strtolower($paymentKey)),
                            'instalmentCyclesAmount'            => $instalmentCyclesAmount,
                            'currency'                          => $currency,
                            'netAmount'                         => str_replace('.', ',', sprintf('%0.2f',$basketRepository->load()->basketAmount)),
                            'showBirthday'                      => $showBirthday
                        ]);
                        $contentType = 'htmlContent';
                    }
                }
                $sessionStorage->getPlugin()->setValue('nnPaymentData', $paymentRequestData);

                // If payment before order creation option was set as 'No' the payment will be created initially
                if($settingsService->getPaymentSettingsValue('novalnet_order_creation') != true) { 
                    if(in_array($paymentKey, ['NOVALNET_INVOICE', 'NOVALNET_PREPAYMENT', 'NOVALNET_CASHPAYMENT', 'NOVALNET_MULTIBANCO']) || ($paymentKey == 'NOVALNET_GUARANTEED_INVOICE' && $showBirthday == false) || $paymentService->isRedirectPayment($paymentKey)) {
                        $paymentResponseData = $paymentService->performServerCall();
                        if(!empty($paymentResponseData) && ($paymentResponseData['result']['status'] == 'FAILURE' || $paymentResponseData['status'] == 'FAILURE')) {
                            $errorMsg = !empty($paymentResponseData['result']['status_text']) ? $paymentResponseData['result']['status_text'] : $paymentResponseData['status_text'];
                            $content = $errorMsg;
                            $contentType = 'errorCode';
                        } elseif($paymentService->isRedirectPayment($paymentKey)) {
                            if(!empty($paymentResponseData) && !empty($paymentResponseData['result']['redirect_url']) && !empty($paymentResponseData['transaction']['txn_secret'])) {
                                // Transaction secret used for the later checksum verification
                                $sessionStorage->getPlugin()->setValue('nnTxnSecret', $paymentResponseData['transaction']['txn_secret']);
                                $content = $twig->render('Novalnet::NovalnetPaymentRedirectForm', 
                                [
                                    'nnPaymentUrl' => $paymentResponseData['result']['redirect_url']
                                ]);
                                $contentType = 'htmlContent';
                            } else {
                                $content = $paymentResponseData['result']['status_text'];
                                $contentType = 'errorCode';
                            }
                        }
                    }
                }
                $event->setValue($content);
                $event->setType($contentType);
            }
        });
    }

     /**
     * Execute the Novalnet payment method
     *
     * @param Dispatcher $eventDispatcher
     * @param PaymentHelper $paymentHelper
     * @param PaymentService $paymentService
     * @param FrontendSessionStorageFactoryContract $sessionStorage
     * @param SettingsService $settingsService
     *
     * @return none
     */
    protected function registerPaymentExecute(Dispatcher $eventDispatcher,
                                              PaymentHelper $paymentHelper,
                                              PaymentService $paymentService,
                                              FrontendSessionStorageFactoryContract $sessionStorage,
                                              SettingsService $settingsService
                                             )
    {
        // Listen for the event that executes the payment
        $eventDispatcher->listen(
            ExecutePayment::class,
            function (ExecutePayment $event) use ($paymentHelper, $paymentService, $sessionStorage, $settingsService)
            {
                $paymentKey = $paymentHelper->getPaymentKeyByMop($event->getMop());
                if($paymentKey) {
                    $sessionStorage->getPlugin()->setValue('nnOrderNo',$event->getOrderId());
                    $sessionStorage->getPlugin()->setValue('mop',$event->getMop());
                    $sessionStorage->getPlugin()->setValue('paymentkey', $paymentKey);
                    $nnDoRedirect = $sessionStorage->getPlugin()->getValue('nnDoRedirect');
                    $nnGooglePayDoRedirect = $sessionStorage->getPlugin()->getValue('nnGooglePayDoRedirect');
                    if($settingsService->getPaymentSettingsValue('novalnet_order_creation') == true) {
                        $paymentResponseData = $paymentService->performServerCall();
                        if($paymentService->isRedirectPayment($paymentKey) || !empty($nnDoRedirect) || (!empty($nnGooglePayDoRedirect) && (string) $nnGooglePayDoRedirect === 'true')) {
                            if(!empty($paymentResponseData) && !empty($paymentResponseData['result']['redirect_url']) && !empty($paymentResponseData['transaction']['txn_secret'])) {
                                // Transaction secret used for the later checksum verification
                                $sessionStorage->getPlugin()->setValue('nnTxnSecret', $paymentResponseData['transaction']['txn_secret']);
                                $sessionStorage->getPlugin()->setValue('nnDoRedirect', null);
                                $sessionStorage->getPlugin()->setValue('nnGooglePayDoRedirect', null);
                                $event->setType('redirectUrl');
                                $event->setValue($paymentResponseData['result']['redirect_url']);
                            } else {
                               // Handle an error case and set the return type and value for the event.
                                $event->setType('error');
                                $event->setValue($paymentResponseData['result']['status_text']);
                            }
                        }
                    } else {
                            // Handle the further process to the order based on the payment response for direct payment payments
                            $paymentService->HandlePaymentResponse();
                   }
                }
            });
    }

    /**
     * Register the Novalnet events
     *
     * @param EventProceduresService $eventProceduresService
     *
     * @return none
     */
    protected function registerEvents(EventProceduresService $eventProceduresService)
    {
        // Event for Onhold - Capture Process
        $captureProcedureTitle = [
            'de' => 'Novalnet | Bestätigen',
            'en' => 'Novalnet | Confirm',
        ];
        $eventProceduresService->registerProcedure(
            'Novalnet',
            ProcedureEntry::EVENT_TYPE_ORDER,
            $captureProcedureTitle,
            '\Novalnet\Procedures\CaptureEventProcedure@run'
        );

        // Event for Onhold - Void Process
        $voidProcedureTitle = [
            'de' => 'Novalnet | Stornieren',
            'en' => 'Novalnet | Cancel',
        ];
        $eventProceduresService->registerProcedure(
            'Novalnet',
            ProcedureEntry::EVENT_TYPE_ORDER,
            $voidProcedureTitle,
            '\Novalnet\Procedures\VoidEventProcedure@run'
        );

        // Event for Onhold - Refund Process
        $refundProcedureTitle = [
            'de' =>  'Novalnet | Rückerstattung',
            'en' =>  'Novalnet | Refund',
        ];
        $eventProceduresService->registerProcedure(
            'Novalnet',
            ProcedureEntry::EVENT_TYPE_ORDER,
            $refundProcedureTitle,
            '\Novalnet\Procedures\RefundEventProcedure@run'
        );
        // Event for Instalment - Recurring Process
        $instalmentProcedureTitle = [
            'de' =>  'Novalnet | Gesamte Ratenzahlung stornieren',
            'en' =>  'Novalnet | Cancel All Instalment',
        ];
        $eventProceduresService->registerProcedure(
            'Novalnet',
            ProcedureEntry::EVENT_TYPE_ORDER,
            $instalmentProcedureTitle,
            '\Novalnet\Procedures\InstalmentAllCycleEventProcedure@run'
        );

        // Event for Instalment Cancel- Recurring Process
        $instalmentCancelProcedureTitle = [
            'de' =>  'Novalnet | Alle übrigen Installationen abbrechen',
            'en' =>  'Novalnet | Cancel All Remaining Instalment',
        ];
        $eventProceduresService->registerProcedure(
            'Novalnet',
            ProcedureEntry::EVENT_TYPE_ORDER,
            $instalmentCancelProcedureTitle,
            '\Novalnet\Procedures\InstalmentRemainingCycleCancelEventProcedure@run'
        );
    }

    /**
     * Display the Novalnet transaction comments in the invoice PDF
     *
     * @param Dispatcher $eventProceduresService
     * @param PaymentService $paymentService
     * @param PaymentHelper $paymentHelper
     * @param PaymentRepositoryContract $paymentRepository
     *
     * @return none
     */
    public function invoicePdfGenerationEvent(Dispatcher $eventDispatcher,
                                              PaymentService $paymentService,
                                              PaymentHelper $paymentHelper,
                                              PaymentRepositoryContract $paymentRepository
                                             )
    {
        $eventDispatcher->listen(
            OrderPdfGenerationEvent::class,
            function (OrderPdfGenerationEvent $event) use ($paymentService, $paymentHelper, $paymentRepository) {
            /** @var Order $order */
            $order = $event->getOrder();
            try {
                $payments = $paymentRepository->getPaymentsByOrderId($order->id);
                // Get Novalnet transaction details from the Novalnet database table
                $nnDbTxDetails = $paymentService->getDatabaseValues($order->id);
                if(!empty($nnDbTxDetails['plugin_version']) && strpos($nnDbTxDetails['paymentName'], 'novalnet') !== false) { // If Novalnet Payments do the invoice PDF process
                    $transactionComments = '';
                    $transactionComments .= $paymentService->displayTransactionComments($order->id, $payments);
                    $orderPdfGenerationModel = pluginApp(OrderPdfGeneration::class);
                    $orderPdfGenerationModel->advice = $paymentHelper->getTranslatedText('novalnet_details'). PHP_EOL . $transactionComments;
                    if ($event->getDocType() == Document::INVOICE) { // Add the comments into Invoice PDF document
                        $event->addOrderPdfGeneration($orderPdfGenerationModel);
                    }
                }
            } catch(\Exception $e) {
                $this->getLogger(__METHOD__)->error('Adding PDF comment failed for order ' . $order->id , $e);
            }

        });
    }
}
