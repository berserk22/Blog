<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\Blog;

use Core\Module\Provider;
use DI\DependencyException;
use DI\NotFoundException;
use Modules\Blog\Controller\DashboardController;
use Modules\Blog\Controller\IndexController;
use Modules\Blog\Db\Schema;
use Modules\Blog\Manager\BlogManager;
use Modules\Blog\Manager\BlogModel;
use Modules\Database\MigrationCollection;
use Modules\View\PluginManager;
use Modules\View\ViewManager;

class ServiceProvider extends Provider {

    /**
     * @var string
     */
    private string $router = "Blog\Router";

    /**
     * @var string
     */
    private string $dashboardRouter = "Blog\DashboardRouter";

    /**
     * @var string
     */
    private string $apiRouter = "Blog\ApiRouter";

    /**
     * @var array
     */
    protected array $plugins = [
        'getLastPosts'=> '\Modules\Blog\Plugins\GetLastPosts',
    ];

    /**
     * @return void
     */
    public function boot(): void {
        $container = $this->getContainer();
        $container->set('Modules\Blog\Controller\IndexController', function () {
            return new IndexController($this);
        });

        $container->set('Modules\Blog\Controller\DashboardController', function () {
            return new DashboardController($this);
        });
    }

    /**
     * @return void
     */
    public function init(): void {
        $container = $this->getContainer();
        if (!$container->has($this->router)){
            $container->set($this->router, function() {
                return new Router($this);
            });
        }

        if (!$container->has($this->dashboardRouter)){
            $container->set($this->dashboardRouter, function() {
                return new DashboardRouter($this);
            });
        }

        if (!$container->has($this->apiRouter)){
            $container->set($this->apiRouter, function(){
                return new ApiRouter($this);
            });
        }
    }

    /**
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function afterInit(): void {
        $container = $this->getContainer();
        if ($container->has('ViewManager::View')) {
            /** @var $viewer ViewManager */
            $viewer = $container->get('ViewManager::View');
            $plugins = function(){
                $pluginManager = new PluginManager();
                $pluginManager->addPlugins($this->plugins);
                return $pluginManager->getPlugins();
            };
            $viewer->setPlugins($plugins());
        }

        // init blog manager
        $container->set('Blog\Manager', function (){
            $manager = new BlogManager($this);
            $manager->initEntity();
            return $manager;
        });

        $container->set('Blog\Model', function () {
            return new BlogModel($this);
        });

        // database bundle
        if ($container->has('Modules\Database\ServiceProvider::Migration::Collection')) {
            /* @var $databaseMigration MigrationCollection  */
            $container->get('Modules\Database\ServiceProvider::Migration::Collection')->add(new Schema($this));
        }
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function register(): void {
        $container = $this->getContainer();

        if ($container->has($this->dashboardRouter)){
            $container->get($this->dashboardRouter)->init();
        }
        if ($container->has($this->router)){
            $container->get($this->router)->init();
        }
        if ($container->has($this->apiRouter)){
            $container->get($this->apiRouter)->init();
        }
    }
}
