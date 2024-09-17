<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\Blog\Manager;

use DI\DependencyException;
use DI\NotFoundException;
use Modules\Blog\BlogTrait;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Slim\Http\ServerRequest as Request;

class BlogModel {

    use BlogTrait;

    /**
     * @var array
     */
    public array $prevPage = [];

    /**
     * @var array
     */
    public array $nextPage = [];

    protected string $sessionManager = "Session\Manager";

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getLang(): string {
        $lang = 'de';
        if (
            $this->getContainer()->has($this->sessionManager)&&
            $this->getContainer()->get($this->sessionManager)->has('lang')
        ){
            $lang=$this->getContainer()->get($this->sessionManager)->get('lang');
        }
        else {
            $config = $this->getContainer()->get('config')->getSetting('lang');
            if (!empty($config) && isset($config['default'])){
                $lang=$config['default'];
            }
        }
        return $lang;
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws DependencyException
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getList(Request $request): mixed {
        $page=isset($request->getQueryParams()['page'])?$request->getQueryParams()['page']:1;

        $tag_path = '';
        if (!empty($request->getAttribute('tag'))) {
            $tag=urlencode($request->getAttribute('tag'));
            $list=$this->getBlogManager()->getBlogEntity()::where([
                ['status', '=', 'publish'],
                ['tags', 'like', '%'.urldecode($tag).'%'],
                ['lang', '=', $this->getLang()]
            ])->orderBy('id', 'desc')->paginate(10, ['*'], 'page', $page);
            $tag_path = '/'.urldecode($tag);
        }
        else {
            $list = $this->getBlogManager()->getBlogEntity()::where([
                ['status', '=', 'publish'],
                ['lang', '=', $this->getLang()]
            ])->orderBy('created_at', 'desc')->paginate(10, ['*'], 'page', $page);
        }
        $list->setPath($this->getRouter()->getUrl('blog_list').$tag_path);

        $this->setPrevPage($list, $tag_path);

        $this->setNextPage($list, $tag_path);

        foreach ($list as $post){
            unset($post->content);
        }
        return $list;
    }

    /**
     * @param $list
     * @param string $tag_path
     * @return void
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     */
    protected function setPrevPage($list, string $tag_path = ""): void {
        $this->prevPage=[];
        if ($list->currentPage()!==1){
            for($i=$list->currentPage()-1; $i>=$list->currentPage()-2; $i--){
                if ($i!==0) {
                    $this->prevPage[$i] = $this->getRouter()->getUrl('blog_list').$tag_path.'?page='.$i;
                }
            }
        }
        asort($this->prevPage);
    }

    /**
     * @param $list
     * @param string $tag_path
     * @return void
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     */
    protected function setNextPage($list, string $tag_path = ""): void {
        $this->nextPage=[];
        if ($list->currentPage()!==$list->lastPage()){
            for($i=$list->currentPage()+1; $i<=$list->currentPage()+2; $i++){
                if ($i<=$list->lastPage()) {
                    $this->nextPage[$i] = $this->getRouter()->getUrl('blog_list').$tag_path.'?page='.$i;
                }
            }
        }
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getPost(Request $request): mixed {
        $name=$request->getAttribute('post');
        return $this->getBlogManager()->getBlogEntity()::query()->where([
            ['name', '=', $name],
            ['lang', '=', $this->getLang()]
        ])->first();
    }

    /**
     * @return array
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getTagsListe(): array {
        $tags_liste = $this->getBlogManager()->getBlogEntity()::select('tags')->where([
            ['status', '=', 'publish'],
            ['lang', '=', $this->getLang()]
        ])->get();
        $liste=[];
        foreach ($tags_liste as $tags){
            $tmp_tags=json_decode($tags->tags, true);
            foreach ($tmp_tags as $tag){
                if (isset($liste[$tag])){
                    $liste[$tag]+=1;
                }
                else {
                    $liste[$tag]=1;
                }
            }
        }
        arsort($liste);
        return $liste;
    }

    /**
     * @param string $tags
     * @return string
     */
    public function getTagListeFromPost(string $tags): string {
        $tag_liste='';
        foreach(json_decode($tags, true) as $tag){
            if (empty($tag_liste)) {
                $tag_liste = $tag;
            }
            else {
                $tag_liste .= ' ' . $tag;
            }
        }
        return $tag_liste;
    }

    /**
     * @param int $count
     * @return mixed
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getLastPost(int $count = 5): mixed {
        $posts = $this->getBlogManager()->getBlogEntity()::where([
            ['status', '=', 'publish'],
            ['lang', '=', $this->getLang()]
        ])->orderBy('id', 'desc')->limit($count)->get();
        foreach ($posts as $post){
            unset($post->content);
        }
        return $posts;
    }

    /**
     * @param string $content
     * @param int $word_count
     * @return string
     */
    public function getDescription(string $content, int $word_count = 20): string {
        $tmp_str=explode(' ', str_replace(["&nbsp;", "\n"], " ",strip_tags($content)));
        $str="";
        $tmp_word_count = 1;
        foreach ($tmp_str as $value) {
            if ($tmp_word_count===$word_count) {
                break;
            }
            elseif (!empty($value)){
                $str.=$value." ";
                $tmp_word_count++;
            }
        }
        return $str."...";
    }

}
