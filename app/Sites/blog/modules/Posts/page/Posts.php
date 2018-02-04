<?php

namespace rdks\app\sites\blog\modules\Posts\page;

use rdks\core\page\Page;
use rdks\core\page\View;
use rdks\core\framework\URL;
use rdks\app\sites\blog\modules\Posts\helper\Posts as PostsHelper;

class Posts extends Page {

    var $page = 1; // GET Param

    private $_helper;

    public function __construct(array $settings, View $view){
        parent::__construct($settings, $view);
         
        // You will be able to call all the helper's PUBLIC methods
        $this->_helper = PostsHelper::init(); // Initialize helper
    }

    public function listing(){
 
        $this->view->assets->scriptsInline([
            "posts",
            "pager"
        ]);
 
        $this->view->assets->scriptsOnReady([
            "posts.ready",
            "pager.ready",
            "pager.focus.ready"
        ]);
 
        $data = $this->_helper->getAllPosts($this->page, 3);
 
        // Pass variables to view <KEY>, <VALUE>
        $this->view->data("data", $data);
        $this->view->data("totalRows", $this->_helper->getTotalPosts());
        $this->view->data("categories", $this->_helper->getCategories());
        $this->view->data("categoryId", 0);
        $this->view->data("latestTag", true);

        $this->view->tpl("categoryRedirect", PostsHelper::getCategoryLink(''));
        $this->view->tpl("totalPages", $data->getTotalPages());
        $this->view->tpl("pageRedirect", URL::build("", ['page' => ""]));

        $this->view->page($this->page);
         
        // Load Layout
        $this->view->layout("content-sidebar", [
            'CONTENT' => [
                $this->view->setView("listing") // listing.phtml
            ],
            'SIDEBAR' => [
                $this->view->setView("category") // category.phtml
            ]
        ]); 
  
        return $this->view->output();
 
    }

    public function byPostId(){

        $postId = $this->getUrlParam("postId");

        if($postId == 0){
            // Throw a 404 page
            $this->pageNotFound();
        }

        $data = $this->_helper->getPostById($postId);

        // Make sure row exists in table records
        // Else, throw a 404 page
        $this->hasData($data->rows());

        $post = $data->fetch(); // Get data (array)

        if($post['active'] == 0){
            $this->pageNotFound();
        }

        $this->view->title($post['title'], true);

        // Pass variables to view <KEY>, <VALUE>
        $this->view->data("post", $post);

        // Load View
        $this->view->load("post"); // post.phtml
 
        return $this->view->output();

    }

    public function byCategoryId(){

        $categoryId = $this->getUrlParam("categoryId", 0);

        if($categoryId == 0){
            $this->redirect(PostsHelper::getCategoryLink(1));
        }

        $data = $this->_helper->getPostsByCategoryId($categoryId, $this->page, 10, true);

        $this->view->assets->scriptsInline([
            "posts",
            "pager"
        ]);

        $this->view->assets->scriptsOnReady([
            "pager.ready",
            "pager.focus.ready"
        ]);

        $categoryLink = PostsHelper::getCategoryLink($categoryId);

        // Pass variables to view <KEY>, <VALUE>
        $this->view->data("data", $data);
        $this->view->data("totalRows", $this->_helper->getTotalPostsByCategoryId($categoryId));
        $this->view->data("categories", $this->_helper->getCategories());
        $this->view->data("categoryId", $categoryId);
        $this->view->data("categoryLink", $categoryLink);
        $this->view->data("categoryTitle", $this->_helper->getCategoryTitle($categoryId));
        $this->view->data("latestTag", false);

        $this->view->tpl("categoryRedirect", PostsHelper::getCategoryLink(''));
        $this->view->tpl("totalPages", $data->getTotalPages());
        $this->view->tpl("pageRedirect", URL::build($categoryLink, ['page' => ""])); 

        $this->view->page($this->page);

        if(!$data->rows()){
            $this->pageNotFound();
        }

        // Load Layout
        $this->view->layout("content-sidebar", [
            'CONTENT' => [
                $this->view->setView("listing")
            ],
            'SIDEBAR' => [
                $this->view->setView("category")
            ]
        ]); 
 
        return $this->view->output();
    }

}