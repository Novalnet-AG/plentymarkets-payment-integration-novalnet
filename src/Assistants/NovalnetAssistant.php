<?php
/**
 * This file is used for creating the configuration for the plugin
 *
 * @author       Novalnet AG
 * @copyright(C) Novalnet
 * @license      https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 */

namespace Novalnet\Assistants;

use Novalnet\Assistants\SettingsHandlers\NovalnetAssistantSettingsHandler;
use Novalnet\Helper\PaymentHelper;
use Plenty\Modules\Wizard\Services\WizardProvider;
use Plenty\Modules\System\Contracts\WebstoreRepositoryContract;
use Plenty\Plugin\Application;
use Plenty\Plugin\Log\Loggable;

/**
 * Class NovalnetAssistant
 *
 * @package Novalnet\Assistants
 */
class NovalnetAssistant extends WizardProvider
{
    use Loggable;

    /**
     * @var WebstoreRepositoryContract
     */
    private $webstoreRepository;

    /**
     * @var $mainWebstore
     */
    private $mainWebstore;

    /**
     * @var $webstoreValues
     */
    private $webstoreValues;

    /**
     * @var PaymentHelper
     */
    private $paymentHelper;

    /**
    * Constructor.
    *
    * @param WebstoreRepositoryContract $webstoreRepository
    * @param PaymentHelper $paymentHelper
    */
    public function __construct(WebstoreRepositoryContract $webstoreRepository,
                                PaymentHelper $paymentHelper
                               )
    {
        $this->webstoreRepository   = $webstoreRepository;
        $this->paymentHelper        = $paymentHelper;
     }

    protected function structure()
    {
        $config =
        [
            "title" => 'NovalnetAssistant.novalnetAssistantTitle',
            "shortDescription" => 'NovalnetAssistant.novalnetAssistantShortDescription',
            "iconPath" => $this->getIcon(),
            "settingsHandlerClass" => NovalnetAssistantSettingsHandler::class,
            "translationNamespace" => 'Novalnet',
            "key" => 'payment-novalnet-assistant',
            "topics" => ['payment'],
            "priority" => 990,
            "options" =>
            [
                'clientId' =>
                [
                    'type'          => 'select',
                    'defaultValue'  => $this->getMainWebstore(),
                    'options'       => [
                                        'name'          => 'NovalnetAssistant.clientId',
                                        'required'      => true,
                                        'listBoxValues' => $this->getWebstoreListForm(),
                                       ],
                ],
            ],
            "steps" => []
        ];

        $config = $this->createGlobalConfiguration($config);
        $config = $this->createWebhookConfiguration($config);
        $config = $this->createPaymentMethodConfiguration($config);
        return $config;
    }

   /**
     * Load Novalnet Icon
     *
     * @return string
     */
    protected function getIcon()
    {
        $app = pluginApp(Application::class);
        $icon = $app->getUrlPath('Novalnet').'/images/novalnet_icon.png';
        return $icon;
    }

    /**
     * Load main web store configuration
     *
     * @return string
     */
    private function getMainWebstore()
    {
        if($this->mainWebstore === null) {
            $this->mainWebstore = $this->webstoreRepository->findById(0)->storeIdentifier;
        }
        return $this->mainWebstore;
    }

    /**
     * Get the shop list
     *
     * @return array
     */
    private function getWebstoreListForm()
    {
        if($this->webstoreValues === null) {
            $webstores = $this->webstoreRepository->loadAll();
            $this->webstoreValues = [];
            /** @var Webstore $webstore */
            foreach($webstores as $webstore) {
                $this->webstoreValues[] = [
                    "caption" => $webstore->name,
                    "value" => $webstore->storeIdentifier,
                ];
            }
        }
        return $this->webstoreValues;
    }

