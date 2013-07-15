<?php
// {{{ICINGA_LICENSE_HEADER}}}
/**
 * Icinga 2 Web - Head for multiple monitoring frontends
 * Copyright (C) 2013 Icinga Development Team
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * @copyright 2013 Icinga Development Team <info@icinga.org>
 * @author Icinga Development Team <info@icinga.org>
 */
// {{{ICINGA_LICENSE_HEADER}}}

namespace Icinga\Application\Modules;

use Icinga\Application\ApplicationBootstrap;
use Icinga\Application\Config;
use Icinga\Web\Hook;
use Zend_Controller_Router_Route as Route;

/**
 * Module handling
 *
 * Register modules and initialize it
 */
class Module
{
    /**
     * Module name
     * @var string
     */
    private $name;

    /**
     * Base directory of module
     * @var string
     */
    private $basedir;

    /**
     * Directory for styles
     * @var string
     */
    private $cssdir;

    /**
     * Library directory
     * @var string
     */
    private $libdir;

    /**
     * Directory containing translations
     * @var string
     */
    private $localedir;

    /**
     * Directory where controllers reside
     * @var string
     */
    private $controllerdir;

    /**
     * Directory containing form implementations
     * @var string
     */
    private $formdir;

    /**
     * Module bootstrapping script
     * @var string
     */
    private $registerscript;

    /**
     * Icinga application
     * @var \Icinga\Application\ApplicationBootstrap
     */
    private $app;

    public function __construct(ApplicationBootstrap $app, $name, $basedir)
    {
        $this->app            = $app;
        $this->name           = $name;
        $this->basedir        = $basedir;
        $this->cssdir         = $basedir. '/public/css';
        $this->libdir         = $basedir. '/library';
        $this->configdir      = $basedir. '/config';
        $this->localedir      = $basedir. '/application/locale';
        $this->formdir        = $basedir. '/application/forms';
        $this->controllerdir  = $basedir. '/application/controllers';
        $this->registerscript = $basedir. '/register.php';
    }

    /**
     * Register module
     * @return bool
     */
    public function register()
    {
        $this->registerAutoloader()
             ->registerWebIntegration()
             ->runRegisterScript();
        return true;
    }

    /**
     * Test for an enabled module by name
     * @param string $name
     * @return boolean
     */
    public static function exists($name)
    {
        return Icinga::app()->moduleManager()->hasEnabled($name);
    }

    public static function get($name, $autoload = false)
    {
        $manager = Icinga::app()->moduleManager();
        if (! $manager->hasLoaded($name)) {
            if ($autoload === true && $manager->hasEnabled($name)) {
                $manager->loadModule($name);
            }
        }
        // @throws ProgrammingError:
        return $manager->getModule($name);
    }

    /**
     * Test if module provide css
     * @return bool
     */
    public function hasCss()
    {
        return file_exists($this->getCssFilename());
    }

    /**
     * Getter for module name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Getter for css file name
     * @return string
     */
    public function getCssFilename()
    {
        return $this->cssdir . '/module.less';
    }

    /**
     * Getter for base directory
     * @return string
     */
    public function getBaseDir()
    {
        return $this->basedir;
    }

    /**
     * Getter for library directory
     * @return string
     */
    public function getLibDir()
    {
        return $this->libdir;
    }

    /**
     * Getter for configuration directory
     * @return string
     */
    public function getConfigDir()
    {
        return $this->configdir;
    }

    /**
     * Getter for form directory
     * @return string
     */
    public function getFormDir()
    {
        return $this->formdir;
    }

    /**
     * Getter for module config object
     * @param null|string $file
     * @return Config
     */
    public function getConfig($file = null)
    {
        return $this->app
            ->getConfig()
            ->module($this->name, $file);
    }

    /**
     * Register new namespaces on the autoloader
     * @return Module
     */
    protected function registerAutoloader()
    {
        if (is_dir($this->getBaseDir()) && is_dir($this->getLibDir())) {
            $moduleName = ucfirst($this->getName());
            $moduleLibraryDir = $this->getLibDir(). '/'. $moduleName;

            $this->app->getLoader()->registerNamespace($moduleName, $moduleLibraryDir);

            $this->app->getLoader()->registerNamespace($moduleName. '\\Form', $this->getFormDir());
        }

        return $this;
    }

    /**
     * Bind text domain for i18n
     * @return Module
     */
    protected function registerLocales()
    {
        if (file_exists($this->localedir) && is_dir($this->localedir)) {
            bindtextdomain($this->name, $this->localedir);
        }
        return $this;
    }

    /**
     * Register web integration
     *
     * Add controller directory to mvc
     *
     * @return Module
     */
    protected function registerWebIntegration()
    {
        if (!$this->app->isWeb()) {
            return $this;
        }

        if (file_exists($this->controllerdir) && is_dir($this->controllerdir)) {
            $this->app->frontController()->addControllerDirectory(
                $this->controllerdir,
                $this->name
            );
        }

        $this->registerLocales()
             ->registerRoutes()
             ->registerMenuEntries();
        return $this;
    }

    /**
     * Register menu entries
     * @return Module
     */
    protected function registerMenuEntries()
    {
        $cfg = $this->app
            ->getConfig()
            ->module($this->name, 'menu');
        $view = $this->app->getView();
        if ($cfg) {
            $view->view->navigation = $cfg->merge($view->view->navigation);
        }
        return $this;
    }

    /**
     * Register routes for web access
     * @return Module
     */
    protected function registerRoutes()
    {
        $this->app->frontController()->getRouter()->addRoute(
            $this->name . '_jsprovider',
            new Route(
                'js/' . $this->name . '/:file',
                array(
                    'controller'    => 'static',
                    'action'        =>'javascript',
                    'moduleName'    => $this->name
                )
            )
        );
        $this->app->frontController()->getRouter()->addRoute(
            $this->name . '_img',
            new Route(
                'img/' . $this->name . '/:file',
                array(
                    'controller'    => 'static',
                    'action'        => 'img',
                    'moduleName'    => $this->name
                )
            )
        );
        return $this;
    }

    /**
     * Run module bootstrap script
     * @return Module
     */
    protected function runRegisterScript()
    {
        if (file_exists($this->registerscript)
         && is_readable($this->registerscript)) {
            include($this->registerscript);
        }
        return $this;
    }

    /**
     * Register hook
     * @param string $name
     * @param string $class
     * @param string $key
     * @return Module
     */
    protected function registerHook($name, $key, $class)
    {
        Hook::register($name, $key, $class);
        return $this;
    }
}
