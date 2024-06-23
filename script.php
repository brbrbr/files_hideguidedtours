<?php

/**
 * @package    files_hideguidedtours
 * @subpackage  System.Extensiontools
 * @version    24.02.01
 * @copyright 2024 Bram Brambring (https://brambring.nl)
 * @license   GNU General Public License version 3 or later;
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects


use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR12.Classes.AnonClassDeclaration
return new class () implements
    ServiceProviderInterface {
    // phpcs:enable PSR12.Classes.AnonClassDeclaration
    public function register(Container $container)
    {
        $container->set(
            InstallerScriptInterface::class,
            // phpcs:disable PSR12.Classes.AnonClassDeclaration
            new class () implements
                InstallerScriptInterface {
                // phpcs:enable PSR12.Classes.AnonClassDeclaration
                private CMSApplicationInterface $app;
                private DatabaseInterface $db;
                /**
                 * @var array<string>
                 *
                 * @since 1.0.1
                 */
                private $extenionsUnpublish = [
                    'com_guidedtours',
                    'mod_guidedtours',
                    'plg_system_guidedtours',
                    'mod_sampledata',
                    'plg_sampledata_blog',
                    'plg_sampledata_multilang',
                ];

                /**
                 * @var array<string>
                 *
                 * @since 1.0.1
                 */
                private $moduleDelete = [
                    'mod_guidedtours',
                    'mod_sampledata',
                ];


                public function __construct()
                {
                    $this->app = Factory::getApplication();
                    $this->db  = Factory::getContainer()->get(DatabaseInterface::class);
                }

                public function install(InstallerAdapter $adapter): bool
                {

                    $this->noWorries();
                    return true;
                }

                public function update(InstallerAdapter $adapter): bool
                {
                    $this->noWorries();
                    return true;
                }

                public function uninstall(InstallerAdapter $adapter): bool
                {
                    $this->noWorries();
                    return true;
                }
                public function preflight(string $type, InstallerAdapter $adapter): bool
                {
                    // Do not run on uninstall.
                    //and this file extension should never be installed
                    if ($type === 'uninstall') {
                        return true;
                    }




                    try {
                        $query = $this->db->getquery(true);
                        $query->from($this->db->quoteName('#__modules'))
                            ->select($this->db->quoteName(['asset_id', 'id']))
                            ->whereIn($this->db->quoteName('module'), $this->moduleDelete, ParameterType::STRING);
                        $this->db->setQuery($query);
                        $results = $this->db->loadAssocList();
                        if ($results) {
                            $pks    = ArrayHelper::getColumn($results, 'id');
                            $assets = ArrayHelper::getColumn($results, 'asset_id');


                            $query = $this->db->getQuery(true)
                                ->delete($this->db->quoteName('#__modules_menu'))
                                ->whereIn($this->db->quoteName('moduleid'), $pks, ParameterType::INTEGER);
                            $this->db->setQuery($query)->execute();



                            $query = $this->db->getQuery(true)
                                ->delete($this->db->quoteName('#__assets'))
                                ->whereIn($this->db->quoteName('id'), $assets, ParameterType::INTEGER);
                            $this->db->setQuery($query)->execute();

                            $query = $this->db->getQuery(true)
                                ->delete($this->db->quoteName('#__modules'))
                                ->whereIn($this->db->quoteName('id'), $pks, ParameterType::INTEGER);
                            $this->db->setQuery($query)->execute();



                            $this->app->enqueueMessage(TEXT::_('FILES_HIDEGUIDEDTOURS_MODULES'), 'notice');
                        }
                    } catch (\Exception $e) {
                        $this->app->enqueueMessage($e->getMessage(), 'error');
                    }


                    try {
                        $query = $this->db->getquery(true);
                        $query->update($this->db->quoteName('#__extensions'))
                            ->set($this->db->quoteName('enabled') . ' = 0')
                            ->whereIn($this->db->quoteName('name'), $this->extenionsUnpublish, ParameterType::STRING);

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

                    $this->noWorries();
                    return false;
                }
                public function postflight(string $type, InstallerAdapter $adapter): bool
                {
                    //$this->noWorries();
                    return true;
                }
                private function noWorries(): void
                {
                    $this->app->enqueueMessage(TEXT::_('FILES_HIDEGUIDEDTOURS_NOWORRY'), 'warning');
                }
            }
        );
    }
};