    /**
    * Create the global onfiguration
    *
    * @param array $config
    *
    * @return array
    */
    public function createGlobalConfiguration($config)
    {
        $config['steps']['novalnetGlobalConf'] =
        [
            "title" => 'NovalnetAssistant.novalnetGlobalConf',
            "sections" => [
                [
                    "title"         => 'NovalnetAssistant.novalnetGlobalConf',
                    "description"   => 'NovalnetAssistant.novalnetGlobalConfDesc',
                    "form"          =>
                    [
                        'novalnetPublicKey' =>
                        [
                            'type'      => 'text',
                            'options'   => [
                                            'name'      => 'NovalnetAssistant.novalnetPublicKeyLabel',
                                            'tooltip'   => 'NovalnetAssistant.novalnetPublicKeyTooltip',
                                            'required'  => true
                                           ]
                        ],
                        'novalnetAccessKey' =>
                        [
                            'type'      => 'text',
                            'options'   => [
                                            'name'      => 'NovalnetAssistant.novalnetAccessKeyLabel',
                                            'tooltip'   => 'NovalnetAssistant.novalnetAccessKeyTooltip',
                                            'required'  => true
                                           ]
                        ],
                        'novalnetTariffId' =>
                        [
                            'type'      => 'text',
                            'options'   => [
                                            'name'      => 'NovalnetAssistant.novalnetTariffIdLabel',
                                            'tooltip'   => 'NovalnetAssistant.novalnetTariffIdTooltip',
                                            'required'  => true,
                                            'pattern'   => '^[1-9]\d*$'
                                           ]
                        ],
                        'novalnetClientKey' =>
                        [
                            'type'      => 'text',
                            'options'   => [
                                            'name'      => 'NovalnetAssistant.novalnetClientKeyLabel',
                                            'tooltip'   => 'NovalnetAssistant.novalnetClientKeyTooltip',
                                            'required'  => true
                                           ]
                        ],
                        'novalnetOrderCreation' =>
                        [
                            'type'         => 'checkbox',
                            'defaultValue' => true,
                            'options'   => [
                                            'name'  => 'NovalnetAssistant.novalnetOrderCreationLabel'
                                           ]
                        ]
                    ]
                ]
            ]
        ];
        return $config;
    }

    /**
    * Create the webhook configuration
    *
    * @param array $config
    *
    * @return array
    */
    public function createWebhookConfiguration($config)
    {
        $config['steps']['novalnetWebhookConf'] =
        [
                "title"     => 'NovalnetAssistant.novalnetWebhookConf',
                "sections"  =>
                [
                    [
                        "title"         => 'NovalnetAssistant.novalnetWebhookConf',
                        "description"   => 'NovalnetAssistant.novalnetWebhookConfDesc',
                        "form" =>
                        [
                            'novalnetWebhookTestMode' =>
                            [
                                'type'      => 'checkbox',
                                'options'   => [
                                                'name'      => 'NovalnetAssistant.novalnetWebhookTestModeLabel'
                                               ]
                            ],
                            'novalnetWebhookEmailTo' =>
                            [
                                'type'      => 'text',
                                'options'   => [
                                                'name'      => 'NovalnetAssistant.novalnetWebhookEmailToLabel',
                                                'tooltip'   => 'NovalnetAssistant.novalnetWebhookEmailToTooltip'
                                               ]
                            ]
                        ]
                    ]
                ]
        ];
        return $config;
    }

