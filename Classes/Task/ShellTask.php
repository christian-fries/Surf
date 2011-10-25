<?php
namespace TYPO3\Surf\Task;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Surf".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * A generic shell task
 *
 */
class ShellTask extends \TYPO3\Surf\Domain\Model\Task {

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\Surf\Domain\Service\ShellCommandService
	 */
	protected $shell;

	/**
	 * Executes this task
	 *
	 * Options:
	 *   command: The command to execute
	 *   rollbackCommand: The command to execute as a rollback (optional)
	 *
	 * @param \TYPO3\Surf\Domain\Model\Node $node
	 * @param \TYPO3\Surf\Domain\Model\Application $application
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @param array $options
	 * @return void
	 */
	public function execute(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		$deploymentPath = $application->getDeploymentPath();
		$sharedPath = $application->getSharedPath();
		$releasePath = $deployment->getApplicationReleasePath($application);
		$currentPath = $application->getDeploymentPath() . '/releases/current';
		$previousPath = $application->getDeploymentPath() . '/releases/previous';

		if (!isset($options['command'])) {
			throw new \Exception('No command option provided for ShellTask', 1311168045);
		}
		$command = $options['command'];
		$command = str_replace(array('{deploymentPath}', '{sharedPath}', '{releasePath}', '{currentPath}', '{previousPath}'), array($deploymentPath, $sharedPath, $releasePath, $currentPath, $previousPath), $command);

		$ignoreErrors = isset($options['ignoreErrors']) && $options['ignoreErrors'] === TRUE;
		$logOutput = !(isset($options['logOutput']) && $options['logOutput'] === FALSE);

		$this->shell->executeOrSimulate($command, $node, $deployment, $ignoreErrors, $logOutput);
	}

	/**
	 * Simulate this task
	 *
	 * @param Node $node
	 * @param Application $application
	 * @param Deployment $deployment
	 * @param array $options
	 * @return void
	 */
	public function simulate(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		$this->execute($node, $application, $deployment, $options);
	}

	/**
	 * Rollback this task
	 *
	 * @param \TYPO3\Surf\Domain\Model\Node $node
	 * @param \TYPO3\Surf\Domain\Model\Application $application
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @param array $options
	 * @return void
	 */
	public function rollback(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		$deploymentPath = $application->getDeploymentPath();
		$sharedPath = $application->getSharedPath();
		$releasePath = $deployment->getApplicationReleasePath($application);
		$currentPath = $application->getDeploymentPath() . '/releases/current';
		$previousPath = $application->getDeploymentPath() . '/releases/previous';

		if (!isset($options['rollbackCommand'])) {
			return;
		}
		$command = $options['rollbackCommand'];
		$command = str_replace(array('{deploymentPath}', '{sharedPath}', '{releasePath}', '{currentPath}', '{previousPath}'), array($deploymentPath, $sharedPath, $releasePath, $currentPath, $previousPath), $command);

		$this->shell->execute($command, $node, $deployment, TRUE);
	}

}
?>