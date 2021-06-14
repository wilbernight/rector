<?php

declare (strict_types=1);
namespace RectorPrefix20210614\TYPO3\CMS\Scheduler;

use RectorPrefix20210614\TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
if (\interface_exists('TYPO3\\CMS\\Scheduler\\AdditionalFieldProviderInterface')) {
    return;
}
interface AdditionalFieldProviderInterface
{
    public function getAdditionalFields(array &$taskInfo, $task, \RectorPrefix20210614\TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule);
}