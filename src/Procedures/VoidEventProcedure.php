<?php
/**
 * This module is used for real time processing of
 * Novalnet payment module of customers.
 * This free contribution made by request.
 * 
 * If you have found this script useful a small
 * recommendation as well as a comment on merchant form
 * would be greatly appreciated.
 *
 * @author       Novalnet AG
 * @copyright(C) Novalnet
 * All rights reserved. https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 */
 
namespace Novalnet\Procedures;

use Plenty\Modules\EventProcedures\Events\EventProceduresTriggered;
use Plenty\Modules\Order\Models\Order;
use Plenty\Plugin\Log\Loggable;
use Plenty\Modules\Payment\Contracts\PaymentRepositoryContract;
use Novalnet\Services\PaymentService;

/**
 * Class VoidEventProcedure
 */
class VoidEventProcedure
{
	use Loggable;
	
	/**
	 *
	 * @var PaymentService
	 */
	private $paymentService;
	
	/**
	 * Constructor.
	 *
	 * @param PaymentHelper $paymentHelper
	 * @param PaymentService $paymentService
	 */
	 
    public function __construct(PaymentService $paymentService)
    {
	    $this->paymentService  = $paymentService;
	}	
	
    /**
     * @param EventProceduresTriggered $eventTriggered
     * 
     */
    public function run(
        EventProceduresTriggered $eventTriggered
    ) {
        /* @var $order Order */
	 
	   $order = $eventTriggered->getOrder(); 
	  
	   $payments = pluginApp(\Plenty\Modules\Payment\Contracts\PaymentRepositoryContract::class);  
       $paymentDetails = $payments->getPaymentsByOrderId($order->id);
	   $paymentKey = $paymentDetails[0]->method->paymentKey;
	   $key = $this->paymentService->getkeyByPaymentKey($paymentKey);
	    
	    foreach ($paymentDetails as $paymentDetail)
		{
			$property = $paymentDetail->properties;
			foreach($property as $proper)
			{
				  if($proper->typeId == 1)
				  {
						$tid = $proper->value;
				  }
				  if($proper->typeId == 30)
				  {
						$status = $proper->value;
				  }
			}
		}

	    if(in_array($status, ['85', '91', '98', '99'])) {
        $this->paymentService->doCaptureVoid($order, $paymentDetails, $tid, $key, '');
	    } 
    }
}
