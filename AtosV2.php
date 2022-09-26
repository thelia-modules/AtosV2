<?php
/*************************************************************************************/
/*      Copyright (c) Franck Allimant, CQFDev                                        */
/*      email : thelia@cqfdev.fr                                                     */
/*      web : http://www.cqfdev.fr                                                   */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE      */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace AtosV2;

use AtosV2\Api\AtosV2Api;
use Propel\Runtime\Connection\ConnectionInterface;
use Symfony\Component\Routing\Router;
use Thelia\Model\Message;
use Thelia\Model\MessageQuery;
use Thelia\Model\Order;
use Thelia\Module\AbstractPaymentModule;
use Thelia\Tools\URL;

/**
 * Class AtosV2
 * @package AtosV2
 * @author  Franck Allimant <franck@cqfdev.fr>
 */
class AtosV2 extends AbstractPaymentModule
{
    const MODULE_DOMAIN = 'atosv2';

    /**
     * The confirmation message identifier
     */
    const CONFIRMATION_MESSAGE_NAME = 'atosv2_payment_confirmation';

    /**
     * @param ConnectionInterface|null $con
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function postActivation(ConnectionInterface $con = null)
    {
        // Setup some default values
        if (null === self ::getConfigValue('merchantId', null)) {
            // Initialize with test data

            self::setConfigValue('merchantId', '002001000000001');
            self::setConfigValue('secretKeyVersion', 1);
            self::setConfigValue('secretKey', '002001000000001_KEY1');
            self::setConfigValue('mode', 'TEST');
            self::setConfigValue('allowed_ip_list', $_SERVER['REMOTE_ADDR']);
            self::setConfigValue('minimum_amount', 0);
            self::setConfigValue('maximum_amount', 0);
            self::setConfigValue('send_payment_confirmation_message', 1);
            self::setConfigValue('transactionId', 1);
            self::setConfigValue('mode_v2_simplifie', 0);
        }

        if (null === self ::getConfigValue('server_url_bank_prod', null)) {
            self::setConfigValue('server_url_bank_test', 'https://payment-webinit.simu.sips-services.com/paymentInit');
            self::setConfigValue('server_url_bank_prod', '');
        }

        if (null === MessageQuery::create()->findOneByName(self::CONFIRMATION_MESSAGE_NAME)) {
            $message = new Message();

            $message
                ->setName(self::CONFIRMATION_MESSAGE_NAME)
                ->setHtmlTemplateFileName('atosv2-payment-confirmation.html')
                ->setTextTemplateFileName('atosv2-payment-confirmation.txt')
                ->setLocale('en_US')
                ->setTitle('AtosV2 payment confirmation')
                ->setSubject('Payment of order {$order_ref}')
                ->setLocale('fr_FR')
                ->setTitle('Confirmation de paiement par AtosV2')
                ->setSubject('Confirmation du paiement de votre commande {$order_ref}')
                ->save()
            ;
        }
    }

    /**
     * @param ConnectionInterface|null $con
     * @param bool $deleteModuleData
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function destroy(ConnectionInterface $con = null, $deleteModuleData = false)
    {
        if ($deleteModuleData) {
            MessageQuery::create()->findOneByName(self::CONFIRMATION_MESSAGE_NAME)->delete();
        }
    }

    /**
     *
     * generate a transaction id
     * @return int|mixed
     */
    private function generateTransactionID()
    {
        $transId = self::getConfigValue('transactionId', 1);

        $transId = 1 + (int)$transId;

        self::setConfigValue('transactionId', $transId);

        return sprintf('%s%d', uniqid('', false), $transId);
    }

    /**
     *
     * generate a V1 transaction id 'reset every 24 hour
     * @return int|mixed
     */
    private function generateV1SimplifieTransactionID()
    {
        $transId = self::getConfigValue('v1TransactionId', 1);

        $transId = 1 + (int)$transId;

        // This ID is supposed unique for a single day. We wiil not rester it everyday, nut we will
        // set is to 1 when the limit size (6 digits) is reached.
        if ($transId > 999999) {
            $transId = 1;
        }

        self::setConfigValue('v1TransactionId', $transId);

        return sprintf("%06d", $transId);
    }

    /**
     *
     *  Method used by payment gateway.
     *
     *  If this method return a \Thelia\Core\HttpFoundation\Response instance, this response is send to the
     *  browser.
     *
     *  In many cases, it's necessary to send a form to the payment gateway.
     *  On your response you can return this form already completed, ready to be sent
     *
     * @param  Order $order processed order
     * @return null|\Thelia\Core\HttpFoundation\Response
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function pay(Order $order)
    {
        $estModeV2Simplifile = (bool) self::getConfigValue('mode_v2_simplifie');

        $amount = $order->getTotalAmount();
        $customer = $order->getCustomer();

        /** @var Router $router */
        $router = $this->getContainer()->get('router.atosv2');

