<?php
/**
 * This file is used to save all data created during
 * the assistant process
 *
 * @author       Novalnet AG
 * @copyright(C) Novalnet
 * @license      https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 */
namespace Novalnet\Assistants\SettingsHandlers;

use Novalnet\Helper\PaymentHelper;
use Novalnet\Services\SettingsService;
use Plenty\Modules\Plugin\PluginSet\Contracts\PluginSetRepositoryContract;
use Plenty\Modules\Wizard\Contracts\WizardSettingsHandler;

/**
 * Class NovalnetAssistantSettingsHandler
 *
 * @package Novalnet\Assistants\SettingsHandlers
 */
class NovalnetAssistantSettingsHandler implements WizardSettingsHandler
{
    public function handle(array $postData)
    {
        /** @var PluginSetRepositoryContract $pluginSetRepo */
        $pluginSetRepo = pluginApp(PluginSetRepositoryContract::class);
        /** @var PaymentHelper $paymentHelper */
        $paymentHelper = pluginApp(PaymentHelper::class);
        $clientId = $postData['data']['clientId'];
        $pluginSetId = $pluginSetRepo->getCurrentPluginSetId();
        $data = $postData['data'];
        // Novalnet global and webhook configuration values
        $novalnetSettings=[
            'novalnet_public_key'       =>  $data['novalnetPublicKey'] ?? '',
            'novalnet_private_key'      =>  $data['novalnetAccessKey'] ?? '',
            'novalnet_tariff_id'        =>  $data['novalnetTariffId'] ?? '',
            'novalnet_client_key'       =>  $data['novalnetClientKey'] ?? '',
            'novalnet_order_creation'   =>  $data['novalnetOrderCreation'] ?? '',
            'novalnet_webhook_testmode' =>  $data['novalnetWebhookTestMode'] ?? '',
            'novalnet_webhook_email_to' =>  $data['novalnetWebhookEmailTo'] ?? '',
        ];
        // Payment method common configuration values
        foreach($paymentHelper->getPaymentMethodsKey() as $paymentMethodKey) {
            $paymentKey=str_replace('_','',ucwords(strtolower($paymentMethodKey),'_'));
            $paymentKey[0] = strtolower($paymentKey[0]);
            $paymentMethodKey = strtolower($paymentMethodKey);
            $novalnetSettings[$paymentMethodKey]['payment_active']               = $data[$paymentKey . 'PaymentActive'] ?? '';
            $novalnetSettings[$paymentMethodKey]['test_mode']                    = $data[$paymentKey . 'TestMode'] ?? '';
            $novalnetSettings[$paymentMethodKey]['payment_logo']                 = $data[$paymentKey . 'PaymentLogo'] ?? '';
            $novalnetSettings[$paymentMethodKey]['minimum_order_amount']         = $data[$paymentKey . 'MinimumOrderAmount'] ?? '';
            $novalnetSettings[$paymentMethodKey]['maximum_order_amount']         = $data[$paymentKey . 'MaximumOrderAmount'] ?? '';
            $novalnetSettings[$paymentMethodKey]['allowed_country']              = $data[$paymentKey . 'AllowedCountry'] ?? '';
            if(in_array($paymentMethodKey, ['novalnet_cc', 'novalnet_invoice', 'novalnet_sepa', 'novalnet_guaranteed_invoice', 'novalnet_guaranteed_sepa', 'novalnet_instalment_invoice', 'novalnet_instalment_sepa', 'novalnet_paypal', 'novalnet_applepay', 'novalnet_googlepay'])) {
                $novalnetSettings[$paymentMethodKey]['payment_action']       = $data[$paymentKey . 'PaymentAction'] ?? '';
                $novalnetSettings[$paymentMethodKey]['onhold_amount']        = $data[$paymentKey . 'OnHold'] ?? '';

            }
            if(in_array($paymentMethodKey, ['novalnet_invoice', 'novalnet_sepa', 'novalnet_prepayment' , 'novalnet_cashpayment'])) {
                $novalnetSettings[$paymentMethodKey]['due_date']             = $data[$paymentKey . 'Duedate'] ?? '';
            }
            switch ($paymentMethodKey) {
                case 'novalnet_cc':
                    $novalnetSettings[$paymentMethodKey]['enforce']              = $data[$paymentKey . 'Enforce'] ?? '';
                    $novalnetSettings[$paymentMethodKey]['inline_form']          = $data[$paymentKey . 'InlineForm'] ?? '';
                    $novalnetSettings[$paymentMethodKey]['standard_style_label'] = $data[$paymentKey . 'StandardStyleLabel'] ?? '';
                    $novalnetSettings[$paymentMethodKey]['standard_style_field'] = $data[$paymentKey . 'StandardStyleField'] ?? '';
                    $novalnetSettings[$paymentMethodKey]['standard_style_css']   = $data[$paymentKey . 'StandardStyleCss'] ?? '';
                    break;
                case 'novalnet_guaranteed_invoice':
                case 'novalnet_guaranteed_sepa':
                    $novalnetSettings[$paymentMethodKey]['force']                = $data[$paymentKey . 'force'] ?? '';
                    $novalnetSettings[$paymentMethodKey]['allow_b2b_customer']   = $data[$paymentKey . 'allowB2bCustomer'] ?? '';
                    break;
                case 'novalnet_instalment_invoice':
                case 'novalnet_instalment_sepa':
                    $novalnetSettings[$paymentMethodKey]['allow_b2b_customer']   = $data[$paymentKey . 'allowB2bCustomer'] ?? '';
                    $novalnetSettings[$paymentMethodKey]['instament_cycles']     = $data[$paymentKey . 'instamentCycles'] ?? '';
                    break;
                case 'novalnet_googlepay':
                    $novalnetSettings[$paymentMethodKey]['merchant_id']          = $data[$paymentKey . 'MerchantId'] ?? '';
                    $novalnetSettings[$paymentMethodKey]['business_name']        = $data[$paymentKey . 'BusinessName'] ?? '';
                    $novalnetSettings[$paymentMethodKey]['enforce']              = $data[$paymentKey . 'Enforce'] ?? '';
                    $novalnetSettings[$paymentMethodKey]['button_type']          = $data[$paymentKey . 'ButtonType'] ?? '';
                    $novalnetSettings[$paymentMethodKey]['button_height']        = $data[$paymentKey . 'ButtonHeight'] ?? '';
                    break;
            }
        }
        /** @var SettingsService $settingsService */
        $settingsService=pluginApp(SettingsService::class);
        $settingsService->updateSettings($novalnetSettings, $clientId, $pluginSetId);
        return true;
    }
}
