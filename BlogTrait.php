<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\Blog;

use Core\Traits\App;
use DI\DependencyException;
use DI\NotFoundException;
use Modules\Blog\Manager\BlogManager;
use Modules\Blog\Manager\BlogModel;
use Modules\I18n\Manager\I18nModel;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

trait BlogTrait {

    use App;

    /**
     * @return Router
     * @throws DependencyException
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getRouter(): Router {
        return $this->getContainer()->get('Blog\Router');
    }

    /**
     * @return DashboardRouter
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getDashboardBlogRouter(): DashboardRouter {
        return $this->getContainer()->get('Blog\DashboardRouter');
    }

    /**
     * @return ApiRouter
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getApiRouter(): ApiRouter {
        return $this->getContainer()->get('Blog\ApiRouter');
    }

    /**
     * @return BlogManager
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getBlogManager():BlogManager {
        return $this->getContainer()->get('Blog\Manager');
    }

    /**
     * @return BlogModel
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getBlogModel():BlogModel {
        return $this->getContainer()->get('Blog\Model');
    }

    /**
     * @return I18nModel
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getI18nModel(): I18nModel {
        return $this->getContainer()->get('I18n\Model');
    }

}
