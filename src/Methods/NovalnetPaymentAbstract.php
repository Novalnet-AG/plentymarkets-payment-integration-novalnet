<?php
/**
 * This file acts as helper for visibility of the payment methods
 *
 * @author       Novalnet AG
 * @copyright(C) Novalnet
 * @license      https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 */
namespace Novalnet\Methods;

use Novalnet\Services\PaymentService;
use Novalnet\Helper\PaymentHelper;
use Novalnet\Services\SettingsService;
use Plenty\Modules\Payment\Method\Services\PaymentMethodBaseService;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Basket\Models\Basket;
use Plenty\Modules\Payment\Models\Payment;
use Plenty\Plugin\Application;
use Plenty\Plugin\Translation\Translator;

/**
 * Class NovalnetPaymentAbstract
 *
 * @package Novalnet\Methods
 */
abstract class NovalnetPaymentAbstract extends PaymentMethodBaseService
{
    const PAYMENT_KEY = 'Novalnet';

    /**
     * @var BasketRepositoryContract
     */
    private $basketRepository;

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
     * Constructor.
     *
     * @param BasketRepositoryContract $basketRepository
     * @param PaymentService $paymentService
     * @param PaymentHelper $paymentHelper
     * @param SettingsService $settingsService
     */
    public function __construct(BasketRepositoryContract $basketRepository,
                                PaymentService $paymentService,
                                PaymentHelper $paymentHelper,
                                SettingsService $settingsService
                               )
    {
        $this->basketRepository = $basketRepository->load();
        $this->paymentService   = $paymentService;
        $this->paymentHelper    = $paymentHelper;
        $this->settingsService  = $settingsService;
    }

    /**
     * Check the configuration if the payment method is active
     * Return true only if the payment method is active
     *
     * @return bool
     */
    public function isActive(): bool
    {
        $isPaymentActive = $this->settingsService->getPaymentSettingsValue('payment_active', strtolower($this::PAYMENT_KEY));

        // Hide and Display the Guaranteed and normal Payment based on the guaranteed conditions satisfied
        if($isPaymentActive && $this::PAYMENT_KEY == 'NOVALNET_INVOICE') {
            $guaranteeStatus = $this->paymentService->isGuaranteePaymentToBeDisplayed($this->basketRepository, 'novalnet_guaranteed_invoice');
            $isPaymentActive = ($guaranteeStatus == 'normal') ? true : false;
        }

        if($isPaymentActive && $this::PAYMENT_KEY == 'NOVALNET_SEPA') {
            $guaranteeStatus = $this->paymentService->isGuaranteePaymentToBeDisplayed($this->basketRepository, 'novalnet_guaranteed_sepa');
            $isPaymentActive = ($guaranteeStatus == 'normal') ? true : false;
        }

        if($isPaymentActive && in_array($this::PAYMENT_KEY, ['NOVALNET_GUARANTEED_INVOICE', 'NOVALNET_GUARANTEED_SEPA'])) {
            $guaranteeStatus = $this->paymentService->isGuaranteePaymentToBeDisplayed($this->basketRepository, strtolower($this::PAYMENT_KEY));
            $isPaymentActive = ($guaranteeStatus == 'guarantee') ? true : false;
        }

        if($isPaymentActive) {
            // Check if the payment allowed for mentioned countries
            $activatePaymentAllowedCountry = true;
            if($allowedCountry = $this->settingsService->getPaymentSettingsValue('allowed_country', strtolower($this::PAYMENT_KEY))) {
                $activatePaymentAllowedCountry  = $this->paymentService->allowedCountries($this->basketRepository, $allowedCountry);
            }

            // Check if the Minimum order amount value met to payment display condition
            $activatePaymentMinimumAmount = true;
            $minimumAmount = trim($this->settingsService->getPaymentSettingsValue('minimum_order_amount', strtolower($this::PAYMENT_KEY)));
            if(!empty($minimumAmount) && is_numeric($minimumAmount)) {
                $activatePaymentMinimumAmount = $this->paymentService->getMinBasketAmount($this->basketRepository, $minimumAmount);
            }

            // Check if the Maximum order amount value met to payment display condition
            $activatePaymentMaximumAmount = true;
            $maximumAmount = trim($this->settingsService->getPaymentSettingsValue('maximum_order_amount', strtolower($this::PAYMENT_KEY)));
            if(!empty($maximumAmount) && is_numeric($maximumAmount)) {
                $activatePaymentMaximumAmount = $this->paymentService->getMaxBasketAmount($this->basketRepository, $maximumAmount);
            }

            return (bool) ($this->paymentService->isMerchantConfigurationValid() && $activatePaymentAllowedCountry && $activatePaymentMinimumAmount && $activatePaymentMaximumAmount);
        }
            return false;
    }

