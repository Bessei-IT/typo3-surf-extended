<?php
namespace BIT\Typo3SurfExtended\Application;

use BIT\Typo3SurfExtended\Task\FusioDeployTask;
use BIT\Typo3SurfExtended\Task\FusioInstallTask;
use BIT\Typo3SurfExtended\Task\MergeSharedFoldersTask;
use BIT\Typo3SurfExtended\Task\WarmupScriptsTask;
use TYPO3\Surf\Application\BaseApplication;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Workflow;

/**
 * @author Christoph Bessei
 */
class FusioApplication extends BaseApplication
{
    use ClearOpcacheTrait;

    /**
     * Extend BaseApplication with additional tasks:
     *
     *
     *
     * @param \TYPO3\Surf\Domain\Model\Workflow $workflow
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @return void
     */
    public function registerTasks(Workflow $workflow, Deployment $deployment)
    {
        parent::registerTasks($workflow, $deployment);

        $workflow->addTask(FusioDeployTask::class, 'migrate', $this);

        // Install Fusio
        if ($deployment->hasOption('initialDeployment') && $deployment->getOption('initialDeployment')) {
            $workflow->beforeTask(FusioDeployTask::class, [FusioInstallTask::class], $this);
        }

        // Merge shared folders/files on node with folders/files in VCS
        $workflow->afterStage('update', [MergeSharedFoldersTask::class,], $this);

        // Warm up
        $workflow->addTask(WarmupScriptsTask::class, 'finalize', $this);

        $this->registerClearOpcacheTaskIfEnabled($workflow, $deployment);
    }
}
