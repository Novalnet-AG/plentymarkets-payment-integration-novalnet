<?php
/**
 * This file is used for post processing
 *
 * @author       Novalnet AG
 * @copyright(C) Novalnet
 * @license      https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 */

namespace Novalnet\Controllers;

use Novalnet\Helper\PaymentHelper;
use Novalnet\Services\PaymentService;
use Novalnet\Services\SettingsService;
use Novalnet\Constants\NovalnetConstants;
use Novalnet\Services\TransactionService;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Templates\Twig;
use \Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Payment\Contracts\PaymentRepositoryContract;
use Plenty\Modules\Payment\Models\PaymentProperty;
use Plenty\Plugin\Mail\Contracts\MailerContract;
use \stdClass;
use Plenty\Plugin\Log\Loggable;

/**
 * Class WebhookController
 *
 * @package Novalnet\Controllers
 */
class WebhookController extends Controller
{
    use Loggable;

    /**
     * @var eventData
     */
    protected $eventData = [];

    /**
     * @var Twig
     */
    private $twig;

    /**
     * @var PaymentHelper
     */
    private $paymentHelper;

    /**
     * @var paymentService
     */
    private $paymentService;

    /**
     * @var ipAllowed
     * @IP-ADDRESS Novalnet IP, is a fixed value, DO NOT CHANGE!!!!!
     */
    protected $ipAllowed = ['213.95.190.5', '213.95.190.7'];

    /**
     * @var SettingsService
    */
    private $settingsService;

    /**
     * @var eventType
     */
    protected $eventType;

    /**
     * @var eventTid
     */
    protected $eventTid;

    /**
     * @var parentTid
     */
    protected $parentTid;

    /**
     * @var object
     */
    private $orderDetails;

    /**
     * @var string
     */
    private $orderLanguage;

    /**
     * @var OrderRepositoryContract
     */
    private $orderRepository;

    /**
     * @var PaymentRepositoryContract
     */
    private $paymentRepository;

    /**
     * @var TransactionService
     */
    private $transactionService;

    /**
     * Webhook constructor.
     *
     * @param Request $request
     * @param Twig $twig
     * @param PaymentHelper $paymentHelper
     * @param PaymentService $paymentService
     * @param SettingsService $settingsService
     * @param OrderRepositoryContract $orderRepository
     * @param PaymentRepositoryContract $paymentRepository
     * @param TransactionService $transactionService
     */
    public function __construct(Request $request,
                                Twig $twig,
                                PaymentHelper $paymentHelper,
                                PaymentService $paymentService,
                                SettingsService $settingsService,
                                OrderRepositoryContract $orderRepository,
                                PaymentRepositoryContract $paymentRepository,
                                TransactionService $transactionService
                                )
    {
        $this->eventData            = $request->all();
        $this->twig                 = $twig;
        $this->paymentHelper        = $paymentHelper;
        $this->paymentService       = $paymentService;
        $this->settingsService      = $settingsService;
        $this->orderRepository      = $orderRepository;
        $this->paymentRepository    = $paymentRepository;
        $this->transactionService   = $transactionService;
    }

    /**
     * Handle the webhook process
     *
     */
    public function processWebhook()
    {
        // validated the IP Address
        $invalidIpMsg =  $this->validateIpAddress();
        if(!empty($invalidIpMsg)) {
           return $invalidIpMsg;    
        }
        // Validates the webhook params before processing
        $mandateEventParamMsg = $this->validateEventParams();
        if(!empty($mandateEventParamMsg)) {
           return $mandateEventParamMsg;    
        }
        // Set Event data
        $this->eventType = $this->eventData['event']['type'];
        $this->parentTid = !empty($this->eventData['event']['parent_tid']) ? $this->eventData['event']['parent_tid'] : $this->eventData['event']['tid'];
        $this->eventTid  = $this->eventData['event']['tid'];
        // Retreiving the shop's order information based on the transaction
        $this->orderDetails = $this->getOrderDetails();
        if(is_string($this->orderDetails))
        {
          return $this->orderDetails;
        }
        //  Get order language from the order object
        $this->orderLanguage = $this->getOrderLanguage();
        // Handle the individual webhook process
        if($this->eventData['result']['status'] == 'SUCCESS') {
            switch($this->eventType) {
                case 'PAYMENT':
                    return $this->renderTemplate('The Payment has been received');
                case 'TRANSACTION_CAPTURE':
                case 'TRANSACTION_CANCEL':
                    return $this->handleTransactionCaptureCancel();
                case 'TRANSACTION_UPDATE':
                    return $this->handleTransactionUpdate();
                case 'TRANSACTION_REFUND':
                    return $this->handleTransactionRefund();
                case 'CREDIT':
                    return $this->handleTransactionCredit();
                case 'CHARGEBACK':
                    return $this->handleChargeback();
                case 'PAYMENT_REMINDER_1':
                case 'PAYMENT_REMINDER_2':
                case 'SUBMISSION_TO_COLLECTION_AGENCY':
                    return $this->handlePaymentNotifications();
                default:
                    return $this->renderTemplate('The webhook notification has been received for the unhandled EVENT type ( ' . $this->eventType . ')' );
            }
        } else {
            return $this->renderTemplate('Status is not valid...The webhook notification has been received for the unhandled EVENT type ( ' . $this->eventType . ')' );
        }
    }

