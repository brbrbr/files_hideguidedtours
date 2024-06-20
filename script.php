<?php

/**
 * @package     Brambring.Plugin
 * @subpackage  System.Extensiontools
 * @version    24.02.01
 * @copyright  2024 Bram Brambring
 * @license    GNU General Public License version 3 or later;
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\Application\AbstractApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

// phpcs:disable PSR12.Classes.AnonClassDeclaration
return new class() implements
	ServiceProviderInterface
{
	// phpcs:enable PSR12.Classes.AnonClassDeclaration
	public function register(Container $container)
	{
		$container->set(
			InstallerScriptInterface::class,
			// phpcs:disable PSR12.Classes.AnonClassDeclaration
			new class() implements
				InstallerScriptInterface
			{
				// phpcs:enable PSR12.Classes.AnonClassDeclaration
				protected AbstractApplication $app;
				protected DatabaseDriver $db;


				public function __construct()
				{
					$this->app = Factory::getApplication();
					$this->db  = Factory::getContainer()->get(DatabaseInterface::class);
				}

				public function install(InstallerAdapter $adapter): bool
				{

					$this->NoWorries();
					return true;
				}

				public function update(InstallerAdapter $adapter): bool
				{
					$this->NoWorries();
					return true;
				}

				public function uninstall(InstallerAdapter $adapter): bool
				{
					$this->NoWorries();
					return true;
				}
				public function preflight(string $type, InstallerAdapter $adapter): bool
				{
					// Do not run on uninstall.
					//and this file extension should never be installed
					if ($type === 'uninstall') {
						return true;
					}
				
		
					$query = $this->db->getquery(true);
					$query->delete($this->db->quoteName('#__modules'))
						->where($this->db->quoteName('module') . ' = ' . $this->db->quote('mod_guidedtours'));
					try {
						$this->db->setQuery($query)->execute();
						$this->app->enqueueMessage(TEXT::_('FILES_HIDEGUIDEDTOURS_MODULES'), 'notice');
					} catch (\Exception $e) {
						$this->app->enqueueMessage($e->getMessage(), 'error');
					}

					$query = $this->db->getquery(true);
					$query->update($this->db->quoteName('#__extensions'))
						->set($this->db->quoteName('enabled') . ' = 0')
						->where($this->db->quoteName('element') . ' like ' . $this->db->quote('%guidedtours%'));

					try {
						$this->db->setQuery($query)->execute();
						$this->app->enqueueMessage(TEXT::_('FILES_HIDEGUIDEDTOURS_EXTENSIONS'), 'notice');
					} catch (\Exception $e) {
						$this->app->enqueueMessage($e->getMessage(), 'error');
					}

					try {
						$query = 'TRUNCATE TABLE ' . $this->db->quotename('#__guidedtours');
						$this->db->setQuery($query)->execute();
						$this->app->enqueueMessage(TEXT::_('FILES_HIDEGUIDEDTOURS_TOURS'), 'notice');
					} catch (\Exception $e) {
						$this->app->enqueueMessage($e->getMessage(), 'error');
					}

					try {
						$query = 'TRUNCATE TABLE ' . $this->db->quotename('#__guidedtour_steps');
						$this->db->setQuery($query)->execute();
						$this->app->enqueueMessage(TEXT::_('FILES_HIDEGUIDEDTOURS_STEPS'), 'notice');
					} catch (\Exception $e) {
						$this->app->enqueueMessage($e->getMessage(), 'error');
					}

					$this->app->enqueueMessage(TEXT::_('FILES_HIDEGUIDEDTOURS_FINISHED'), 'success');

					$this->NoWorries();
					return false;
				}
				public function postflight(string $type, InstallerAdapter $adapter): bool
				{
					//$this->NoWorries();
					return true;
				}
				private function NoWorries()
				{
					$this->app->enqueueMessage(TEXT::_('FILES_HIDEGUIDEDTOURS_NOWORRY'), 'warning');
				}
			}
		);
	}
};
