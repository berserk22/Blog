<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\Blog\Controller;

use Core\Module\Controller;
use DI\DependencyException;
use DI\NotFoundException;
use Modules\Blog\BlogTrait;
use Modules\Seo\Manager\SeoModel;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Slim\Http\ServerRequest as Request;
use Slim\Http\Response;
use Spatie\SchemaOrg\Schema;

class IndexController extends Controller {

    use BlogTrait;

    private string $seoModel = "Seo\Model";

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     */
    protected function registerFunctions(): void {
        $this->getRouter()->getMapBuilder($this);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     */
    public function list(Request $request, Response $response) :Response {
        $list = $this->getBlogModel()->getList($request);

        if ($this->getContainer()->has($this->seoModel)){
            /** @var SeoModel $seoModel */
            $seoModel = $this->getContainer()->get($this->seoModel);
            $domainSetting = $this->getConfig("domain");
            $schema = Schema::blog();
            $blogPosts = [];
            foreach ($list as $post){
                $tmpPost = Schema::blogPosting()
                    ->name($post->title)
                    ->description($post->description);
                if (!empty($post->image)){
                    $tmpPost->image($domainSetting["protocol"].'://'.$domainSetting["name"].$post->image);
                }
                $tmpPost->dateCreated(date(DATE_ATOM, strtotime($post->created_at)))
                    ->dateModified(date(DATE_ATOM, strtotime($post->updated_at)))
                    ->commentCount(0)
                    ->wordCount(1);
                $blogPosts[] = $tmpPost;
            }
            $schema->blogPosts($blogPosts);
            $seoModel->setSchema("blog", $schema->toScript());
        }

        $this->getView()->setVariables([
            'seo'=>[
                'title'=>$this->getI18nModel()->translate('Unsere Blog'),
                'description'=>'',
            ],
            'breadcrumbs'=> [
                $this->getI18nModel()->translate('Home')=>['main_home'],
                $this->getI18nModel()->translate('Blog')=>''
            ],
            'list'=>$list,
            'paginate'=>[
                'count'=>$list->count(),
                'currentPage'=>$list->currentPage(),
                'lastPage'=>$list->lastPage(),
                'nextPageUrl'=>$list->nextPageUrl(),
                'lastPageUrl'=>$this->getRouter()->getUrl('blog_list').'?page='.$list->lastPage(),
                'next_page'=>$this->getBlogModel()->nextPage,
                'previousPageUrl'=>$list->previousPageUrl(),
                'firstPageUrl'=>$this->getRouter()->getUrl('blog_list'),
                'prev_page'=>$this->getBlogModel()->prevPage,
                'total'=>$list->total()
            ],
            'tags'=>$this->getBlogModel()->getTagsListe(),
            'last_posts'=>$this->getBlogModel()->getLastPost()
        ]);
        return $this->getView()->render($response, 'blog/list');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function post(Request $request, Response $response) :Response {
        $post = $this->getBlogModel()->getPost($request);
        if ($post === null){
            $response->withStatus(404);
            $this->getView()->setVariables([
                'seo'=>[
                    'title'=>'404 - Not Found',
                    'description'=>'Page Not Found',
                    'type'=>'article'
                ],
                'breadcrumbs'=>[
                    $this->getI18nModel()->translate('Home')=>['main_home'],
                    $this->getI18nModel()->translate('Page Not Found')=>''
                ],
            ]);
            return $this->getView()->render($response, 'error/404')->withStatus(404);
        }

        $post->content = $this->getView()->getHtmlFromContent(str_replace("&gt;", ">", $post->content));

        $post->tags = json_decode($post->tags, true);

        /** @var SeoModel $seoModel */
        $seoModel = $this->getContainer()->get($this->seoModel);
        $domainSetting = $this->getConfig("domain");
        $schema = Schema::blog();
        $blogPosts = Schema::blogPosting()
            ->name($post->title)
            ->description($post->description);
        if (!empty($post->image)){
            $blogPosts->image($domainSetting["protocol"].'://'.$domainSetting["name"].$post->image);
        }
        $blogPosts->dateCreated(date(DATE_ATOM, strtotime($post->created_at)))
            ->dateModified(date(DATE_ATOM, strtotime($post->updated_at)))
            ->commentCount(0)
            ->wordCount(1);
        $schema->blogPost($blogPosts);
        $seoModel->setSchema("blogPost", $schema->toScript());

        $this->getView()->setVariables([
            'seo'=>[
                'title'=>$post->title,
                'image'=>$post->image,
                'description'=>$post->description,
                'type'=>'article',
                'article'=>[
                    'tag'=>$this->getBlogModel()->getTagListeFromPost(json_encode($post->tags)),
                    'published_time'=>date("c", strtotime($post->created_at)),
                    'modified_time'=>date("c", strtotime($post->updated_at)),
                ],
            ],
            'breadcrumbs'=>[
                'Home'=>['main_home'],
                'Blog'=>['blog_list'],
                $post->title=>""
            ],
            'post'=>$post,
            'tags'=>$this->getBlogModel()->getTagsListe(),
            'last_posts'=>$this->getBlogModel()->getLastPost()
        ]);
        return $this->getView()->render($response, 'blog/post');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     */
    public function tag(Request $request, Response $response) :Response {
        $tag=urlencode($request->getAttribute('tag'));
        $list = $this->getBlogModel()->getList($request);

        $this->getView()->setVariables([
            'seo'=>[
                'title'=>urldecode($tag),
                'description'=>'',
            ],
            'breadcrumbs'=>[
                'Home'=>['main_home', []],
                'Blog'=>['blog_list'],
                'Tag: '.urldecode($tag)=>''
            ],
            'list'=>$list,
            'paginate'=>[
                'count'=>$list->count(),
                'currentPage'=>$list->currentPage(),
                'lastPage'=>$list->lastPage(),
                'nextPageUrl'=>$list->nextPageUrl(),
                'lastPageUrl'=>$this->getRouter()->getUrl('blog_list').'/'.urldecode($tag).'?page='.$list->lastPage(),
                'next_page'=>$this->getBlogModel()->nextPage,
                'previousPageUrl'=>$list->previousPageUrl(),
                'firstPageUrl'=>$this->getRouter()->getUrl('blog_list').'/'.urldecode($tag),
                'prev_page'=>$this->getBlogModel()->prevPage,
                'total'=>$list->total()
            ],
            'tags'=>$this->getBlogModel()->getTagsListe(),
            'last_posts'=>$this->getBlogModel()->getLastPost()
        ]);
        return $this->getView()->render($response, 'blog/list');
    }

}
