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

namespace Novalnet\Helper;

use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;
use Plenty\Modules\Payment\Models\Payment;
use Plenty\Modules\Payment\Models\PaymentProperty;
use Plenty\Modules\Payment\Contracts\PaymentRepositoryContract;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Payment\Contracts\PaymentOrderRelationRepositoryContract;
use Plenty\Modules\Order\Models\Order;
use Plenty\Plugin\Log\Loggable;
use Plenty\Plugin\Translation\Translator;
use Plenty\Plugin\ConfigRepository;
use \Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Comment\Contracts\CommentRepositoryContract;
use Plenty\Modules\Order\Shipping\Countries\Contracts\CountryRepositoryContract;
use Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract;
use Novalnet\Constants\NovalnetConstants;

/**
 * Class PaymentHelper
 *
 * @package Novalnet\Helper
 */
class PaymentHelper
{
    use Loggable;

    /**
     *
     * @var PaymentMethodRepositoryContract
     */
    private $paymentMethodRepository;
       
    /**
     *
     * @var PaymentRepositoryContract
     */
    private $paymentRepository;
    
    /**
     *
     * @var OrderRepositoryContract
     */
    private $orderRepository;

    /**
     *
     * @var PaymentOrderRelationRepositoryContract
     */
    private $paymentOrderRelationRepository;

     /**
     *
     * @var orderComment
     */
    private $orderComment;

    /**
    *
    * @var $configRepository
    */
    public $config;

    /**
    *
    * @var $countryRepository
    */
    private $countryRepository;

    /**
    *
    * @var $sessionStorage
    */
    private $sessionStorage;
        

    /**
     * Constructor.
     *
     * @param PaymentMethodRepositoryContract $paymentMethodRepository
     * @param PaymentRepositoryContract $paymentRepository
     * @param OrderRepositoryContract $orderRepository
     * @param PaymentOrderRelationRepositoryContract $paymentOrderRelationRepository
     * @param CommentRepositoryContract $orderComment
     * @param ConfigRepository $configRepository
     * @param FrontendSessionStorageFactoryContract $sessionStorage
     * @param CountryRepositoryContract $countryRepository
     */
    public function __construct(PaymentMethodRepositoryContract $paymentMethodRepository,
                                PaymentRepositoryContract $paymentRepository,
                                OrderRepositoryContract $orderRepository,
                                PaymentOrderRelationRepositoryContract $paymentOrderRelationRepository,
                                CommentRepositoryContract $orderComment,
                                ConfigRepository $configRepository,
                                FrontendSessionStorageFactoryContract $sessionStorage,
                                CountryRepositoryContract $countryRepository
                              )
    {
        $this->paymentMethodRepository        = $paymentMethodRepository;
        $this->paymentRepository              = $paymentRepository;
        $this->orderRepository                = $orderRepository;
        $this->paymentOrderRelationRepository = $paymentOrderRelationRepository;
        $this->orderComment                   = $orderComment;      
        $this->config                         = $configRepository;
        $this->sessionStorage                 = $sessionStorage;
        $this->countryRepository              = $countryRepository;
    }

    /**
     * Load the ID of the payment method
     * Return the ID for the payment method found
     * 
     * @param string $paymentKey
     * @return string|int
     */
    public function getPaymentMethodByKey($paymentKey)
    {
        $paymentMethods = $this->paymentMethodRepository->allForPlugin('plenty_novalnet');
        
        if(!is_null($paymentMethods))
        {
            foreach($paymentMethods as $paymentMethod)
            {
                if($paymentMethod->paymentKey == $paymentKey)
                {
                    return [$paymentMethod->id, $paymentMethod->paymentKey, $paymentMethod->name];
                }
            }
        }
        return 'no_paymentmethod_found';
    }

    /**
     * Load the ID of the payment method
     * Return the payment key for the payment method found
     *
     * @param int $mop
     * @return string|bool
     */
    public function getPaymentKeyByMop($mop)
    {
        $paymentMethods = $this->paymentMethodRepository->allForPlugin('plenty_novalnet');

        if(!is_null($paymentMethods))
        {
            foreach($paymentMethods as $paymentMethod)
            {
                if($paymentMethod->id == $mop)
                {
                    return $paymentMethod->paymentKey;
                }
            }
        }
        return false;
    }