    /**
     * Render twig template for webhook message
     *
     * @param string $webhookMsg
     *
     * @return string
     */
    public function renderTemplate($webhookMsg)
    {
        return $this->twig->render('Novalnet::webhook.NovalnetWebhook', ['webhookMsg' => $webhookMsg]);
    }

    /**
     * Validate the IP control check
     *
     * @return bool|string
     */
    public function validateIpAddress()
    {
        $clientIp = $this->paymentHelper->getRemoteAddress();
        // Condition to check whether the webhook is called from authorized IP
        if(!in_array($clientIp, $this->ipAllowed) && $this->settingsService->getPaymentSettingsValue('novalnet_webhook_testmode') != true) {
            return $this->renderTemplate('Unauthorised access from the IP ' . $clientIp);
        }
    }

    /**
     * Validates the event parameters
     *
     * @return none
     */
    public function validateEventParams()
    {
        // Mandatory webhook params
        $requiredParams = ['event' => ['type', 'checksum', 'tid'], 'result' => ['status']];
        // Validate required parameters
        foreach($requiredParams as $category => $parameters) {
            if(empty($this->eventData[$category])) {
                // Could be a possible manipulation in the notification data
                return $this->renderTemplate('Required parameter category(' . $category. ') not received');
            } elseif(!empty($parameters)) {
                foreach($parameters as $parameter) {
                    if(empty($this->eventData[$category][$parameter])) {
                       // Could be a possible manipulation in the notification data
                       return $this->renderTemplate('Required parameter(' . $parameter . ') in the category (' . $category . ') not received');
                    }
                }
            }
        }

        // Validate the received checksum.
        $this->validateChecksum();
    }

    /**
     * Validate checksum
     *
     * @return none
     */
    public function validateChecksum()
    {
        $privatekey = $this->settingsService->getPaymentSettingsValue('novalnet_private_key');
        $tokenString  = $this->eventData['event']['tid'] . $this->eventData['event']['type'] . $this->eventData['result']['status'];
        if(isset($this->eventData['transaction']['amount'])) {
            $tokenString .= $this->eventData['transaction']['amount'];
        }
        if(isset($this->eventData['transaction']['currency'])) {
            $tokenString .= $this->eventData['transaction']['currency'];
        }
        if(!empty($privatekey)) {
            $tokenString .= $this->paymentHelper->reverseString($privatekey);
        }
        $generatedChecksum = hash('sha256', $tokenString);
        if($generatedChecksum !== $this->eventData['event']['checksum']) {
            return $this->renderTemplate('While notifying some data has been changed. The hash check failed');
        }
    }