    /**
    * Create the payment methods configurations
    *
    * @param array $config
    *
    * @return array
    */
    public function createPaymentMethodConfiguration($config)
    {
       foreach($this->paymentHelper->getPaymentMethodsKey() as $paymentMethodKey) {
          $paymentMethodKey = str_replace('_','',ucwords(strtolower($paymentMethodKey),'_'));
          $paymentMethodKey[0] = strtolower($paymentMethodKey[0]);

          $config['steps'][$paymentMethodKey] =
          [
                "title"     => 'Customize.' . $paymentMethodKey,
                "sections"  =>
                [
                    [
                        "title"         => 'Customize.' . $paymentMethodKey,
                        "description"   => 'Customize.' . $paymentMethodKey .'Desc',
                        "form"          =>
                        [
                            $paymentMethodKey .'PaymentActive' =>
                            [
                                'type'      => 'checkbox',
                                'options'   => [
                                                'name' => 'NovalnetAssistant.novalnetPaymentActiveLabel'
                                               ]
                            ],
                            $paymentMethodKey . 'TestMode' =>
                            [
                                'type'      => 'checkbox',
                                'options'   => [
                                                'name' => 'NovalnetAssistant.novalnetTestModeLabel'
                                               ]
                            ],
                           $paymentMethodKey . 'PaymentLogo' =>
                           [
                                'type'      => 'file',
                                'options'   => [
                                                'name'              => 'NovalnetAssistant.novalnetPaymentLogoLabel',
                                                'showPreview'       => true,
                                                'allowedExtensions' => ['svg', 'png', 'jpg', 'jpeg'],
                                                'allowFolders'      => false
                                               ]
                            ]
                        ]
                    ]
                 ]
          ];

        $config = $this->CreateOptionalPaymentDisplayConfiguration($config, $paymentMethodKey);
        }
        // Load the SEPA payment configuration
        $config = $this->createSepaPaymentConfiguration($config);
        // Load the card payment configuration
        $config = $this->createCcPaymentConfiguration($config);
        // Load the Invoice payment configuration
        $config = $this->createInvoicePaymentConfiguration($config);
        // Load the Prepayment payment configuration
        $config = $this->createPrepaymentPaymentConfiguration($config);
         // Load the Cashpayment payment configuration
        $config = $this->createCashpaymentPaymentConfiguration($config);
        // Load the Guaranteed payments configuration
        $config = $this->createGuaranteedPaymentConfiguration($config);
        // Load the Google Pay payments configuration
        $config = $this->createGooglePayPaymentConfiguration($config);
        // Load the On Hold configuration
        $config = $this->createOnHoldConfiguration($config);
        return $config;
    }

    /**
    * Create due date configuration for SEPA payments
    *
    * @param array $config
    *
    * @return array
    */
    public function createSepaPaymentConfiguration($config)
    {
        $sepaPayments = ['novalnetSepa', 'novalnetGuaranteedSepa'];
        foreach($sepaPayments as $sepaPayment) {
            $config['steps'][$sepaPayment]['sections'][]['form'] =
            [
                $sepaPayment . 'Duedate' =>
                [
                    'type' => 'text',
                    'options' => [
                                  'name'    => 'NovalnetAssistant.novalnetSepaDueDateLabel',
                                  'tooltip' => 'NovalnetAssistant.novalnetSepaDueDateTooltip',
                                  'pattern' => '^[1-9]\d*$'
                                 ]
                ]
            ];
        }
        return $config;
    }

    /**
    * Create configuration for card payment
    *
    * @param array $config
    *
    * @return array
    */
    public function createCcPaymentConfiguration($config)
    {
        $config['steps']['novalnetCc']['sections'][]['form'] =
        [
            'novalnetCcEnforce' =>
            [
                'type'      => 'checkbox',
                'options'   => [
                                'name'  => 'NovalnetAssistant.novalnetCcEnforceLabel'
                             ]
            ],
            'novalnetCcInlineForm' =>
            [
                'type'         => 'checkbox',
                'defaultValue' => true,
                'options'      => [
                                   'name' => 'NovalnetAssistant.novalnetCcDisplayInlineFormLabel'
                                  ]
            ],
            'novalnetCcStandardStyleLabel' =>
            [
                'type'       => 'text',
                'options'    => [
                                'name' => 'NovalnetAssistant.novalnetCcStandardStyleLabelLabel'
                               ]
            ],
            'novalnetCcStandardStyleField' =>
            [
                'type'       => 'text',
                'options'    => [
                                'name' => 'NovalnetAssistant.novalnetCcStandardStyleFieldLabel'
                               ]
            ],
            'novalnetCcStandardStyleCss' =>
            [
                'type'         => 'text',
                'defaultValue' => 'body{color: #555;font-family: Verdana,Arial,sans-serif;font-size:12px;line-height: 1.5;}.label-group{width:152px !important;float:unset !important;}.expiry_date .cvc{width:100%;}',
                'options'    => [
                                'name' => 'NovalnetAssistant.novalnetCcStandardStyleCssLabel'
                               ]
            ]
        ];
        return $config;
    }