    /**
     * Create the Plenty payment
     * Return the Plenty payment object
     *
     * @param array $requestData
     * @return object
     */
    public function createPlentyPayment($requestData)
    {        
        /** @var Payment $payment */
        $payment = pluginApp(\Plenty\Modules\Payment\Models\Payment::class);
        
        $payment->mopId           = (int) $requestData['mop'];
        $payment->transactionType = Payment::TRANSACTION_TYPE_BOOKED_POSTING;
        $payment->status          = ($requestData['type'] == 'confirmed' ? Payment::STATUS_APPROVED : ($requestData['type'] == 'cancel' ? Payment::STATUS_CANCELED : Payment::STATUS_CAPTURED));
        $payment->currency        = $requestData['currency'];
        $payment->amount          = $requestData['paid_amount'];
        if(isset($requestData['booking_text']) && !empty($requestData['booking_text'])) {
        $bookingText = $requestData['booking_text'];
        } else {
        $bookingText = $requestData['tid'];
        }
        $transactionId = $requestData['tid'];
        if(!empty($requestData['type']) && $requestData['type'] == 'debit')
        {
            $payment->type = $requestData['type'];
            $payment->status = Payment::STATUS_REFUNDED;
        }
        
        $invoicePrepaymentDetails =  [
              'invoice_bankname'  => $requestData['invoice_bankname'],
              'invoice_bankplace' => $requestData['invoice_bankplace'],
              'invoice_iban'      => $requestData['invoice_iban'],
              'invoice_bic'       => $requestData['invoice_bic'],
              'due_date'          => $requestData['due_date'],
              'invoice_type'      => $requestData['invoice_type'],
              'invoice_account_holder' => $requestData['invoice_account_holder']
               ];
           
        $invoiceDetails = json_encode($invoicePrepaymentDetails);
        $paymentProperty     = [];
        $paymentProperty[]   = $this->getPaymentProperty(PaymentProperty::TYPE_BOOKING_TEXT, $bookingText);
        $paymentProperty[]   = $this->getPaymentProperty(PaymentProperty::TYPE_TRANSACTION_ID, $transactionId);
        $paymentProperty[]   = $this->getPaymentProperty(PaymentProperty::TYPE_ORIGIN, Payment::ORIGIN_PLUGIN);
        $paymentProperty[]   = $this->getPaymentProperty(PaymentProperty::TYPE_EXTERNAL_TRANSACTION_STATUS, $requestData['tid_status']);
        
        if (in_array($requestData['payment_id'], ['27','41'])) {
            $paymentProperty[]   = $this->getPaymentProperty(PaymentProperty::TYPE_ACCOUNT_OF_RECEIVER, $invoiceDetails); 
        }
        
        if ($requestData['payment_id'] == '59') {
        $cashpayment_comments = $this->getCashPaymentComments($requestData);
        $paymentProperty[]   = $this->getPaymentProperty(PaymentProperty::TYPE_PAYMENT_TEXT, $cashpayment_comments);    
        }
        $payment->properties = $paymentProperty;
        $paymentObj = $this->paymentRepository->createPayment($payment);

        $this->assignPlentyPaymentToPlentyOrder($paymentObj, (int)$requestData['order_no']);
    }
    

    /**
     * Get the payment property object
     *
     * @param mixed $typeId
     * @param mixed $value
     * @return object
     */
    public function getPaymentProperty($typeId, $value)
    {
        /** @var PaymentProperty $paymentProperty */
        $paymentProperty = pluginApp(\Plenty\Modules\Payment\Models\PaymentProperty::class);

        $paymentProperty->typeId = $typeId;
        $paymentProperty->value  = (string) $value;

        return $paymentProperty;
    }