    /**
     * Find and retrieves the shop order details for the Novalnet transaction
     *
     * @return object|string
     */
    public function getOrderDetails()
    {
        // Get the order details if the Novalnet transaction is alreay in the Novalnet database
        $novalnetOrderDetails = $this->transactionService->getTransactionData('tid', $this->parentTid);
        // Use the initial transaction details
        $novalnetOrderDetail = $novalnetOrderDetails[0];
        $additionalInfo = json_decode($novalnetOrderDetail->additionalInfo, true);
        // If both the order number from Novalnet and in shop is missing, then something is wrong
        if(empty($novalnetOrderDetail->orderNo) && empty($this->eventData['transaction']['order_no'])) {
            return $this->renderTemplate('Order reference not found for the TID ' . $this->parentTid);
        }
        // Get the Order No and proceed further
         $orderNo = !empty($novalnetOrderDetail->orderNo) ? $novalnetOrderDetail->orderNo : $this->eventData['transaction']['order_no'];
        // If the order in the Novalnet server to the order number in Novalnet database doesn't match, then there is an issue
        if(!empty($this->eventData['transaction']['order_no']) && !empty($novalnetOrderDetail->orderNo) && (($this->eventData['transaction']['order_no']) != $novalnetOrderDetail->orderNo)) {
            return $this->renderTemplate('Order reference not matching for the order number ' . $orderNo);
        }
        if(!empty($novalnetOrderDetail)) {
            $orderObj                     = pluginApp(stdClass::class);
            $orderObj->tid                = $this->parentTid;
            $orderObj->orderTotalAmount   = $novalnetOrderDetail->amount;
            $orderObj->orderPaidAmount    = 0; // Collect paid amount information from the novalnet DB
            $orderObj->orderNo            = $novalnetOrderDetail->orderNo;
            $orderObj->paymentName        = $novalnetOrderDetail->paymentName;
            $orderObj->currency           = $additionalInfo['currency'];
            // Get the Novalnet payment methods Id
            $mop = $this->paymentHelper->getPaymentMethodByKey(strtoupper($novalnetOrderDetail->paymentName));
            $orderObj->mopId = $mop[0];

            // Get the total paid amounts for an order
            if($this->eventType != 'CREDIT') {
                // Get the entire transaction details to the specific order
                $getOrderDetails = $this->transactionService ->getTransactionData('orderNo', $novalnetOrderDetail->orderNo);
                if(!empty($getOrderDetails)) {
                    $paidAmount = 0;
                    foreach($getOrderDetails as $getOrderDetail) {
                        $paidAmount += $getOrderDetail->callbackAmount;
                    }
                    $orderObj->orderPaidAmount = $paidAmount;
                }
            }
        } else {
            if(!empty($orderNo)) {
                $orderObj = $this->getOrderObject($orderNo);
                // Handle the communication break
                return $this->handleCommunicationBreak($orderObj);
            }
            else {
               return $this->renderTemplate('Transaction mapping failed ' . $orderNo);
            }
        }
        return $orderObj;
    }

    /**
     * Retrieves the order details from shop order ID
     *
     * @param int $orderId
     *
     * @return object
     */
    public function getOrderObject($orderId)
    {
        $orderId = (int)$orderId;
        try {
            $authHelper = pluginApp(AuthHelper::class);
            $orderRef = $authHelper->processUnguarded(function () use ($orderId) {
                $orderObj = $this->orderRepository->findById($orderId);
                return $orderObj;
            });
            return $orderRef;
        } catch (\Exception $e) {
               return null;
        }
    }

    /**
     * Handling communication breakup
     *
     * @param array $orderObj
     *
     * @return string
     */
    public function handleCommunicationBreak($orderObj)
    {
        //  Get order language from the order object
        $orderlanguage = $this->getOrderLanguage();
        foreach($orderObj->properties as $orderProperty) {
            if($orderProperty->typeId == '3' && $this->paymentHelper->getPaymentKeyByMop($orderProperty->value)) {  // Is the Novalnet payment methods
                $this->eventData['custom']['lang'] = $orderlanguage;
                $this->eventData['mop'] = $orderProperty->value;
                $this->eventData['payment_method'] = $this->paymentHelper->getPaymentKey($this->eventData['transaction']['payment_type']);
                $this->eventData['transaction']['system_version'] = NovalnetConstants::PLUGIN_VERSION;
                // Insert the transaction details into Novalnet DB
                $this->paymentService->insertPaymentResponse($this->eventData);
                // Create the payment to the plenty order
                $this->paymentHelper->createPlentyPayment($this->eventData);
                // Webhook executed message
                $webhookComments = $this->paymentHelper->getTranslatedText('nn_tid') . $this->eventData['transaction']['tid'];
                if(!empty($this->eventData['transaction']['test_mode'])) {
                    $webhookComments .= '<br>' . $this->paymentHelper->getTranslatedText('test_order') . $this->eventData['transaction']['test_mode'];
                }
                $webhookComments .= '<br>' . $this->eventData['result']['status_text'];
                $this->sendWebhookMail($webhookComments);
                return $this->renderTemplate($webhookComments);
            } else {
                return $this->renderTemplate('Webhook executed already');
            }
        }
    }

