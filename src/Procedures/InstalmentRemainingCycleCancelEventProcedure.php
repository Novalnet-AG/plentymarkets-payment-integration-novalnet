<?php
/**
 * This file is used for handling the payment cancel event procedure
 *
 * @author       Novalnet AG
 * @copyright(C) Novalnet
 * @license      https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 */
namespace Novalnet\Procedures;

use Plenty\Modules\EventProcedures\Events\EventProceduresTriggered;
use Plenty\Modules\Order\Models\Order;
use Novalnet\Services\PaymentService;
use Novalnet\Constants\NovalnetConstants;
use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;
use Plenty\Modules\Plugin\DataBase\Contracts\Query;
use Novalnet\Models\TransactionLog;
use Plenty\Plugin\Log\Loggable;

/**
 * Class VoidEventProcedure
 *
 * @package Novalnet\Procedures
 */
class InstalmentRemainingCycleCancelEventProcedure
{
     /**
     *
     * @var PaymentService
     */
    private $paymentService;

    /**
     * Constructor.
     *
     * @param PaymentService $paymentService
     */

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * @param EventProceduresTriggered $eventTriggered
     *
     */
    public function run(EventProceduresTriggered $eventTriggered)
    {
        /* @var $order Order */
        $order = $eventTriggered->getOrder();
        // Load the order language
        foreach($order->properties as $orderProperty) {
            if($orderProperty->typeId == '6' ) {
                $orderLanguage = $orderProperty->value;
            }
        }
        // Get necessary information for the capture process
        $database = pluginApp(DataBase::class);
        $transactionDetails = $database->query(TransactionLog::class)->where('orderNo', '=', $order->id)->limit(1)->get();
        $transactionDetails = (array) $transactionDetails[0];
        $transactionDetails['lang'] = $orderLanguage;
        $transactionDetails['cancel_type'] = 'CANCEL_REMAINING_CYCLES';
        // Call the Void process for the On-Hold payments
        $this->paymentService->doInstalmentVoid($transactionDetails, NovalnetConstants::INSTALMENT_VOID_URL);
    }
}
