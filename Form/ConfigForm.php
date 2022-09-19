<?php
/*************************************************************************************/
/*      Copyright (c) Franck Allimant, CQFDev                                        */
/*      email : thelia@cqfdev.fr                                                     */
/*      web : http://www.cqfdev.fr                                                   */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE      */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace AtosV2\Form;

use AtosV2\AtosV2;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use Thelia\Model\Module;
use Thelia\Model\ModuleQuery;

/**
 * Class ConfigForm
 * @package AtosV2\Form
 * @author  Franck Allimant <franck@cqfdev.fr>
 */
class ConfigForm extends BaseForm
{
    protected function buildForm()
    {
        // If the Multi plugin is not enabled, all multi_fields are hidden
        /** @var Module $multiModule */
        $multiEnabled = (null !== $multiModule = ModuleQuery::create()->findOneByCode('AtosV2Nx')) && $multiModule->getActivate() != 0;

        $translator = Translator::getInstance();

        $this->formBuilder
            ->add(
                'merchantId',
                'text',
                [
                    'constraints' => [
                        new NotBlank(),
                    ],
                    'label' => $translator->trans('Shop Merchant ID', [], AtosV2::MODULE_DOMAIN),
                    'label_attr' => [
                        'for' => 'merchant_id',
                    ]
                ]
            )
            ->add(
                'server_url_bank_prod',
                'text',
                [
                    'constraints' => [
                        new NotBlank(),
                    ],
                    'label' => $translator->trans('Payment server production URL', [], AtosV2::MODULE_DOMAIN),
                    'label_attr' => [
                        'for' => 'server_url_bank_prod',
                        'help' => $translator->trans('The production url is to be requested from your bank', [], AtosV2::MODULE_DOMAIN)
                    ],
                    "attr" => [
                        'placeholder' => 'https://',
                    ]
                ]
            )
            ->add(
                'server_url_bank_test',
                'text',
                [
                    'constraints' => [
                        new NotBlank(),
                    ],
                    'label' => $translator->trans('Payment server test URL', [], AtosV2::MODULE_DOMAIN),
                    'label_attr' => [
                        'for' => 'server_url_bank_test',
                        'help' => $translator->trans('The test url above is available on the SIPS doc <a href="%URL">right here </a>', [ '%URL' => "https://documentation.sips.worldline.com/fr/WLSIPS.317-UG-Sips-Paypage-POST.html#Etape-3-Tester-sur-l-environnement-de-simulation_"], AtosV2::MODULE_DOMAIN)
                    ]
                ]
            )
            ->add(
                'mode',
                'choice',
                [
                    'constraints' =>  [
                        new NotBlank()
                    ],
                    'choices' => [
                        'TEST' => $translator->trans('Test', [], AtosV2::MODULE_DOMAIN),
                        'PRODUCTION' => $translator->trans('Production', [], AtosV2::MODULE_DOMAIN),
                    ],
                    'label' => $translator->trans('Operation Mode', [], AtosV2::MODULE_DOMAIN),
                    'label_attr' => [
                        'for' => 'mode',
                        'help' => $translator->trans('Test or production mode', [], AtosV2::MODULE_DOMAIN)
                    ]
                ]
            )
            ->add(
                'allowed_ip_list',
                'textarea',
                [
                    'required' => false,
                    'label' => $translator->trans('Allowed IPs in test mode', [], AtosV2::MODULE_DOMAIN),
                    'label_attr' => [
                        'for' => 'platform_url',
                        'help' => $translator->trans(
                            'List of IP addresses allowed to use this payment on the front-office when in test mode (your current IP is %ip). One address per line',
                            [ '%ip' => $this->getRequest()->getClientIp() ],
                            AtosV2::MODULE_DOMAIN
                        )
                    ],
                    'attr' => [
                        'rows' => 3
                    ]
                ]
            )
            ->add(
                'minimum_amount',
                'text',
                [
                    'constraints' => [
                        new NotBlank(),
                        new GreaterThanOrEqual(['value' => 0 ])
                    ],
                    'label' => $translator->trans('Minimum order total', [], AtosV2::MODULE_DOMAIN),
                    'label_attr' => [
                        'for' => 'minimum_amount',
                        'help' => $translator->trans(
                            'Minimum order total in the default currency for which this payment method is available. Enter 0 for no minimum',
                            [],
                            AtosV2::MODULE_DOMAIN
                        )
                    ]
                ]
            )
            ->add(
                'maximum_amount',
                'text',
                [
                    'constraints' => [
                        new NotBlank(),
                        new GreaterThanOrEqual([ 'value' => 0 ])
                    ],
                    'label' => $translator->trans('Maximum order total', [], AtosV2::MODULE_DOMAIN),
                    'label_attr' => [
                        'for' => 'maximum_amount',
                        'help' => $translator->trans(
                            'Maximum order total in the default currency for which this payment method is available. Enter 0 for no maximum',
                            [],
                            AtosV2::MODULE_DOMAIN
                        )
                    ]
                ]
            )
            ->add(
                'secretKey',
                'text',
                [
                    'label' => $translator->trans('AtosV2 secret key', [], AtosV2::MODULE_DOMAIN),
                    'label_attr' => [
                        'for' => 'platform_url',
                        'help' => $translator->trans(
                            'Please paste here the secret key you get from AtosV2 Download',
                            [],
                            AtosV2::MODULE_DOMAIN
                        ),
                    ],
                    'attr' => [
                        'rows' => 10
                    ]
                ]
            )
            ->add(
                'secretKeyVersion',
                'text',
                [
                    'label' => $translator->trans('AtosV2 secret key version number', [], AtosV2::MODULE_DOMAIN),
                    'label_attr' => [
                        'for' => 'platform_url',
                        'help' => $translator->trans(
                            'The secret key version you get from AtosV2 Download, 1 for the first secret key you get',
                            [],
                            AtosV2::MODULE_DOMAIN
                        ),
                    ],
                    'attr' => [
                        'rows' => 10
                    ]
                ]
            )
            ->add(
                'send_confirmation_message_only_if_paid',
                'checkbox',
                [
                    'value' => 1,
                    'required' => false,
                    'label' => $this->translator->trans('Send order confirmation on payment success', [], AtosV2::MODULE_DOMAIN),
                    'label_attr' => [
                        'help' => $this->translator->trans(
                            'If checked, the order confirmation message is sent to the customer only when the payment is successful. The order notification is always sent to the shop administrator',
                            [],
                            AtosV2::MODULE_DOMAIN
                        )
                    ]
                ]
            )
            ->add(
                'mode_v2_simplifie',
                'checkbox',
                [
                    'value' => 1,
                    'required' => false,
                    'label' => $this->translator->trans('Simplified migration of 1.0 account', [], AtosV2::MODULE_DOMAIN),
                    'label_attr' => [
                        'help' => $this->translator->trans(
                            'Somes AtosV2 1.0 accounts are migrated in 2.0 in a specific way, called "simplified migration". Please check with your account manager to get this information.',
                            [],
                            AtosV2::MODULE_DOMAIN
                        )
                    ]
                ]
            )
            ->add(
                'send_payment_confirmation_message',
                'checkbox',
                [
                    'value' => 1,
                    'required' => false,
                    'label' => $this->translator->trans('Send a payment confirmation e-mail', [], AtosV2::MODULE_DOMAIN),
                    'label_attr' => [
                        'help' => $this->translator->trans(
                            'If checked, a payment confirmation e-mail is sent to the customer.',
                            [],
                            AtosV2::MODULE_DOMAIN
                        )
                    ]
                ]
            )

            // -- Multiple times payement parameters, hidden id the AtosV2Nx module is not activated.
            ->add(
                'nx_nb_installments',
                $multiEnabled ? 'text' : 'hidden',
                [
                    'constraints' => [
                        new NotBlank(),
                        new GreaterThanOrEqual(['value' => 1 ])
                    ],
                    'required' => $multiEnabled,
                    'label' => $translator->trans('Number of installments', [], AtosV2::MODULE_DOMAIN),
                    'label_attr' => [
                        'for' => 'nx_nb_installments',
                        'help' => $translator->trans(
                            'Number of installements. Should be more than one',
                            [],
                            AtosV2::MODULE_DOMAIN
                        )
                    ]
                ]
            )
            ->add(
                'nx_minimum_amount',
                $multiEnabled ? 'text' : 'hidden',
                [
                    'constraints' => [
                        new NotBlank(),
                        new GreaterThanOrEqual(['value' => 0 ])
                    ],
                    'required' => $multiEnabled,
                    'label' => $translator->trans('Minimum order total', [], AtosV2::MODULE_DOMAIN),
                    'label_attr' => [
                        'for' => 'nx_minimum_amount',
                        'help' => $translator->trans(
                            'Minimum order total in the default currency for which the multiple times payment method is available. Enter 0 for no minimum',
                            [],
                            AtosV2::MODULE_DOMAIN
                        )
                    ]
                ]
            )
            ->add(
                'nx_maximum_amount',
                $multiEnabled ? 'text' : 'hidden',
                [
                    'constraints' => [
                        new NotBlank(),
                        new GreaterThanOrEqual([ 'value' => 0 ])
                    ],
                    'required' => $multiEnabled,
                    'label' => $translator->trans('Maximum order total', [], AtosV2::MODULE_DOMAIN),
                    'label_attr' => [
                        'for' => 'nx_maximum_amount',
                        'help' => $translator->trans(
                            'Maximum order total in the default currency for which the multiple times payment method is available. Enter 0 for no maximum',
                            [],
                            AtosV2::MODULE_DOMAIN
                        )
                    ]
                ]
            )
        ;
    }

    /**
     * @return string the name of you form. This name must be unique
     */
    public function getName()
    {
        return 'config';
    }
}