    /**
    * Create due date configuration for Invoice
    *
    * @param array $config
    *
    * @return array
    */
    public function createInvoicePaymentConfiguration($config)
    {
        $config['steps']['novalnetInvoice']['sections'][]['form'] =
        [
            'novalnetInvoiceDuedate' =>
            [
                'type'      => 'text',
                'options'   => [
                                'name'      => 'NovalnetAssistant.novalnetInvoiceDuedateLabel',
                                'tooltip'   => 'NovalnetAssistant.novalnetInvoiceDuedateTooltip',
                                'pattern'   => '^[1-9]\d*$'
                               ]
            ]
        ];
        return $config;
    }

    /**
    * Create due date configuration for Prepayment
    *
    * @param array $config
    *
    * @return array
    */
    public function createPrepaymentPaymentConfiguration($config)
    {
        $config['steps']['novalnetPrepayment']['sections'][]['form'] =
        [
            'novalnetPrepaymentDuedate' =>
            [
                'type'      => 'text',
                'options'   => [
                                'name'      => 'NovalnetAssistant.novalnetPrepaymentDuedateLabel',
                                'tooltip'   => 'NovalnetAssistant.novalnetPrepaymentDuedateTooltip',
                                'pattern'   => '^[1-9]\d*$'
                               ]
            ]
        ];
        return $config;
    }

    /**
    * Create Slip expiry configuration for Cashpayment
    *
    * @param array $config
    *
    * @return array
    */
    public function createCashpaymentPaymentConfiguration($config)
    {
        $config['steps']['novalnetCashpayment']['sections'][]['form'] =
        [
            'novalnetCashpaymentDuedate' =>
            [
                'type' => 'text',
                'options' => [
                                'name'    => 'NovalnetAssistant.novalnetCashpaymentDueDateLabel',
                                'tooltip' => 'NovalnetAssistant.novalnetCashpaymentDueDateTooltip',
                                'pattern' => '^[1-9]\d*$'
                             ]
            ]
        ];
        return $config;
    }

    /**
    * Create payment additional configuration
    *
    * @param array $config
    *
    * @return array
    */
    public function CreateOptionalPaymentDisplayConfiguration($config, $paymentMethodKey)
    {
        $config['steps'][$paymentMethodKey]['sections'][]['form'] =
        [
            $paymentMethodKey . 'MinimumOrderAmount' =>
            [
                'type'      => 'text',
                'options'   => [
                                'name'      => 'NovalnetAssistant.novalnetMinimumOrderAmountLabel',
                                'tooltip'   => 'NovalnetAssistant.novalnetMinimumOrderAmountTooltip'
                               ]
            ],
            $paymentMethodKey . 'MaximumOrderAmount' =>
            [
                'type'      => 'text',
                'options'   => [
                                'name'      => 'NovalnetAssistant.novalnetMaximumOrderAmountLabel',
                                'tooltip'   => 'NovalnetAssistant.novalnetMaximumOrderAmountTooltip',
                               ]
            ],
            $paymentMethodKey . 'AllowedCountry' =>
            [
               'type'       => 'text',
               'options'    => [
                                'name'      => 'NovalnetAssistant.novalnetAllowedCountryLabel'
                               ]
            ]
        ];
        return $config;
    }

    /**
    * Create On-hold configuration
    *
    * @param array $config
    *
    * @return array
    */
    public function createOnHoldConfiguration($config)
    {
         $onHoldSupportedPayments = ['novalnetSepa', 'novalnetCc', 'novalnetInvoice', 'novalnetGuaranteedInvoice', 'novalnetGuaranteedSepa', 'novalnetPaypal', 'novalnetApplepay', 'novalnetGooglepay'];
         foreach($onHoldSupportedPayments as $onHoldSupportedPayment) {
            $config['steps'][$onHoldSupportedPayment]['sections'][]['form'] =
            [
                $onHoldSupportedPayment . 'PaymentAction' =>
                [
                    'type'          => 'select',
                    'defaultValue'  => 0,
                    'options'       => [
                                        'name'          => 'NovalnetAssistant.novalnetPaymentActionLabel',
                                        'listBoxValues' => [
                                            [
                                            'caption'   => 'NovalnetAssistant.novalnetOnHoldCaptureLabel',
                                            'value'     => 0
                                            ],
                                            [
                                            'caption'   => 'NovalnetAssistant.novalnetOnHoldAuthorizeLabel',
                                            'value'     => 1
                                            ]
                                        ]
                                       ]
                ],
                $onHoldSupportedPayment . 'OnHold' =>
                [
                    'type'      => 'text',
                    'options'   => [
                                    'name'      => 'NovalnetAssistant.novalnetOnHoldLabel',
                                    'tooltip'   => 'NovalnetAssistant.novalnetOnHoldTooltip'
                                   ]
                ]
            ];
         }
         return $config;
    }

