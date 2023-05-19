<?php
/**
 * This file is used for handling the payment refund event procedure
 *
 * @author       Novalnet AG
 * @copyright(C) Novalnet
 * @license      https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 */
namespace Novalnet\Procedures;

use Novalnet\Helper\PaymentHelper;
use Novalnet\Services\SettingsService;
use Novalnet\Services\PaymentService;
use Novalnet\Constants\NovalnetConstants;
use Plenty\Modules\EventProcedures\Events\EventProceduresTriggered;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Order\Models\OrderType;
use Plenty\Modules\Payment\Contracts\PaymentRepositoryContract;
use Plenty\Plugin\Log\Loggable;

/**
 * Class RefundEventProcedure
 *
 * @package Novalnet\Procedures
 */
class RefundEventProcedure
{
    use Loggable;

    /**
     * @var PaymentRepositoryContract
     */
    private $paymentRepository;

    /**
     *
     * @var PaymentHelper
     */
    private $paymentHelper;

    /**
     * @var SettingsService
    */
    private $settingsService;

    /**
     *
     * @var PaymentService
     */
    private $paymentService;

    /**
     * Constructor.
     *
     * @param PaymentRepositoryContract $paymentRepository
     * @param PaymentHelper $paymentHelper
     * @param SettingsService $settingsService
     * @param PaymentService $paymentService
     */
    public function __construct(PaymentRepositoryContract $paymentRepository,
                                PaymentHelper $paymentHelper,
                                SettingsService $settingsService,
                                PaymentService $paymentService)
    {
        $this->paymentRepository = $paymentRepository;
        $this->paymentHelper     = $paymentHelper;
        $this->settingsService   = $settingsService;
        $this->paymentService    = $paymentService;
    }

    /**
     * @param EventProceduresTriggered $eventTriggered
     *
     */
    public function run(EventProceduresTriggered $eventTriggered)
    {
        try {
            /* @var $order Order */
            $order = $eventTriggered->getOrder();
            $parentOrderId = $order->id;
            // Checking order type and set the parent and child Order Id
            if($order->typeId == OrderType::TYPE_CREDIT_NOTE) {
                foreach($order->orderReferences as $orderReference) {
                    $parentOrderId = $orderReference->originOrderId;
                    $childOrderId = $orderReference->orderId;
                }
            }
            // Get the payment details
            $paymentDetails = $this->paymentRepository->getPaymentsByOrderId($parentOrderId);
            // Get the payment currency
            foreach($paymentDetails as $paymentDetail) {
                $paymentCurrency = $paymentDetail->currency;
            }
            // Get the proper order amount even the system currency and payment currency are differ
            if(count($order->amounts) > 1) {
                foreach($order->amounts as $amount) {
                    if($paymentCurrency == $amount->currency) {
                       $refundAmount = (float) $amount->invoiceTotal; // Get the refunding amount
                    }
                }
            } else {
                 $refundAmount = (float) $order->amounts[0]->invoiceTotal; // Get the refunding amount
            }
            // Load the order language
            foreach($order->properties as $orderProperty) {
                if($orderProperty->typeId == '6' ) {
                $orderLanguage = $orderProperty->value;
                }
            }
            // Get necessary information for the refund process
            $transactionDetails = $this->paymentService->getDetailsFromPaymentProperty($parentOrderId);
            if(in_array($transactionDetails['tx_status'], ['PENDING', 'CONFIRMED'])) {
                // Novalnet access key
                $privateKey = $this->settingsService->getPaymentSettingsValue('novalnet_private_key');
                $paymentRequestData = [];
                $paymentRequestData['transaction']['tid']    = $transactionDetails['tid'];
                $paymentRequestData['transaction']['amount'] = (float) $refundAmount * 100;
                $paymentRequestData['custom']['lang'] = strtoupper($orderLanguage);
                // Send the payment capture/void call to Novalnet server
                $paymentResponseData = $this->paymentHelper->executeCurl($paymentRequestData, NovalnetConstants::PAYMENT_REFUND_URL, $privateKey);
                $paymentResponseData = array_merge($paymentRequestData, $paymentResponseData);
                // If refund is successful
                if($paymentResponseData['result']['status'] == 'SUCCESS' && in_array($paymentResponseData['transaction']['status'], ['PENDING', 'CONFIRMED', 'DEACTIVATED'])) {
                    // Booking text
                    if(!empty($paymentResponseData['transaction']['refund']['tid'])) {
                        $paymentResponseData['bookingText'] = sprintf($this->paymentHelper->getTranslatedText('refund_message_new_tid', $orderLanguage), $paymentResponseData['transaction']['tid'], sprintf('%0.2f', ($paymentResponseData['transaction']['refund']['amount'] / 100)) , $paymentCurrency, $paymentResponseData['transaction']['refund']['tid']);
                    } else {
                        $paymentResponseData['bookingText'] = sprintf($this->paymentHelper->getTranslatedText('refund_message', $orderLanguage), $paymentResponseData['transaction']['tid'], sprintf('%0.2f', ($paymentResponseData['transaction']['refund']['amount'] / 100)), $paymentCurrency, uniqid());
                    }
                    // Insert the refund details into Novalnet DB
                    $this->paymentService->insertPaymentResponse($paymentResponseData);
                    // Get refund status it is happened for Full amount or Partially
                    $refundStatus = $this->paymentService->getRefundStatus($paymentResponseData['transaction']['order_no'], $paymentResponseData['transaction']['amount'], $paymentResponseData['transaction']['refund']['amount']);
                    // Set the refund status it Partial or Full refund
                    $paymentResponseData['refund'] = $refundStatus;
                    if($order->typeId == OrderType::TYPE_CREDIT_NOTE) { // Create refund entry in credit note order
                        $paymentResponseData['childOrderId'] = $childOrderId;
                        $this->paymentHelper->createRefundPayment($paymentDetails, $paymentResponseData, $paymentResponseData['bookingText']);
                    } else {
                        // Get the Novalnet payment methods Id
                        $mop = $this->paymentHelper->getPaymentMethodByKey(strtoupper($transactionDetails['paymentName']));
                        $paymentResponseData['mop'] = $mop[0];
                        // Create the payment to the plenty order
                        $this->paymentHelper->createPlentyPayment($paymentResponseData);
                    }
                } else {
                    $this->getLogger(__METHOD__)->error('Novalnet::Refund failed ' . $paymentResponseData['result']['status_text'], $e);
                }
            } else {
                $this->getLogger(__METHOD__)->error('Novalnet::Refund Failed ' . $transactionDetails['order_no'], 'Transaction status is not valid');
            }
        } catch(\Exception $e) {
            $this->getLogger(__METHOD__)->error('Novalnet::Refund failed ' . $transactionDetails['order_no'], $e);
        }
    }
}
