<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\Blog\Plugins;

use DI\DependencyException;
use DI\NotFoundException;
use Modules\View\AbstractPlugin;

class GetLastPosts extends AbstractPlugin{

    /**
     * @param int $count
     * @return mixed
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function process(int $count=5): mixed {
        return $this->getModel()->getLastPost($count);
    }

    /**
     * @return mixed
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected function getModel(): mixed {
        if ($this->getContainer()->has('Blog\Model')) {
            return $this->getContainer()->get('Blog\Model');
        }
        return null;
    }
}