    /**
     * Assign the payment to an order in plentymarkets.
     *
     * @param Payment $payment
     * @param int $orderId
     */
    public function assignPlentyPaymentToPlentyOrder(Payment $payment, int $orderId)
    {
        try {
        /** @var \Plenty\Modules\Authorization\Services\AuthHelper $authHelper */
        $authHelper = pluginApp(AuthHelper::class);
        $authHelper->processUnguarded(
                function () use ($payment, $orderId) {
                //unguarded
                $order = $this->orderRepository->findOrderById($orderId);
                if (! is_null($order) && $order instanceof Order)
                {
                    $this->paymentOrderRelationRepository->createOrderRelation($payment, $order);
                }
            }
        );
        } catch (\Exception $e) {
            $this->getLogger(__METHOD__)->error('Novalnet::assignPlentyPaymentToPlentyOrder', $e);
        }
    }

    /**
     * Update order status by order id
     *
     * @param int $orderId
     * @param float $statusId
     */
    public function updateOrderStatus($orderId, $statusId)
    {
        try {
            /** @var \Plenty\Modules\Authorization\Services\AuthHelper $authHelper */
            $authHelper = pluginApp(AuthHelper::class);
            $authHelper->processUnguarded(
                    function () use ($orderId, $statusId) {
                    //unguarded
                    $order = $this->orderRepository->findOrderById($orderId);

                    if (!is_null($order) && $order instanceof Order) {
                        $status['statusId'] = (float) $statusId;
                        $this->orderRepository->updateOrder($status, $orderId);
                    }
                }
            );
        } catch (\Exception $e) {
            $this->getLogger(__METHOD__)->error('Novalnet::updateOrderStatus', $e);
        }
    }

    /**
     * Get Novalnet status message.
     *
     * @param array $response
     * @return string
     */
    public function getNovalnetStatusText($response)
    {
       return ((!empty($response['status_desc'])) ? $response['status_desc'] : ((!empty($response['status_text'])) ? $response['status_text'] : ((!empty($response['status_message']) ? $response['status_message'] : ((in_array($response['status'], ['90', '100'])) ? $this->getTranslatedText('payment_success') : $this->getTranslatedText('payment_not_success'))))));
    }

    /**
     * Execute curl process
     *
     * @param array $data
     * @param string $url
     * @return array
     */
    public function executeCurl($data, $url)
    {
        try {
            $curl = curl_init();
            // Set cURL options
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            $gateway_timeout = $this->getNovalnetConfig('novalnet_gateway_timeout');
            $curlTimeOut  = (!empty($gateway_timeout) && $gateway_timeout > 240) ? $gateway_timeout : 240;
            curl_setopt($curl, CURLOPT_TIMEOUT, $curlTimeOut);

            if (!empty($this->getNovalnetConfig('novalnet_proxy_server'))) {
               curl_setopt($curl, CURLOPT_PROXY, $this->getNovalnetConfig('novalnet_proxy_server'));
            }
            $response = curl_exec($curl);
            $errorText = curl_error($curl);
            curl_close($curl);
            return [
                'response' => $response,
                'error'    => $errorText
            ];
        } catch (\Exception $e) {
            $this->getLogger(__METHOD__)->error('Novalnet::executeCurlError', $e);
        }
    }


    /**
     * Get the payment method executed to store in the transaction log for future use
     *
     * @param int  $paymentKey
     * @param bool $isPrepayment
     * @return string
     */
    public function getPaymentNameByResponse($paymentKey, $isPrepayment = false)
    {
        // Doing this as the payment key for both the invoice and prepayment are same
        if ($isPrepayment)
        {
            return 'novalnet_prepayment';
        }

        $paymentMethodName = [
            '6'   => 'novalnet_cc',
            '27'  => 'novalnet_invoice',
            '33'  => 'novalnet_sofort',
            '34'  => 'novalnet_paypal',
            '37'  => 'novalnet_sepa',
            '40'  => 'novalnet_sepa',
            '41'  => 'novalnet_invoice',
            '49'  => 'novalnet_ideal',
            '50'  => 'novalnet_eps',
            '59'  => 'novalnet_cashpayment',
            '69'  => 'novalnet_giropay',
            '78'  => 'novalnet_przelewy',
        ];
        return $paymentMethodName[$paymentKey];
    }

