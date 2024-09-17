<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\Blog;

use Modules\Blog\Controller\DashboardController;

class DashboardRouter extends \Core\Module\Router {

    /**
     * @var string
     */
    public string $routerType = "dashboard_blog";

    /**
     * @var string
     */
    public string $router = "/dashboard/blog";

    /**
     * @var array|array[]
     */
    public array $mapForUriBuilder = [
        'list' => [
            'callback' => 'list',
            'pattern' =>'',
            'method'=>['GET']
        ],
        'post_add' => [
            'callback' => 'postAdd',
            'pattern' =>'/add',
            'method'=>['GET', 'POST']
        ],
        'post_delete' => [
            'callback' => 'postDelete',
            'pattern' =>'/remove-{postId:[0-9]+}',
            'method'=>['DELETE']
        ],
        'post_edit' => [
            'callback' => 'postEdit',
            'pattern' =>'/edit-{postId:[0-9]+}',
            'method'=>['GET', 'POST']
        ],
    ];

    /**
     * @var string
     */
    public string $controller = DashboardController::class;

}
