<?php
/**
 * This file is used to create a settings model in the database
 *
 * @author       Novalnet AG
 * @copyright(C) Novalnet
 * @license      https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 */
namespace Novalnet\Models;

use Carbon\Carbon;
use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;
use Plenty\Modules\Plugin\DataBase\Contracts\Model;
use Novalnet\Services\PaymentService;
use Plenty\Plugin\Log\Loggable;

/**
 * Class Settings
 *
 * @property int $id
 * @property int $clientId
 * @property int $pluginSetId
 * @property array $value
 * @property string $createdAt
 * @property string $updatedAt
 *
 * @package Novalnet\Models
 */
class Settings extends Model
{
    use Loggable;

    public $id;
    public $clientId;
    public $pluginSetId;
    public $value = [];
    public $createdAt = '';
    public $updatedAt = '';

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return 'Novalnet::settings';
    }

    /**
     * Insert the configuration values into settings table
     *
     * @param array $data
     *
     * @return Model
     */
    public function create($data)
    {
        $this->clientId    = $data['clientId'];
        $this->pluginSetId = $data['pluginSetId'];
        $this->createdAt   = (string)Carbon::now();
        $this->value = [
            'novalnet_public_key'           => $data['novalnet_public_key'],
            'novalnet_private_key'          => $data['novalnet_access_key'],
            'novalnet_tariff_id'            => $data['novalnet_tariff_id'],
            'novalnet_client_key'           => $data['novalnet_client_key'],
            'novalnet_order_creation'       => $data['novalnet_order_creation'],
            'novalnet_webhook_testmode'     => $data['novalnet_webhook_testmode'],
            'novalnet_webhook_email_to'     => $data['novalnet_webhook_email_to'],
            'novalnet_sepa'                 => $data['novalnet_sepa'],
            'novalnet_cc'                   => $data['novalnet_cc'],
            'novalnet_applepay'             => $data['novalnet_applepay'],
            'novalnet_invoice'              => $data['novalnet_invoice'],
            'novalnet_prepayment'           => $data['novalnet_prepayment'],
            'novalnet_guaranteed_invoice'   => $data['novalnet_guaranteed_invoice'],
            'novalnet_guaranteed_sepa'      => $data['novalnet_guaranteed_sepa'],
            'novalnet_ideal'                => $data['novalnet_ideal'],
            'novalnet_sofort'               => $data['novalnet_sofort'],
            'novalnet_giropay'              => $data['novalnet_giropay'],
            'novalnet_cashpayment'          => $data['novalnet_cashpayment'],
            'novalnet_przelewy24'           => $data['novalnet_przelewy24'],
            'novalnet_eps'                  => $data['novalnet_eps'],
            'novalnet_paypal'               => $data['novalnet_paypal'],
            'novalnet_postfinance_card'     => $data['novalnet_postfinance_card'],
            'novalnet_postfinance_efinance' => $data['novalnet_postfinance_efinance'],
            'novalnet_bancontact'           => $data['novalnet_bancontact'],
            'novalnet_multibanco'           => $data['novalnet_multibanco'],
            'novalnet_online_bank_transfer' => $data['novalnet_online_bank_transfer'],
            'novalnet_alipay'               => $data['novalnet_alipay'],
            'novalnet_wechat_pay'           => $data['novalnet_wechat_pay'],
            'novalnet_trustly'              => $data['novalnet_trustly'],
            'novalnet_googlepay'            => $data['novalnet_googlepay']
        ];
        return $this->save();
    }

    /**
     * Update the configuration values into settings table
     *
     * @param array $data
     *
     * @return Model
     */
    public function update($data)
    {
        if(isset($data['novalnet_public_key'])) {
            $this->value['novalnet_public_key'] = $data['novalnet_public_key'];
        }
        if(isset($data['novalnet_private_key'])) {
            $this->value['novalnet_private_key'] = $data['novalnet_private_key'];
        }
        if(isset($data['novalnet_tariff_id'])) {
            $this->value['novalnet_tariff_id']  = $data['novalnet_tariff_id'];
        }
        if(isset($data['novalnet_client_key'])) {
            $this->value['novalnet_client_key'] = $data['novalnet_client_key'];
        }
        if(isset($data['novalnet_order_creation'])) {
            $this->value['novalnet_order_creation'] = $data['novalnet_order_creation'];
        }
        if(isset($data['novalnet_webhook_testmode'])) {
            $this->value['novalnet_webhook_testmode'] = $data['novalnet_webhook_testmode'];
        }
        if(isset($data['novalnet_webhook_email_to'])) {
            $this->value['novalnet_webhook_email_to'] = $data['novalnet_webhook_email_to'];
        }
        if(isset($data['novalnet_sepa'])) {
            $this->value['novalnet_sepa'] = $data['novalnet_sepa'];
        }
        if(isset($data['novalnet_cc'])) {
            $this->value['novalnet_cc'] = $data['novalnet_cc'];
        }
        if(isset($data['novalnet_applepay'])) {
            $this->value['novalnet_applepay'] = $data['novalnet_applepay'];
        }
        if(isset($data['novalnet_invoice'])) {
            $this->value['novalnet_invoice'] = $data['novalnet_invoice'];
        }
        if(isset($data['novalnet_prepayment'])) {
            $this->value['novalnet_prepayment'] = $data['novalnet_prepayment'];
        }
        if(isset($data['novalnet_guaranteed_invoice'])) {
            $this->value['novalnet_guaranteed_invoice'] = $data['novalnet_guaranteed_invoice'];
        }
        if(isset($data['novalnet_guaranteed_sepa'])) {
            $this->value['novalnet_guaranteed_sepa'] = $data['novalnet_guaranteed_sepa'];
        }
        if(isset($data['novalnet_ideal'])) {
            $this->value['novalnet_ideal'] = $data['novalnet_ideal'];
        }
        if(isset($data['novalnet_sofort'])) {
            $this->value['novalnet_sofort'] = $data['novalnet_sofort'];
        }
        if(isset($data['novalnet_giropay'])) {
            $this->value['novalnet_giropay'] = $data['novalnet_giropay'];
        }
        if(isset($data['novalnet_cashpayment'])) {
            $this->value['novalnet_cashpayment'] = $data['novalnet_cashpayment'];
        }
        if(isset($data['novalnet_przelewy24'])) {
            $this->value['novalnet_przelewy24'] = $data['novalnet_przelewy24'];
        }
        if(isset($data['novalnet_eps'])) {
            $this->value['novalnet_eps'] = $data['novalnet_eps'];
        }
        if(isset($data['novalnet_paypal'])) {
            $this->value['novalnet_paypal'] = $data['novalnet_paypal'];
        }
        if(isset($data['novalnet_postfinance_card'])) {
            $this->value['novalnet_postfinance_card'] = $data['novalnet_postfinance_card'];
        }
        if(isset($data['novalnet_postfinance_efinance'])) {
            $this->value['novalnet_postfinance_efinance'] = $data['novalnet_postfinance_efinance'];
        }
        if(isset($data['novalnet_bancontact'])) {
            $this->value['novalnet_bancontact'] = $data['novalnet_bancontact'];
        }
        if(isset($data['novalnet_multibanco'])) {
            $this->value['novalnet_multibanco'] = $data['novalnet_multibanco'];
        }
        if(isset($data['novalnet_online_bank_transfer'])) {
            $this->value['novalnet_online_bank_transfer'] = $data['novalnet_online_bank_transfer'];
        }
        if(isset($data['novalnet_alipay'])) {
            $this->value['novalnet_alipay'] = $data['novalnet_alipay'];
        }
        if(isset($data['novalnet_wechat_pay'])) {
            $this->value['novalnet_wechat_pay'] = $data['novalnet_wechat_pay'];
        }
        if(isset($data['novalnet_trustly'])) {
            $this->value['novalnet_trustly'] = $data['novalnet_trustly'];
        }
        if(isset($data['novalnet_googlepay'])) {
            $this->value['novalnet_googlepay'] = $data['novalnet_googlepay'];
        }
        return $this->save();
    }

    /**
     * Save the configuration values into settings table
     *
     * @return Model
     */
    public function save()
    {
        /** @var DataBase $database */
        $database = pluginApp(DataBase::class);
        $this->updatedAt = (string)Carbon::now();
        $paymentService = pluginApp(PaymentService::class);
        // Update the Novalnet API version 
        $paymentService->updateApiVersion($this->value);
        // Log the configuration updated time for the reference
        $this->getLogger(__METHOD__)->error('Updated Novalnet settings details ' . $this->updatedAt, $this);
        return $database->save($this);
    }

    /**
     * Delete the configuration values into settings table
     *
     * @return bool
     */
    public function delete()
    {
        /** @var DataBase $database */
        $database = pluginApp(DataBase::class);
        return $database->delete($this);
    }
}