    /**
     * Get the order language based on the order
     *
     * @param object $orderObj
     *
     * @return string
     */
    public function getOrderLanguage()
    {
        $orderObj = $this->getOrderObject($this->eventData['transaction']['order_no']);
        foreach($orderObj->properties as $orderProperty) {
            if($orderProperty->typeId == '6' ) {
                $orderLanguage = $orderProperty->value;
                return $orderLanguage;
            }
        }
    }

    /**
     * Handling the Novalnet transaction authorization process
     *
     * @return string
     */
    public function handleTransactionCaptureCancel()
    {
        // If the transaction is captured, we update necessary alterations in DB
        if($this->eventType == 'TRANSACTION_CAPTURE') {
            $webhookComments = sprintf($this->paymentHelper->getTranslatedText('webhook_order_confirmation_text', $this->orderLanguage), date('d.m.Y'), date('H:i:s'));
        } else {
        $this->eventData['transaction']['amount'] = 0;
        $this->eventData['transaction']['currency'] = $this->orderDetails->currency;
            $webhookComments = sprintf($this->paymentHelper->getTranslatedText('webhook_transaction_cancellation', $this->orderLanguage), date('d.m.Y'), date('H:i:s'));
        }
        // Insert the updated transaction details into Novalnet DB
        $this->paymentService->insertPaymentResponse($this->eventData);
        // Booking Message
        $this->eventData['bookingText'] = $webhookComments;
        // Create the payment to the plenty order
        $this->paymentHelper->createPlentyPayment($this->eventData);
        $this->sendWebhookMail($webhookComments);
        return $this->renderTemplate($webhookComments);
    }

    /**
     * Handling the Novalnet transaction update process
     *
     * @return string
     */
    public function handleTransactionUpdate()
    {
        // Transaction status update process
        if($this->eventData['transaction']['update_type'] == 'STATUS') {
            // // If the transaction is cancelled
            if($this->eventData['transaction']['status'] == 'DEACTIVATED') {
                $webhookComments = sprintf($this->paymentHelper->getTranslatedText('webhook_transaction_cancellation', $this->orderLanguage), date('d.m.Y'), date('H:i:s'));
            } else {
                if(in_array($this->eventData['transaction']['status'], ['ON_HOLD', 'CONFIRMED'])) {
                    $webhookComments = sprintf($this->paymentHelper->getTranslatedText('webhook_update_confirmation_text', $this->orderLanguage), $this->parentTid, sprintf('%0.2f', ($this->eventData['transaction']['amount']/100)) , $this->eventData['transaction']['currency'], date('d.m.Y'), date('H:i:s'));
                    // If the transaction status is On-Hold
                    if($this->eventData['transaction']['status'] == 'ON_HOLD') {
                        $webhookComments = sprintf($this->paymentHelper->getTranslatedText('webhook_pending_to_onhold_status_change', $this->orderLanguage), $this->parentTid, date('d.m.Y'), date('H:i:s'));
                    }
                }
            }
            // Insert the updated transaction details into Novalnet DB
            $this->paymentService->insertPaymentResponse($this->eventData);

            // Booking Message
            $this->eventData['bookingText'] = $webhookComments;

            // Create the payment to the plenty order
            $this->paymentHelper->createPlentyPayment($this->eventData);

            return $this->renderTemplate($webhookComments);
        } else { // Due Date and Amount Update process
            // Due date update text
            $dueDateUpdateMessage = sprintf($this->paymentHelper->getTranslatedText('webhook_duedate_update_message', $this->orderLanguage), sprintf('%0.2f', ($this->eventData['transaction']['amount']/100)) , $this->eventData['transaction']['currency'], $this->eventData['transaction']['due_date']);
            // Amount update text
            $amountUpdateMessage = sprintf($this->paymentHelper->getTranslatedText('webhook_amount_update_message', $this->orderLanguage), sprintf('%0.2f', ($this->eventData['transaction']['amount']/100)) , $this->eventData['transaction']['currency'], date('d.m.Y'), date('H:i:s'));
            // Update the transaction details in the Novalnet DB
            $transactionDetails = $this->transactionService->updateTransactionData('orderNo', $this->eventData['transaction']['order_no'], $this->eventData);
            $webhookComments = (($this->eventData['transaction']['update_type'] == 'AMOUNT') ? $amountUpdateMessage : (($this->eventData['transaction']['update_type'] == 'DUE_DATE') ? $dueDateUpdateMessage : $dueDateUpdateMessage . $amountUpdateMessage));
            // Update the booking text in the latest payment entry
            $payments = $this->paymentRepository->getPaymentsByOrderId($this->eventData['transaction']['order_no']);
            // Get the end of the payment details
            $finalPaymentDetails = end($payments);
            $paymentProperty     = [];
            $paymentProperty[]   = $this->paymentHelper->getPaymentProperty(PaymentProperty::TYPE_BOOKING_TEXT, $webhookComments);
            $finalPaymentDetails->properties = $paymentProperty;
            // Update the booking text
            $this->paymentRepository->updatePayment($finalPaymentDetails);
            $this->sendWebhookMail($webhookComments);
            return $this->renderTemplate($webhookComments);
        }
    }

