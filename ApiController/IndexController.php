<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\Blog\ApiController;

use DI\DependencyException;
use DI\NotFoundException;
use Modules\Blog\BlogTrait;
use Modules\Rest\Manager\AbstractManager;
use OpenApi\Annotations as OA;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Slim\Http\ServerRequest as Request;
use Slim\Http\Response;

class IndexController extends AbstractManager {

    use BlogTrait;

    /**
     * @return array
     */
    public function options(): array  {
        return [
            self::VERSION => 1,
            self::METHOD => 'blog'
        ];
    }

    /**
    * @return void
    * @throws DependencyException
    * @throws NotFoundException
     */
    protected function registerFunctions():void {
        $this->getApiRouter()->getMapBuilder($this);
    }

    /**
     * @OA\Get(
     *   path="/blog",
     *   summary="List a Blog Posts",
     *   tags={"Blog"},
     *   @OA\Parameter(
     *      name="page",
     *      in="query",
     *      description="Seite Number",
     *      required=false,
     *      @OA\Schema(
     *          type="string",
     *      )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="A list with posts",
     *      content={
     *         @OA\MediaType(
     *             mediaType="application/json"
     *         )
     *     }
     *   ),
     *   security={
     *     {"oauth2": {"read"}}
     *   }
     * )
     */
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function list(Request $request, Response $response) :Response {
        $list = $this->getBlogManager()->getBlogEntity()::all();
        $this->getView()->setVariables([
            'success'=>true,
            'posts'=>$list->toArray()
        ]);

        return $this->getView()->renderJson($response);
    }

    /**
     * @OA\Get(
     *   path="/blog/tags",
     *   summary="Blog Tags List",
     *   tags={"Blog"},
     *   @OA\Response(
     *     response=200,
     *     description="A list with posts",
     *      content={
     *         @OA\MediaType(
     *             mediaType="application/json"
     *         )
     *     }
     *   ),
     *   security={
     *     {"oauth2": {"read"}}
     *   }
     * )
     */
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getTags(Request $request, Response $response) :Response{
        return $this->getView()
            ->renderJson($response, array_merge(
                ['success' => true],
                ['tags'=>$this->getBlogModel()->getTagsListe()]
            ));
    }

    /**
     * @OA\Get(
     *   path="/blog/{name}",
     *   summary="Blog Post",
     *   tags={"Blog"},
     *   @OA\Parameter(
     *      name="name",
     *      in="path",
     *      description="Post Name",
     *      required=true,
     *      @OA\Schema(
     *          type="string",
     *      )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="A list with posts",
     *      content={
     *         @OA\MediaType(
     *             mediaType="application/json"
     *         )
     *     }
     *   ),
     *   security={
     *     {"oauth2": {"read"}}
     *   }
     * )
     */
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
            return $this->notFound($request, $response);
        }

        $data=[
            'seo'=>[
                'title'=>$post->title,
                'image'=>$post->image,
                'description'=>$post->description,
                'type'=>'article',
                'article'=>[
                    'tag'=>$this->getBlogModel()->getTagListeFromPost($post->tags),
                    'published_time'=>date("c", strtotime($post->created_at)),
                    'modified_time'=>date("c", strtotime($post->updated_at)),
                ],
                'breadcrumbs'=>[
                    'Home'=>'/',
                    'Blog'=>'/blog',
                    $post->title=>""
                ]
            ],
            'post'=>$post,
            'tags'=>$this->getBlogModel()->getTagsListe(),
            'last_posts'=>$this->getBlogModel()->getLastPost()
        ];
        return $this->getView()->renderJson($response, $data);
    }

    /**
     * @OA\Get(
     *   path="/blog/tag/{tag}",
     *   summary="List a Blog Posts",
     *   tags={"Blog"},
     *   @OA\Parameter(
     *      name="tag",
     *      in="path",
     *      description="Tag Name",
     *      required=true,
     *      @OA\Schema(
     *          type="string",
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="page",
     *      in="query",
     *      description="Seite Number",
     *      required=false,
     *      @OA\Schema(
     *          type="string",
     *      )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="A list with posts",
     *     content={
     *        @OA\MediaType(
     *            mediaType="application/json"
     *        )
     *     }
     *   ),
     *   security={
     *     {"oauth2": {"read"}}
     *   }
     * )
     */
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws DependencyException
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function tags(Request $request, Response $response) :Response {
        $tag=urlencode($request->getAttribute('tag'));
        $list = $this->getBlogModel()->getList($request);

        $data=[
            'seo'=>[
                'title'=>'Tag: '.$tag,
                'description'=>'',
                'breadcrumbs'=>[
                    'Home'=>'/',
                    'Blog'=>'/blog',
                    'Tag: '.$tag=>''
                ]
            ],
            'list'=>$list,
            'paginate'=>[
                'count'=>$list->count(),
                'currentPage'=>$list->currentPage(),
                'lastPage'=>$list->lastPage(),
                'nextPageUrl'=>$list->nextPageUrl(),
                'lastPageUrl'=>'/blog/tag-'.$tag.'?page='.$list->lastPage(),
                'next_page'=>$this->getBlogModel()->next_page,
                'previousPageUrl'=>$list->previousPageUrl(),
                'firstPageUrl'=>'/blog/tag-'.$tag,
                'prev_page'=>$this->getBlogModel()->prev_page,
                'total'=>$list->total()
            ],
            'tags'=>$this->getBlogModel()->getTagsListe(),
            'last_posts'=>$this->getBlogModel()->getLastPost(5)
        ];
        return $this->getView()->renderJson($response, array_merge(['success' => true], $data));
    }

}