        // Initialisation de la classe AtosV2 avec passage en parametre de la cle secrete
        $paymentRequest = new AtosV2Api(self::getConfigValue('secretKey'));

        if ($estModeV2Simplifile) {
            $transactionId = $this->generateV1SimplifieTransactionID();
            $paymentRequest->sets10TransactionId($transactionId);
        } else {
            $transactionId = $this->generateTransactionID();
            $paymentRequest->setTransactionReference($transactionId);
        }

        // Indiquer quelle page de paiement appeler : TEST ou PRODUCTION
        if ('TEST' === self::getConfigValue('mode', 'TEST')) {
            $paymentRequest->setUrl(AtosV2::getConfigValue('server_url_bank_test'));
        } else {
            $paymentRequest->setUrl(AtosV2::getConfigValue('server_url_bank_prod'));
        }

        // Renseigner les parametres obligatoires pour l'appel de la page de paiement
        $paymentRequest->setMerchantId(self::getConfigValue('merchantId'));
        $paymentRequest->setKeyVersion(self::getConfigValue('secretKeyVersion'));

        $paymentRequest->setAmount((int)round(100 * $amount));

        $paymentRequest->setCurrency($order->getCurrency()->getCode());

        $paymentRequest->setNormalReturnUrl(URL::getInstance()->absoluteUrl($router->generate('atosv2.payment.manual_response')));
        $paymentRequest->setAutomaticResponseUrl(URL::getInstance()->absoluteUrl($router->generate('atosv2.payment.confirmation')));

        // Renseigner les parametres facultatifs pour l'appel de la page de paiement
        try {
            $paymentRequest->setLanguage(substr($order->getLang()->getCode(), 0, 2));
        } catch (\Exception $ex) {
            $paymentRequest->setLanguage('en');
        }

        $paymentRequest->setCustomerContactEmail($customer->getEmail());
        $paymentRequest->setOrderId($order->getId());
        $order->setTransactionRef($transactionId)->save();

        // Verification de la validite des parametres renseignes
        $paymentRequest->validate();


        // Appel de la page de paiement AtosV2 avec le connecteur POST en passant en parametres : Data, InterfaceVersion, Seal
        /*
        echo "<html><body><form name=\"redirectForm\" method=\"POST\" action=\"" . $paymentRequest->getUrl() . "\">" .
            "<input type=\"hidden\" name=\"Data\" value=\"". $paymentRequest->toParameterString() . "\">" .
            "<input type=\"hidden\" name=\"InterfaceVersion\" value=\"". self::INTERFACE_VERSION . "\">" .
            "<input type=\"hidden\" name=\"Seal\" value=\"" . $paymentRequest->getShaSign() . "\">" .
            "<noscript><input type=\"submit\" name=\"Go\" value=\"Click to continue\"/></noscript> </form>" .
            "<script type=\"text/javascript\"> document.redirectForm.submit(); </script>" .
            "</body></html>";
        */

        return $this->generateGatewayFormResponse(
            $order,
            $paymentRequest->getUrl(),
            [
                'Data' => $paymentRequest->toParameterString(),
                'InterfaceVersion' => AtosV2Api::INTERFACE_VERSION,
                'Seal' => $paymentRequest->getShaSign(),
            ]
        );
    }

    /**
     * @return boolean true to allow usage of this payment module, false otherwise.
     */
    public function isValidPayment()
    {
        $valid = (null !== self::getConfigValue('secretKey')) && (null !== self::getConfigValue('merchantId'));

        if ($valid) {
            $mode = self::getConfigValue('mode', false);

            // If we're in test mode, do not display Payzen on the front office, except for allowed IP addresses.
            if ('TEST' === $mode) {
                $raw_ips = explode("\n", self::getConfigValue('allowed_ip_list', ''));

                $allowed_client_ips = array();

                foreach ($raw_ips as $ip) {
                    $allowed_client_ips[] = trim($ip);
                }

                $client_ip = $this->getRequest()->getClientIp();

                $valid = in_array($client_ip , $allowed_client_ips , true);

            } elseif ('PRODUCTION' === $mode) {
                $valid = true;
            }

            if ($valid) {
                // Check if total order amount is in the module's limits
                $valid = $this->checkMinMaxAmount();
            }
        }

        return $valid;
    }

    /**
     * Check if total order amount is in the module's limits
     *
     * @return bool true if the current order total is within the min and max limits
     */
    protected function checkMinMaxAmount()
    {
        // Check if total order amount is in the module's limits
        $order_total = $this->getCurrentOrderTotalAmount();

        $min_amount = self::getConfigValue('minimum_amount', 0);
        $max_amount = self::getConfigValue('maximum_amount', 0);

        return
            $order_total > 0
            &&
            ($min_amount <= 0 || $order_total >= $min_amount) && ($max_amount <= 0 || $order_total <= $max_amount);
    }
}
