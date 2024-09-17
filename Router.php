<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\Blog;

use Modules\Blog\Controller\IndexController;

class Router extends \Core\Module\Router {

    /**
     * @var string
     */
    public string $routerType = "blog";

    /**
     * @var string
     */
    public string $router = "/blog";

    /**
     * @var array|array[]
     */
    public array $mapForUriBuilder = [
        'list' => [
            'callback' => 'list',
            'pattern' =>'',
            'method'=>['GET']
        ],
        'post' => [
            'callback' => 'post',
            'pattern' =>'/{post:[a-z0-9-_]+}',
            'method'=>['GET']
        ],
        'tag' => [
            'callback' => 'tag',
            'pattern' => '/{tag:[A-ZÜÄÖa-zöäüß0-9 +-_]+}',
            'method'=>['GET']
        ],
    ];

    /**
     * @var string
     */
    public string $controller = IndexController::class;

}
