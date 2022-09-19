<?php
/*************************************************************************************/
/*      Copyright (c) Franck Allimant, CQFDev                                        */
/*      email : thelia@cqfdev.fr                                                     */
/*      web : http://www.cqfdev.fr                                                   */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE      */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace AtosV2\Controller;

use AtosV2\AtosV2;
use Symfony\Component\HttpFoundation\Response;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Form\Exception\FormValidationException;
use Thelia\Tools\URL;

/**
 * Class ConfigureController
 * @package AtosV2\Controller
 * @author Franck Allimant <franck@cqfdev.fr>
 */
class ConfigureController extends BaseAdminController
{
    public function downloadLog()
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, 'atosv2', AccessManager::UPDATE)) {
            return $response;
        }

        $logFilePath = sprintf(THELIA_ROOT."log".DS."%s.log", AtosV2::MODULE_DOMAIN);

        return Response::create(
            @file_get_contents($logFilePath),
            200,
            array(
                'Content-type' => "text/plain",
                'Content-Disposition' => sprintf('Attachment;filename=atosv2-log.txt')
            )
        );

    }

    public function configure()
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, 'atosv2', AccessManager::UPDATE)) {
            return $response;
        }

        $configurationForm = $this->createForm('atosv2_configuration');
        $message = null;

        try {
            $form = $this->validateForm($configurationForm);

            // Get the form field values
            $data = $form->getData();

            foreach ($data as $name => $value) {
                if (is_array($value)) {
                    $value = implode(';', $value);
                }

                AtosV2::setConfigValue($name, $value);
            }

            $merchantId = $data['merchantId'];

            // Log configuration modification
            $this->adminLogAppend(
                "atosv2.configuration.message",
                AccessManager::UPDATE,
                "AtosV2 configuration updated"
            );

            // Redirect to the success URL,
            if ($this->getRequest()->get('save_mode') == 'stay') {
                // If we have to stay on the same page, redisplay the configuration page/
                $url = '/admin/module/AtosV2';
            } else {
                // If we have to close the page, go back to the module back-office page.
                $url = '/admin/modules';
            }

            return $this->generateRedirect(URL::getInstance()->absoluteUrl($url));
        } catch (FormValidationException $ex) {
            $message = $this->createStandardFormValidationErrorMessage($ex);
        } catch (\Exception $ex) {
            $message = $ex->getMessage();
        }
        $this->setupFormErrorContext(
            $this->getTranslator()->trans("AtosV2 configuration", [], AtosV2::MODULE_DOMAIN),
            $message,
            $configurationForm,
            $ex
        );

        return $this->generateRedirect(URL::getInstance()->absoluteUrl('/admin/module/AtosV2'));
    }
}
