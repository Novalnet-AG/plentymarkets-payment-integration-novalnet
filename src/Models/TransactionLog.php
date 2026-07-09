<?php
/**
 * This file is used to create a TransactionLog model in the database
 *
 * @author       Novalnet AG
 * @copyright(C) Novalnet
 * @license      https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 */
namespace Novalnet\Models;

use Plenty\Modules\Plugin\DataBase\Contracts\Model;

/**
 * Class TransactionLog
 *
 * @property int     $id
 * @property int     $orderNo
 * @property int     $amount
 * @property int     $callbackAmount
 * @property string  $referenceTid
 * @property string  $transactionDatetime
 * @property string  $tid
 * @property string  $paymentName
 * @property array   $additionalInfo
 *
 * @package Novalnet\Models
 */
class TransactionLog extends Model
{
    public $id;
    public $orderNo;
    public $amount;
    public $callbackAmount;
    public $referenceTid;
    public $transactionDatetime;
    public $tid;
    public $paymentName;
    public $additionalInfo;

    /**
     * Returns table name to create during build
     *
     * @return string
     */
    public function getTableName(): string
    {
        return 'Novalnet::TransactionLog';
    }
}