    /**
     * Get the name of the payment method. The name can be entered in the multilingualism.
     *
     * @param string $lang
     *
     * @return string
     */
    public function getName(string $lang = 'de'): string
    {
        $paymentMethodKey = str_replace('_','',ucwords(strtolower($this::PAYMENT_KEY),'_'));
        $paymentMethodKey[0] = strtolower($paymentMethodKey[0]);

        /** @var Translator $translator */
        $translator = pluginApp(Translator::class);
        return $translator->trans('Novalnet::Customize.'. $paymentMethodKey, [], $lang);
    }

    /**
     * Return an additional payment fee for the payment method.
     *
     * @return float
     */
    public function getFee(): float
    {
        return 0.00;
    }

    /**
     * Retrieves the icon of the payment. The URL can be entered in the configuration.
     *
     * @param string $lang
     *
     * @return string
     */
    public function getIcon(string $lang = 'de'): string
    {
        $logoUrl = $this->settingsService->getPaymentSettingsValue('payment_logo', strtolower($this::PAYMENT_KEY));
        if(empty($logoUrl)){
            /** @var Application $app */
            $app = pluginApp(Application::class);
            $logoUrl = $app->getUrlPath('novalnet') .'/images/'. strtolower($this::PAYMENT_KEY) .'.png';
        }
        return $logoUrl;
    }

    /**
     * Retrieves the description of the payment. The description can be entered in the configuration.
     *
     * @param string $lang
     *
     * @return string
     */
    public function getDescription(string $lang = 'de'): string
    {
        $paymentMethodKey = str_replace('_','',ucwords(strtolower($this::PAYMENT_KEY),'_'));
        $paymentMethodKey[0] = strtolower($paymentMethodKey[0]);
        /** @var Translator $translator */
        $translator = pluginApp(Translator::class);
        return $translator->trans('Novalnet::Customize.'. $paymentMethodKey .'Desc', []);
    }

    /**
     * Check if it is allowed to switch to this payment method
     *
     * @param int $orderId
     *
     * @return bool
     */
    public function isSwitchableTo($orderId = null): bool
    {
        if($orderId > 0) {
            return true;
        }
        return false;
    }

    /**
     * Check if it is allowed to switch from this payment method
     *
     * @param int $orderId
     *
     * @return bool
     */
    public function isSwitchableFrom($orderId = null): bool
    {
        if($orderId > 0) {
            $transactionDetails = $this->paymentService->getDetailsFromPaymentProperty($orderId);
            if( strpos($this::PAYMENT_KEY, 'NOVALNET') !== false &&  ( (!empty($transactionDetails['tx_status']) && !in_array($transactionDetails['tx_status'], ['PENDING', 'ON_HOLD', 'CONFIRMED', 'DEACTIVATED'])) || empty($transactionDetails['tx_status']) )) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the name for the back end.
     *
     * @param string $lang
     *
     * @return string
     */
    public function getBackendName(string $lang = 'de'): string
    {
        return 'Novalnet ' . $this->getName($lang);
    }

    /**
     * Return the icon for the back end, shown in the payments UI.
     *
     * @return string
     */
    public function getBackendIcon(): string
    {
        $app = pluginApp(Application::class);
        $icon = $app->getUrlPath('novalnet') . '/images/logos/' . strtolower($this::PAYMENT_KEY) .'_backend_icon.svg';
        return $icon;
    }
}
