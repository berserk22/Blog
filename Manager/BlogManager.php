<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\Blog\Manager;

use DI\DependencyException;
use DI\NotFoundException;
use Modules\Blog\BlogTrait;

class BlogManager {

    use BlogTrait;

    private string $blogEntity = "Blog\Blog";

    /**
     * @return void
     */
    public function initEntity(): void {
        if (!$this->getContainer()->has($this->blogEntity)){
            $this->getContainer()->set($this->blogEntity, function () {
                return "Modules\Blog\Db\Models\Blog";
            });
        }
    }

    /**
     * @return mixed
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getBlogEntity(): mixed {
        return $this->getContainer()->get($this->blogEntity);
    }

}
