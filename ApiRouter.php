<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\Blog;

use Modules\Blog\ApiController\IndexController;

class ApiRouter extends \Core\Module\ApiRouter {

    /**
     * @var int
     */
    public int $version = 1;

    /**
     * @var string
     */
    public string $routerType = "blog";

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
            'pattern' =>'/{name:[a-z0-9-_]+}',
            'method'=>['GET']
        ],
        'tag' => [
            'callback' => 'tag',
            'pattern' => '/tag/{tag:[A-Z][a-zöäüß0-9+-_]+}',
            'method'=>['GET']
        ],
    ];

    /**
     * @var string
     */
    public string $controller = IndexController::class;

}
