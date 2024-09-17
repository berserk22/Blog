<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\Blog\Db;

use DI\DependencyException;
use DI\NotFoundException;
use Illuminate\Database\Schema\Blueprint;
use Modules\Database\Migration;

class Schema extends Migration {

    /**
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function create(): void {
        if (!$this->schema()->hasTable('blog')) {
            $this->schema()->create('blog', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('name');
                $table->string('title');
                $table->string('description')->nullable();
                $table->longtext('content');
                $table->string('keywords')->nullable();
                $table->string('image')->nullable();
                $table->mediumText('tags')->nullable();
                $table->string('status');
                $table->string('lang', 5);
                $table->dateTime('created_at');
                $table->dateTime('updated_at');
                $table->index('id');
            });
        }
    }

    /**
     * @return void
     */
    public function update(): void {}

    /**
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function delete(): void {
        if ($this->schema()->hasTable('blog')) {
            $this->schema()->drop('blog');
        }
    }

}