    /**
     * Handling the transaction refund process
     *
     * @return string
     */
    public function handleTransactionRefund()
    {
        // If refund is executing
        if(!empty($this->eventData['transaction']['refund']['amount'])) {
            $webhookComments = sprintf($this->paymentHelper->getTranslatedText('webhook_refund_execution', $this->orderLanguage), $this->parentTid, sprintf('%0.2f', ($this->eventData['transaction']['refund']['amount']/100)) , $this->eventData['transaction']['currency'], uniqid());
            if(!empty($this->eventData['transaction']['refund']['tid'])) {
                $webhookComments = sprintf($this->paymentHelper->getTranslatedText('webhook_new_tid_refund_execution', $this->orderLanguage), $this->parentTid, sprintf('%0.2f', ($this->eventData['transaction']['refund']['amount']/100)) , $this->eventData['transaction']['currency'], $this->eventTid);
            }
            // Get chargeback status it is happened for Full amount or Partially
            $refundStatus = $this->paymentService->getRefundStatus($this->eventData['transaction']['order_no'], $this->orderDetails->orderTotalAmount, $this->eventData['transaction']['refund']['amount']);
            // Set the refund status it Partial or Full refund
            $this->eventData['refund'] = $refundStatus;
            // Set the payment name
            $this->eventData['payment_method'] = $this->orderDetails->paymentName;
            // Insert the refund transaction details into Novalnet DB
            $this->paymentService->insertPaymentResponse($this->eventData);
            // Booking Message
            $this->eventData['bookingText'] = $webhookComments;
            // Create the payment to the plenty order
            $this->paymentHelper->createPlentyPayment($this->eventData);
            $this->sendWebhookMail($webhookComments);
            return $this->renderTemplate($webhookComments);
        }
    }

    /**
     * Handling the credit process
     *
     * @return string
     */
    public function handleTransactionCredit()
    {
        $webhookComments = sprintf($this->paymentHelper->getTranslatedText('webhook_initial_execution', $this->orderLanguage), $this->parentTid, sprintf('%0.2f', ($this->eventData['transaction']['amount']/100)), $this->eventData['transaction']['currency'], date('d.m.Y'), date('H:i:s'), $this->eventTid);
        if(in_array($this->eventData['transaction']['payment_type'], ['INVOICE_CREDIT', 'CASHPAYMENT_CREDIT', 'ONLINE_TRANSFER_CREDIT', 'MULTIBANCO_CREDIT'])) {
            if($this->orderDetails->orderTotalAmount >= $this->orderDetails->orderPaidAmount) {
                $this->eventData['unaccountable'] = 0;
            } else {
                $this->eventData['unaccountable'] = 1;
            }
        } else {
            $this->eventData['unaccountable'] = 1;
        }
        $this->eventData['credit'] = 1;
        // Booking Message
        $this->eventData['bookingText'] = $webhookComments;
        $this->eventData['mop'] = $this->orderDetails->mopId;
        // Set the payment name
        $this->eventData['payment_method'] = $this->orderDetails->paymentName;
        $orderTotalAmount = $this->orderDetails->orderTotalAmount;
        // Insert the refund details into Novalnet DB
        $this->paymentService->insertPaymentResponse($this->eventData, $this->parentTid, 0, $orderTotalAmount);
        // Create the payment to the plenty order
        $this->paymentHelper->createPlentyPayment($this->eventData);
        $this->sendWebhookMail($webhookComments);
        return $this->renderTemplate($webhookComments);
    }

