<?php
/*************************************************************************************/
/*      Copyright (c) Franck Allimant, CQFDev                                        */
/*      email : thelia@cqfdev.fr                                                     */
/*      web : http://www.cqfdev.fr                                                   */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE      */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace AtosV2\Hook;

use AtosV2\AtosV2;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;
use Thelia\Model\ModuleConfig;
use Thelia\Model\ModuleConfigQuery;

/**
 * Class HookManager
 * @package AtosV2\Hook
 * @author  Franck Allimant <franck@cqfdev.fr>
 */
class HookManager extends BaseHook
{
    const MAX_TRACE_SIZE_IN_BYTES = 40000;

    public function onModuleConfigure(HookRenderEvent $event)
    {
        $logFilePath = sprintf(THELIA_ROOT."log".DS."%s.log", AtosV2::MODULE_DOMAIN);

        $traces = @file_get_contents($logFilePath);

        if (false === $traces) {
            $traces = $this->translator->trans(
                "The log file '%log' does not exists yet.",
                [ '%log' => $logFilePath ],
                AtosV2::MODULE_DOMAIN
            );
        } elseif (empty($traces)) {
            $traces = $this->translator->trans("The log file is currently empty.", [], AtosV2::MODULE_DOMAIN);
        } else {
            // Limiter la taille des traces à 1MO
            if (strlen($traces) > self::MAX_TRACE_SIZE_IN_BYTES) {
                $traces = substr($traces, strlen($traces) - self::MAX_TRACE_SIZE_IN_BYTES);
                // Cut a first line break;
                if (false !== $lineBreakPos = strpos($traces, "\n")) {
                    $traces = substr($traces, $lineBreakPos+1);
                }

                $traces = $this->translator->trans(
                    "(Previous log is in %file file.)\n",
                    [ '%file' => sprintf("log".DS."%s.log", AtosV2::MODULE_DOMAIN) ],
                    AtosV2::MODULE_DOMAIN
                ) . $traces;
            }
        }

        $vars = [ 'trace_content' => nl2br($traces)  ];

        if (null !== $params = ModuleConfigQuery::create()->findByModuleId(AtosV2::getModuleId())) {
            /** @var ModuleConfig $param */
            foreach ($params as $param) {
                $vars[ $param->getName() ] = $param->getValue();
            }
        }

        $event->add(
            $this->render('atosv2/module-configuration.html', $vars)
        );
    }
}