    /**
     * Generates 16 digit unique number
     *
     * @return int
     */
    public function getUniqueId()
    {
        return rand(1000000000000000, 9999999999999999);
    }

    /**
     * Encode the input data based on the secure algorithm
     *
     * @param mixed $data
     * @param integer $uniqid
     *
     * @return string
     */
    public function encodeData($data, $uniqid)
    {
        $accessKey = $this->getNovalnetConfig('novalnet_access_key');

        # Encryption process
        $encodedData = htmlentities(base64_encode(openssl_encrypt($data, "aes-256-cbc", $accessKey, 1, $uniqid)));

        # Response
        return $encodedData;
    }

    /**
     * Decode the input data based on the secure algorithm
     *
     * @param mixed $data
     * @param mixed $uniqid
     *
     * @return string
     */
    public function decodeData($data, $uniqid)
    {
        $accessKey = $this->getNovalnetConfig('novalnet_access_key');

        # Decryption process
        $decodedData = openssl_decrypt(base64_decode($data), "aes-256-cbc", $accessKey, 1, $uniqid);

        # Response
        return $decodedData;
    }

    /**
     * Generates an unique hash with the encoded data
     *
     * @param array $data
     *
     * @return string
     */
    public function generateHash($data)
    {
        if (!function_exists('hash'))
        {
            return 'Error: Function n/a';
        }

        $accessKey = $this->getNovalnetConfig('novalnet_access_key');
        $strRevKey = $this->reverseString($accessKey);

        # Generates a hash to be sent with the sha256 mechanism
        return hash('sha256', ($data['auth_code'] . $data['product'] . $data['tariff'] . $data['amount'] . $data['test_mode']. $data['uniqid'] . $strRevKey));
    }

    /**
     * Reverse the given string
     *
     * @param string $str
     * @return string
     */
    public function reverseString($str)
    {
        $string = '';
        // Find string length
        $len = strlen($str);
        // Loop through it and print it reverse
        for($i=$len-1;$i>=0;$i--)
        {
            $string .= $str[$i];
        }
        return $string;
    }

   /**
    * Get the translated text for the Novalnet key
    * @param string $key
    * @param string $lang
    *
    * @return string
    */
    public function getTranslatedText($key, $lang = null)
    {
        $translator = pluginApp(Translator::class);

        return $lang == null ? $translator->trans("Novalnet::PaymentMethod.$key") : $translator->trans("Novalnet::PaymentMethod.$key",[], $lang);
    }

    /**
     * Check given string is UTF-8
     *
     * @param string $str
     * @return string
     */
    public function checkUtf8Character($str)
    {
        $decoded = utf8_decode($str);
        if(mb_detect_encoding($decoded , 'UTF-8', true) === false)
        {
            return $str;
        }
        else
        {
            return $decoded;
        }
    }

    /**
     * Retrieves the original end-customer address with and without proxy
     *
     * @return string
     */
    public function getRemoteAddress()
    {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];