    /**
     * Handling the chargeback process
     *
     * @return string
     */
    public function handleChargeback()
    {
        if($this->eventData['transaction']['payment_type'] == 'RETURN_DEBIT_SEPA') {
            $webhookComments = sprintf($this->paymentHelper->getTranslatedText('webhook_return_debit_execution', $this->orderLanguage), $this->parentTid, sprintf('%0.2f', ($this->eventData['transaction']['amount']/100)), $this->eventData['transaction']['currency'], date('d.m.Y'), date('H:i:s'), $this->eventTid);
        } elseif($this->eventData['transaction']['payment_type'] == 'REVERSAL') {
            $webhookComments = sprintf($this->paymentHelper->getTranslatedText('webhook_reversal_execution', $this->orderLanguage), $this->parentTid, sprintf('%0.2f', ($this->eventData['transaction']['amount']/100)), $this->eventData['transaction']['currency'], date('d.m.Y'), date('H:i:s'), $this->eventTid);
        } else {
            $webhookComments = sprintf($this->paymentHelper->getTranslatedText('webhook_chargeback_execution', $this->orderLanguage), $this->parentTid, sprintf('%0.2f', ($this->eventData['transaction']['amount']/100)) , $this->eventData['transaction']['currency'], date('d.m.Y'), date('H:i:s'), $this->eventTid);
        }
        $RefundOrderTotalAmount = $this->orderDetails->orderTotalAmount;
        // Insert the refund details into Novalnet DB
        $this->paymentService->insertPaymentResponse($this->eventData, $this->parentTid, $RefundOrderTotalAmount, 0);
        // Get chargeback status it is happened for Full amount or Partially
        $refundStatus = $this->paymentService->getRefundStatus($this->eventData['transaction']['order_no'], $this->orderDetails->orderTotalAmount, $this->eventData['transaction']['amount']);
        // Set the refund status it Partial or Full refund
        $this->eventData['refund'] = $refundStatus;
        // Booking Message
        $this->eventData['bookingText'] = $webhookComments;
        $this->eventData['mop'] = $this->orderDetails->mopId;
        // Create the payment to the plenty order
        $this->paymentHelper->createPlentyPayment($this->eventData);
        $this->sendWebhookMail($webhookComments);
        return $this->renderTemplate($webhookComments);
    }

    /**
     * Handling the Payment notification process
     *
     * @return string
     */
    public function handlePaymentNotifications()
    {
        if(in_array($this->eventType, ['PAYMENT_REMINDER_1', 'PAYMENT_REMINDER_2'])) {
            $reminderNumber = preg_replace("/[^0-9]/", '', $this->eventType);
            $webhookComments = sprintf($this->paymentHelper->getTranslatedText('webhook_payment_reminder_text', $this->orderLanguage), $reminderNumber);
        } else {
            $webhookComments = sprintf($this->paymentHelper->getTranslatedText('webhook_collection_submission', $this->orderLanguage), $this->eventData['collection']['reference']);
        }
        $this->eventData['unaccountable'] = 1;
        // Booking Message
        $this->eventData['bookingText'] = $webhookComments;
        // Create the payment to the plenty order
        $this->paymentHelper->createPlentyPayment($this->eventData);
        $this->sendWebhookMail($webhookComments);
        return $this->renderTemplate($webhookComments);
    }
    
    /**
     * Send the webhook script email for the execution
     *
     * @param string $mailContent
     *
     * @return none
     */
    public function sendWebhookMail($mailContent)
    {
        try
        {
            $toAddress  = $this->settingsService->getPaymentSettingsValue('novalnet_webhook_email_to');
            if($toAddress)
            {
                $subject = 'Novalnet Callback Script Access Report';
                $mailer  = pluginApp(MailerContract::class);
                $mailer->sendHtml($mailContent, $toAddress, $subject);
            }
        } catch (\Exception $e) {
            $this->getLogger(__METHOD__)->error('Novalnet::sendWebhookMail', $e);
        }
    }
}
