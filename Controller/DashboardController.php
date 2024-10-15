<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\Blog\Controller;

use Core\Exception;
use Core\Module\Dashboard;
use DI\DependencyException;
use DI\NotFoundException;
use Modules\Blog\BlogTrait;
use Modules\Dashboard\DashboardTrait;
use Modules\User\UserTrait;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Psr7\UploadedFile;

class DashboardController extends Dashboard {

    use DashboardTrait, BlogTrait, UserTrait;

    /**
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function registerFunctions(): void {
        $this->getDashboardBlogRouter()->getMapBuilder($this);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function list(Request $request, Response $response): Response {
        $posts = $this->getBlogManager()->getBlogEntity()::all();
        $this->getView()->setVariables([
            'seo'=>[
                'title'=>'Blog',
            ],
            'breadcrumbs'=>[
                'Dashboard'=>['dashboard_home'],
                'Blog'=>''
            ],
            'posts'=>$posts
        ]);
        return $this->getView()->render($response, 'blog/list');
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
    public function postAdd(Request $request, Response $response): Response {
        if (!$this->getAuth()->getStatus()){
            return $response->withRedirect($this->getUserRouter()->getUrl('dashboard_login'));
        }

        $formData = $request->getParsedBody();
        if (!empty($formData)){

            $dir = WEB_ROOT_DIR."/".$this->upload."/".date("Y")."/".date("m")."/";
            $web_path = "/uploads/".date("Y")."/".date("m")."/";

            if (isset($request->getUploadedFiles()["file_0"]) && !empty($request->getUploadedFiles()["file_0"])){
                /** @var UploadedFile $img*/
                $img = $request->getUploadedFiles()["file_0"];
                @mkdir(WEB_ROOT_DIR."/".$this->upload."/".date("Y"));
                @mkdir(WEB_ROOT_DIR."/".$this->upload."/".date("Y")."/".date("m"));
                $img->moveTo($dir.$img->getClientFilename());
                $img_name = $web_path.$img->getClientFilename();
            }
            else {
                $img_name = "";
            }

            $postEntity = $this->getBlogManager()->getBlogEntity();

            $post = new $postEntity();
            $post->title = $formData["title"];
            $post->name = $this->changeChars($post->title);
            $post->content = $formData["content"];
            $post->description = $formData["description"];
            $post->keywords = $formData["keywords"];
            $post->tags = json_encode(explode(",", $formData["tags"]));
            $post->status = $formData["status"];
            $post->image = $img_name;
            $post->lang = "de";
            $post->save();

            $seoEntity = $this->getSeoManager()->getSeoEntity();

            $seo = new $seoEntity();
            $seo->path = $this->getRouter()->getUrl("blog_post", ["post"=>$post->name]);
            $seo->title = $formData["meta_title"];
            $seo->keywords = json_encode(explode(",", $formData["meta_tags"]));
            $seo->description = $formData["meta_description"];
            $seo->canonical = "";
            $seo->image = "";
            $seo->video = "";
            $seo->save();

            $this->getView()->setVariables([
                'success'=>true,
                'redirect'=>$this->getRouter()->getUrl('dashboard_blog_post_edit', ['postId' => $post->id])
            ]);
            return $this->getView()->renderJson($response);
        }
        else {
            $this->getView()->setVariables([
                'seo'=>[
                    'title'=>'Neuer Beitrag',
                ],
                'breadcrumbs'=>[
                    'Dashboard'=>['dashboard_home'],
                    'Blog'=>['dashboard_blog_list'],
                    'Neuer Beitrag' => ''
                ],
            ]);
            return $this->getView()->render($response, 'blog/add');
        }
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
    public function postEdit(Request $request, Response $response): Response {
        if (!$this->getAuth()->getStatus()){
            return $response->withRedirect($this->getUserRouter()->getUrl('dashboard_login'));
        }

        $postId = $request->getAttribute('postId');
        $post = $this->getBlogManager()->getBlogEntity()::find($postId);
        try {
            $seoMeta = $this->getSeoManager()
                ->getSeoEntity()::where(
                    'path',
                    '=',
                    $this->getRouter()->getUrl("blog_post", ["post" => $post->name])
                )
                ->first();
        } catch (DependencyException|NotFoundException|NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            echo $e->getMessage();
        }
        $formData = $request->getParsedBody();

        if (!empty($formData)){
            $dir = WEB_ROOT_DIR."/".$this->upload."/".date("Y")."/".date("m")."/";
            $web_path = "/".$this->upload."/".date("Y")."/".date("m")."/";

            if (isset($request->getUploadedFiles()["file_0"]) && !empty($request->getUploadedFiles()["file_0"])){
                /** @var UploadedFile $img*/
                $img = $request->getUploadedFiles()["file_0"];
                @mkdir(WEB_ROOT_DIR."/".$this->upload."/".date("Y"));
                @mkdir(WEB_ROOT_DIR."/".$this->upload."/".date("Y")."/".date("m"));
                $img->moveTo($dir.$img->getClientFilename());
                $img_name = $web_path.$img->getClientFilename();
            }
            else {
                $img_name = $post->image;
            }

            $post->title = $formData["title"];
            $post->name = $this->changeChars($post->title);
            $post->content = $formData["content"];
            $post->description = $formData["description"];
            $post->keywords = $formData["keywords"];
            $post->tags = json_encode(explode(",", $formData["tags"]));
            $post->status = $formData["status"];
            $post->image = $img_name;
            $post->lang = "de";
            $post->save();

            $seoEntity = $this->getSeoManager()->getSeoEntity();
            if (is_null($seoMeta)) {
                $seoMeta = new $seoEntity();
            }

            $seoMeta->path = $this->getRouter()->getUrl("blog_post", ["post"=>$post->name]);
            $seoMeta->title = $formData["meta_title"];
            $seoMeta->keywords = json_encode(explode(",", $formData["meta_tags"]));
            $seoMeta->description = $formData["meta_description"];
            $seoMeta->canonical = "";
            $seoMeta->image = "";
            $seoMeta->video = "";
            $seoMeta->save();

            $this->getView()->setVariables([
                'success'=>true,
                'redirect'=>$this->getUserRouter()->getUrl('dashboard_blog_post_edit', ['postId' => $post->id])
            ]);
            return $this->getView()->renderJson($response);
        }

        $post->tags = implode(",", json_decode($post->tags, true));

        $this->getView()->setVariables([
            'seo'=>[
                'title'=>'Blog',
            ],
            'breadcrumbs'=>[
                'Dashboard'=>['dashboard_home'],
                'Blog'=>['dashboard_blog_list'],
                $post->title => ''
            ],
            'post'=>$post,
            'meta'=>$seoMeta
        ]);
        return $this->getView()->render($response, 'blog/edit');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function postDelete(Request $request, Response $response): Response {
        $postId = $request->getAttribute('postId');
        try {
            $this->getBlogManager()->getBlogEntity()::where('id', '=', $postId)->delete();
            $this->getView()->setVariables([
                'success'=>true
            ]);
        } catch (Exception $e){
            $this->getView()->setVariables([
                'success' => false,
                'errorMessage' => $e->getMessage()
            ]);
        }
        return $this->getView()->renderJson($response);
    }
}
