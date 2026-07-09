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
use Plenty\Modules\Order\Shipping\Countries\Contracts\CountryRepositoryContract;
use Plenty\Modules\System\Contracts\SystemInformationRepositoryContract;
use Plenty\Plugin\Log\Loggable;

/**
 * Class NovalnetAssistant
 *
 * @package Novalnet\Assistants
 */
class NovalnetAssistant extends WizardProvider
{
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
        // Load the Allow B2B configuration
        $config = $this->createAllowB2BConfiguration($config);
        // Load the Allow Force configuration
        $config = $this->createForceConfiguration($config);
        // Load the Instalment payments configuration
        $config = $this->createInstalmentPaymentConfiguration($config);
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
        $config['steps']['novalnetSepa']['sections'][]['form'] =
        [
            'novalnetSepaDuedate' =>
            [
                'type'      => 'text',
                'options'   =>  [
                                    'name'    => 'NovalnetAssistant.novalnetSepaDueDateLabel',
                                    'tooltip' => 'NovalnetAssistant.novalnetSepaDueDateTooltip',
                                    'pattern' => '^(?:[3-9]|1[0-4])$',
                                ]
            ]
        ];
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
                'options'   =>  [
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
                                'pattern'   => '^(?:[7-9]|[1-9]\d|[1-2]\d{2}|3[0-5]\d|36[0-5])$'
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
                                'pattern'   => '^(?:[7-9]|1\d|2[0-8])$'
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
        $deliveryCountries = $this->getSpecificDeliveryCountries($paymentMethodKey);
        $config['steps'][$paymentMethodKey]['sections'][]['form'] =
        [
            $paymentMethodKey . 'MinimumOrderAmount' =>
            [
                'type'          => 'double',
                'defaultValue'  => 0,
                'options'   => [
                                'isPriceInput' => true,
                                'decimalCount' => 2,
                                'name'      => 'NovalnetAssistant.novalnetMinimumOrderAmountLabel',
                                'tooltip'   => 'NovalnetAssistant.novalnetMinimumOrderAmountTooltip'
                               ]
            ],
            $paymentMethodKey . 'MaximumOrderAmount' =>
            [
                'type'          => 'double',
                'defaultValue'  => 0,
                'options'   => [
                                'isPriceInput' => true,
                                'decimalCount' => 2,
                                'name'      => 'NovalnetAssistant.novalnetMaximumOrderAmountLabel',
                                'tooltip'   => 'NovalnetAssistant.novalnetMaximumOrderAmountTooltip',
                               ]
            ],
            $paymentMethodKey . 'AllowedCountry' =>
            [
               'type'           => 'checkboxGroup',
               'defaultValue'   => $this->getDefaultCountries($deliveryCountries),
               'options'    => [
                                'name'      => 'NovalnetAssistant.novalnetAllowedCountryLabel',
                                'required' => false,
                                'checkboxValues' => array_values($deliveryCountries)
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

    $paymentActionSupportedPayments = ['novalnetSepa', 'novalnetCc', 'novalnetInvoice', 'novalnetGuaranteedInvoice', 'novalnetGuaranteedSepa','novalnetPaypal', 'novalnetApplepay', 'novalnetGooglepay', 'novalnetInstalmentInvoice', 'novalnetInstalmentSepa' ];

    $onHoldSupportedPayments = ['novalnetSepa', 'novalnetCc', 'novalnetInvoice', 'novalnetGuaranteedInvoice', 'novalnetGuaranteedSepa','novalnetPaypal', 'novalnetApplepay', 'novalnetGooglepay','novalnetInstalmentInvoice', 'novalnetInstalmentSepa'];


    foreach ($paymentActionSupportedPayments as $payment) {
        // Base options
        $listBoxValues = [
            [
                'caption' => 'NovalnetAssistant.novalnetOnHoldCaptureLabel',
                'value'   => 0
            ]
        ];
        // Add authorize option
        if (in_array($payment, $onHoldSupportedPayments)) {
            $listBoxValues[] = [
                'caption' => 'NovalnetAssistant.novalnetOnHoldAuthorizeLabel',
                'value'   => 1
            ];
        }
        // Build form dynamically
        $form = [
            $payment . 'PaymentAction' => [
                'type'         => 'select',
                'defaultValue' => 0,
                'options'      => [
                    'name'          => 'NovalnetAssistant.novalnetPaymentActionLabel',
                    'listBoxValues' => $listBoxValues
                ]
            ]
        ];
        // Add OnHold field only if supported
        if (in_array($payment, $onHoldSupportedPayments)) {
            $form[$payment . 'OnHold'] = [
                'type'    => 'text',
                'options' => [
                    'name'    => 'NovalnetAssistant.novalnetOnHoldLabel',
                    'tooltip' => 'NovalnetAssistant.novalnetOnHoldTooltip'
                ]
            ];
        }
        // Assign to config
        $config['steps'][$payment]['sections'][]['form'] = $form;
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
    public function createAllowB2BConfiguration($config)
    {
        $nnPayments = ['novalnetGuaranteedInvoice', 'novalnetGuaranteedSepa', 'novalnetInstalmentInvoice', 'novalnetInstalmentSepa'];
        foreach($nnPayments as $nnPayment) {
            $config['steps'][$nnPayment]['sections'][]['form'] =
            [
                $nnPayment . 'allowB2bCustomer' =>
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
    * Create Guaranteed payment configuration
    *
    * @param array $config
    *
    * @return array
    */
    public function createForceConfiguration($config)
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
                ]
            ];
        }
        return $config;
    }

    /**
    * Create Instalment payment configuration
    *
    * @param array $config
    *
    * @return array
    */
    public function createInstalmentPaymentConfiguration($config)
    {
        $nnInstalmentPayments = ['novalnetInstalmentInvoice', 'novalnetInstalmentSepa'];
        foreach($nnInstalmentPayments as $nnInstalmentPayment) {
            $config['steps'][$nnInstalmentPayment]['sections'][]['form'] =
            [
                $nnInstalmentPayment .  'instamentCycles' =>
                [
                       'type' => 'checkboxGroup',
                     'defaultValue'  => [2],
                       'options' => [
                            'required'  => true,
                           'name' => 'NovalnetAssistant.novalnetinstamentCyclesLabel',
                           'checkboxValues' =>
                           [
                                [
                                   'caption' => '2 Cycles',
                                   'value' => 2
                                ],
                                [
                                   'caption' => '3 Cycles',
                                   'value' => 3
                                ],
                                [
                                   'caption' => '4 Cycles',
                                   'value' => 4
                                ],
                                [
                                   'caption' => '5 Cycles',
                                   'value' => 5
                                ],
                                [
                                   'caption' => '6 Cycles',
                                   'value' => 6
                                ],
                                [
                                   'caption' => '7 Cycles',
                                   'value' => 7
                                ],
                                [
                                   'caption' => '8 Cycles',
                                   'value' => 8
                                ],
                                [
                                   'caption' => '9 Cycles',
                                   'value' => 9
                                ],
                                [
                                   'caption' => '10 Cycles',
                                   'value' => 10
                                ],
                                [
                                   'caption' => '11 Cycles',
                                   'value' => 11
                                ],
                                [
                                   'caption' => '12 Cycles',
                                   'value' => 12
                                ],
                                [
                                   'caption' => '15 Cycles',
                                   'value' => 15
                                ],
                                [
                                   'caption' => '18 Cycles',
                                   'value' => 18
                                ],
                                [
                                   'caption' => '21 Cycles',
                                   'value' => 21
                                ],
                                [
                                   'caption' => '24 Cycles',
                                   'value' => 24
                                ],
                                [
                                   'caption' => '36 Cycles',
                                   'value' => 36
                                ],
                           ],
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
                  'type'    => 'number',
                  'options' => [
                                 'name'     => 'NovalnetAssistant.novalnetGooglepayButtonHeightLabel',
                                 'tooltip'  => 'NovalnetAssistant.novalnetGooglepayButtonHeightTooltip',
                                 'pattern'  => '^(3[0-9]|4[0-9]|5[0-9]|6[0-4])$',
                                 'min'      => 30,
                                 'max'      => 64,
                                 'step'     => 1,
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

      /**
     * @return array
     */
    protected function getSpecificDeliveryCountries($paymentMethodKey): array
    {
        $deliveryCountries = [];
        switch ($paymentMethodKey) {
            case 'novalnetSepa':
            case 'novalnetInvoice':
            case 'novlanetPrepayment':
                $allowedCountries = [
                    1,  // DE 
                    2,  // AT 
                    3,  // BE 
                    5,  // CY 
                    6,  // CZ 
                    7,  // DK 
                    8,  // ES 
                    9,  // EE 
                    10, // FR 
                    11, // FI 
                    13, // GR 
                    14, // HU 
                    15, // IT 
                    16, // IE 
                    17, // LU
                    18, // LV
                    19, // MT
                    21, // NL
                    22, // PT
                    26, // SK
                    27, // SI
                    33, // LT
                    35, // MC
                    71, // AD
                    131, // GI
                    212, // SM
                ];
                break;
            case 'novalnetGuaranteedInvoice':
            case 'novalnetGuaranteedSepa':
            case 'novalnetInstalmentInvoice':
            case 'novalnetInstalmentSepa':
                $allowedCountries = [
                    1,  // DE 
                    2,  // AT 
                    3,  // BE 
                    4,  // CH
                    5,  // CY 
                    6,  // CZ 
                    7,  // DK 
                    8,  // ES 
                    9,  // EE 
                    10, // FR 
                    11, // FI 
                    13, // GR 
                    14, // HU 
                    15, // IT 
                    16, // IE 
                    17, // LU
                    18, // LV
                    19, // MT
                    21, // NL
                    22, // PT
                    26, // SK
                    27, // SI
                    33, // LT
                    35, // MC
                    71, // AD
                    131, // GI
                    212, // SM
                ];
                break;
            case 'novalnetIdeal':
                $allowedCountries = [
                    21, // NL
                ];
                break;
            case 'novalnetPrzelewy24':
                $allowedCountries = [
                    20, // PL
                ];
                break;
            case 'novalnetEps':
                $allowedCountries = [
                    2, // AT
                ];
                break; 
            case 'novalnetPostfinanceCard':
            case 'novalnetPostfinanceEfinance':
                $allowedCountries = [
                    4, // CH
                ];
                break;    
            case 'novalnetBancontact':
                $allowedCountries = [
                    3, // BE
                ];
                break;  
            case 'novalnetMultibanco':
                $allowedCountries = [
                    22, // PT
                ];
                break; 
            case 'novalnetOnlineBankTransfer':
            case 'novalnetTrustly':
                $allowedCountries = [
                    1,  // DE
                    2,  // AT
                    7,  // DK
                    9,  // EE
                    8,  // ES
                    11, // FI
                    12, // UK
                    33, // LT
                    18, // LV
                    21, // NL
                    20, // NO
                    24, // SE
                ];
                break;    
            case 'novalnetAlipay':
                $allowedCountries = [
                    32, // CN
                ];
                break; 
            case 'novalnetWechatPay':
                $allowedCountries = [
                    32, // CN
                    10, // FR
                    2,  // AT
                ];
                break;        
            case 'novalnetBlik':
                $allowedCountries = [
                    20, // PL
                ];
                break;    
            case 'novalnetMbway':
                $allowedCountries = [
                    22, // PT
                ];
                break;
            case 'novalnetAch':
                $allowedCountries = [
                    28, // US
                ];
                break;
            case 'novalnetTwint':
                $allowedCountries = [
                    4, // CH
                ];
                break;       
            case 'novalnetCc':
            case 'novalnetApplepay':
            case 'novalnetGooglepay':
            default:
                $allowedCountries = [];
                break;
        }
        /** @var CountryRepositoryContract $countryRepository */
        $countryRepository = pluginApp(CountryRepositoryContract::class);
        $systemLanguage = $this->getLanguage();
        $countries = $countryRepository->getCountriesList(null, ['names']);
        /** @var Country $country */
        foreach ($countries as $country) {
            if (count($allowedCountries) <= 0 || array_search($country->id, $allowedCountries) !== false) {
                $name = $country->names->where('lang', $systemLanguage)->first()->name;
                $deliveryCountries[$country->id] = [
                    'caption' => $name ?? $country->name,
                    'value' => $country->id
                ];
            }
        }

        return $deliveryCountries;
    }

    /**
     * Load the active country values
     */
    protected function getDefaultCountries($availableCountries = array())
    {

        /** @var CountryRepositoryContract $countryRepository */
        $countryRepository = pluginApp(CountryRepositoryContract::class);
        $activeCountries = $countryRepository->getActiveCountriesList();
        /** @var Country $country */
        foreach ($activeCountries as $country) {
            $this->activeCountries[$country->id] = $country->isoCode2;
        }
        
        return array_column(array_intersect_key($availableCountries, $this->activeCountries), 'value');
    }

    /**
     * @return string
     */
    protected function getLanguage()
    {
        if ($this->language === null) {
            $this->language =  \Locale::getDefault();
        }

        return $this->language;
    }
}