    /**
    * Create Guaranteed payment configuration
    *
    * @param array $config
    *
    * @return array
    */
    public function createGuaranteedPaymentConfiguration($config)
    {
        $nnGuaranteedPayments = ['novalnetGuaranteedInvoice', 'novalnetGuaranteedSepa'];
        foreach($nnGuaranteedPayments as $nnGuaranteedPayment) {
            $config['steps'][$nnGuaranteedPayment]['sections'][]['form'] =
            [
                $nnGuaranteedPayment . 'force' =>
                [
                    'type'      => 'checkbox',
                    'options'   => [
                                    'name' => 'NovalnetAssistant.novalnetGuaranteedForceLabel'
                                   ]
                ],
                $nnGuaranteedPayment . 'allowB2bCustomer' =>
                [
                    'type'         => 'checkbox',
                    'defaultValue' => true,
                    'options'      => [
                                       'name' => 'NovalnetAssistant.novalnetAllowB2bCustomerLabel'
                                      ]
                ]
            ];
        }
        return $config;
    }

    /**
    * Create Google Pay payment configuration
    *
    * @param array $config
    *
    * @return array
    */
    public function createGooglePayPaymentConfiguration($config)
    {
        $config['steps']['novalnetGooglepay']['sections'][]['form'] =
        [
            'novalnetGooglepayMerchantId' =>
            [
                'type'      => 'text',
                'options'   => [
                                'name' => 'NovalnetAssistant.novalnetGooglepayMerchantIdLabel',
                                'tooltip' => 'NovalnetAssistant.novalnetGooglepayMerchantIdTooltip',
                               ]
            ],
            'novalnetGooglepayBusinessName' =>
            [
                'type'      => 'text',
                'options'   => [
                                'name'      => 'NovalnetAssistant.novalnetGooglepayBusinessNameLabel',
                                'tooltip'   => 'NovalnetAssistant.novalnetGooglepayMerchantIdTooltip',
                               ]
            ],
            'novalnetGooglepayEnforce' =>
            [
                'type'      => 'checkbox',
                'options'   => [
                                'name' => 'NovalnetAssistant.novalnetGooglepayEnforceLabel',
                               ]
            ],
            'novalnetGooglepayButtonType' =>
            [
                'type'          => 'select',
                'defaultValue'  => 'buy',
                'options'       => [
                                    'name'          => 'NovalnetAssistant.novalnetGooglepayButtonTypeLabel',
                                    'listBoxValues' => $this->listGooglePayButtonTypes()
                                   ]
            ],
            'novalnetGooglepayButtonHeight' =>
            [
                  'type'    => 'text',
                  'options' => [
                                 'name'     => 'NovalnetAssistant.novalnetGooglepayButtonHeightLabel',
                                 'tooltip'  => 'NovalnetAssistant.novalnetGooglepayButtonHeightTooltip',
                                ]
            ]
        ];
        return $config;
    }

    /**
    * List Google Pay button types
    *
    * @return array
    */
    public function listGooglePayButtonTypes()
    {
        // Button types
        $ButtonTypes = ['Buy' => 'buy', 'Book' => 'book', 'Checkout' => 'checkout', 'Donate' => 'donate', 'Order' => 'order', 'Pay' => 'pay', 'Plain' => 'plain', 'Subscribe' => 'subscribe'];
        $googlePayButtonTypes = [];
        foreach($ButtonTypes as $buttonTypeIndex => $buttonType) {
                $googlePayButtonTypes[] = [
                                            'caption' => 'NovalnetAssistant.novalnetGooglepay' . $buttonTypeIndex . 'Label',
                                            'value' => $buttonType
                                          ];
        }
        return $googlePayButtonTypes;
    }
}