        foreach ($ipKeys as $key)
        {
            if (array_key_exists($key, $_SERVER) === true)
            {
                foreach (explode(',', $_SERVER[$key]) as $ip)
                {
                    return $ip;
                }
            }
        }
    }

    /**
     * Retrieves the server address
     *
     * @return string
     */
    public function getServerAddress()
    {
        return $_SERVER['SERVER_ADDR'];
    }

    /**
     * Get merchant configuration parameters by trimming the whitespace
     *
     * @param string $key
     * @return mixed
     */
    public function getNovalnetConfig($key)
    {
        return preg_replace('/\s+/', '', $this->config->get("Novalnet.$key"));
    }

    /**
     * Get merchant configuration parameters by trimming the whitespace
     *
     * @param int $mop
     * @param string $string
     * @return mixed
     */
    public function getPaymentStatusByConfig($mop, $string)
    {
        $name = (String) $this->getPaymentKeyByMop($mop);
        $statusString = 'Novalnet.' . strtolower($name) . $string;

        return preg_replace('/\s+/', '', $this->config->get($statusString));
    }

    /**
    * Get merchant configuration parameters by trimming the whitespace
    *
    * @param string $string
    * @param string $delimeter
    * @return array
    */
    public function convertStringToArray($string, $delimeter)
    {
        $data = [];
        $elem = explode($delimeter, $string);
        $elems = array_filter($elem);
        foreach($elems as $elm) {
            $items = explode("=", $elm);
            $data[$items[0]] = $items[1];
        }
        return $data;
    }

    /**
    * Check the payment activate params
    *
    * return bool
    */
    public function paymentActive()
    {
        $paymentDisplay = false;
        if (is_numeric($this->getNovalnetConfig('novalnet_vendor_id')) && !empty($this->getNovalnetConfig('novalnet_auth_code')) && is_numeric($this->getNovalnetConfig('novalnet_product_id')) 
        && is_numeric($this->getNovalnetConfig('novalnet_tariff_id')) && !empty($this->getNovalnetConfig('novalnet_access_key')))
        {
            $paymentDisplay = true;
        }
        return $paymentDisplay;
    }
    
    
    public function dateFormatter($days) {
        return date( 'Y-m-d', strtotime( date( 'y-m-d' ) . '+ ' . $days . ' days' ) );
    }
    
    public function ConvertAmountToSmallerUnit($amount) {
        return sprintf('%0.2f', $amount) * 100;
    }
    
    /**
     * Update the Plenty payment
     * Return the Plenty payment object
     *
     * @param int $tid
     * @param int $tid_status
     * @param int $orderId
     * @return null
     */
    public function updatePayments($tid, $tid_status, $orderId)
    {    
        $payments = $this->paymentRepository->getPaymentsByOrderId($orderId);
        foreach ($payments as $payment) {
        $paymentProperty     = [];
        $paymentProperty[]   = $this->getPaymentProperty(PaymentProperty::TYPE_BOOKING_TEXT, $tid);
        $paymentProperty[]   = $this->getPaymentProperty(PaymentProperty::TYPE_TRANSACTION_ID, $tid);
        $paymentProperty[]   = $this->getPaymentProperty(PaymentProperty::TYPE_ORIGIN, Payment::ORIGIN_PLUGIN);
        $paymentProperty[]   = $this->getPaymentProperty(PaymentProperty::TYPE_EXTERNAL_TRANSACTION_STATUS, $tid_status);
        $payment->properties = $paymentProperty;   
    
        $this->paymentRepository->updatePayment($payment);
        }      
    }
    
    /**
      * Build cash payment transaction comments
      *
      * @param array $requestData
      * @return string
      */
    public function getCashPaymentComments($requestData)
    {
        $comments = $this->getTranslatedText('cashpayment_expire_date') . $requestData['cp_due_date'] . PHP_EOL;
        $comments .= PHP_EOL . PHP_EOL . $this->getTranslatedText('cashpayment_near_you') . PHP_EOL . PHP_EOL . PHP_EOL;

        $strnos = 0;
        foreach($requestData as $key => $val)
        {
            if(strpos($key, 'nearest_store_title') !== false)
            {
                $strnos++;
            }
        }

        for($i = 1; $i <= $strnos; $i++)
        {
            $countryName = !empty($requestData['nearest_store_country_' . $i]) ? $requestData['nearest_store_country_' . $i] : '';
            $comments .= $requestData['nearest_store_title_' . $i] . PHP_EOL;
            $comments .= $countryName . PHP_EOL;
            $comments .= $this->checkUtf8Character($requestData['nearest_store_street_' . $i]) . PHP_EOL;
            $comments .= $requestData['nearest_store_city_' . $i] . PHP_EOL;
            $comments .= $requestData['nearest_store_zipcode_' . $i] . PHP_EOL . PHP_EOL;
        }

        return $comments;
    }
}
