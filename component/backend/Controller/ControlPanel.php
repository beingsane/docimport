<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Admin\Controller;

// Protect from unauthorized access
use FOF30\Container\Container;
use FOF30\Controller\Controller;

defined('_JEXEC') or die();

class ControlPanel extends Controller
{
	use Mixin\PredefinedTaskList;

	public function __construct(Container $container, array $config = array())
	{
		parent::__construct($container, $config);

		$this->predefinedTaskList = ['main'];
	}

	/**
	 * Runs before the main task, used to perform housekeeping function automatically
	 */
	protected function onBeforeMain()
	{
		/** @var \Akeeba\DocImport\Admin\Model\ControlPanel $model */
		$model = $this->getModel();
		$model
			->checkAndFixDatabase()
			->updateMagicParameters();
	}

}