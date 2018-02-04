<?php

namespace rdks\app\sites\admin\modules\Posts\page;
 
use rdks\core\page\AdminPage;
use rdks\core\page\View;
use rdks\core\framework\URL;
use rdks\app\sites\admin\modules\Posts\helper\Posts as PostsHelper;
 
class Posts extends AdminPage {
 
	var $page = 1;

    private $_helper;
 
    public function __construct(array $settings, View $view){
        parent::__construct($settings, $view);
          
        // You will be able to call all the helper's PUBLIC methods
        $this->_helper = PostsHelper::init(); // Initialize helper
    }

    private function _setCategories($name, $active = true){
        $categories = $this->_helper->getCategories($active);
        
        $this->view->data($name, $categories);

        return $categories;
    }

    private function _form(){

        $this->view->assets->scriptsInline([
            "form"
        ]);

        $this->view->data("url", PostsHelper::getPostsLink());
    }

    private function _formPost(){
        $this->_setCategories("categories");
        $this->_form();
    }

    public function byCategoryId(){

        $categoryId = $this->getUrlParam("categoryId", 0);

        if($categoryId == 0){
            $this->pageNotFound();
        }

        $data = $this->_helper->getPostsByCategoryId($categoryId,$this->page, 10, false);

        $this->hasData($this->_helper->checkCategoryId($categoryId));

        $this->view->title("Category: " . $this->_helper->getCategoryTitle($categoryId), true, "title-posts");

        $this->view->assets->scriptsInline([
            "popover",
            "grid",
            "pager"
        ]);
 
        $this->view->assets->scriptsOnReady([
            "grid.ready",
            "pager.ready",
            "pager.focus.ready"
        ]);

        $this->view->data("data", $data);
        $this->view->tpl("totals", $this->_helper->getTotalPostsByCategoryId($categoryId, false));
        $this->view->tpl("totalPages", $data->getTotalPages());
        $this->view->tpl("pageRedirect", URL::build(PostsHelper::getPostsCategoryLink($categoryId), ['page' => ""]));
        $this->view->tpl("btnCreateUrl", PostsHelper::getAddPostLink($categoryId));
        $this->view->page($this->page);

        $this->view->layout("sidebar-content",[
            'CONTENT' => [
                $this->view->setTemplate("go-back"),
                $this->view->setTemplate("controllers"),                
                $this->view->setView("posts")
            ],
            'SIDEBAR' => [
                $this->view->setTemplate("sidebar-left")
            ],              
            'SIDEBAR-CHILD-LEFT' => [
                $this->view->setTemplate("sidebar-dashboard")
            ]                       
        ]);

        return $this->view->output();
    }

    public function addPost(){

        $categoryId = $this->getUrlParam("categoryId", 0);

        if($categoryId == 0){
            $this->pageNotFound();
        }

        $this->hasData($this->_helper->checkCategoryId($categoryId));

        $category = $this->_helper->getCategoryTitle($categoryId);

        $row = [
            'active' => 1,
            'title' => "", 
            'post' => "",
            'postId' => 0,
            'id_category' => $categoryId
        ];

        $this->view->title("New Post - ({$category})");
        $this->view->data("edit", false);
        $this->view->data("action", "insert/post");
        $this->view->data("row", $row);

        $this->_formPost();

        $this->view->load("post-form");

        return $this->view->output();
    }

    public function editPost(){

        $postId = $this->getUrlParam("postId", 0);

        $data = $this->_helper->getPostById($postId);

        $this->hasData($data->rows());

        $row = $data->fetch();

        $this->view->title("Edit Post");
        $this->view->data("edit", true);
        $this->view->data("action", "update/post/{$postId}");
        $this->view->data("row", $row);

        $this->_formPost();

        $this->view->load("post-form");

        return $this->view->output();
    }

    public function listCategories(){

        $data = $this->_helper->getPagedCategories($this->page);

        $this->view->assets->scriptsInline([
            "popover",
            "grid",
            "pager"
        ]);
 
        $this->view->assets->scriptsOnReady([
            "grid.ready",
            "pager.ready",
            "pager.focus.ready"
        ]);

        $this->view->title("Categories", true, "title-posts");

        $this->view->data("data", $data);
        $this->view->tpl("totals", $this->_helper->getTotalCategories());
        $this->view->tpl("totalPages", $data->getTotalPages());
        $this->view->tpl("pageRedirect", URL::build(PostsHelper::getCategoriesLink(), ['page' => ""]));
        $this->view->tpl("btnCreateUrl", PostsHelper::getCategoriesLink('/add'));
        $this->view->page($this->page);

        $this->view->layout("sidebar-content",[
            'CONTENT' => [
                $this->view->setTemplate("go-back"),
                $this->view->setTemplate("controllers"),                
                $this->view->setView("categories")
            ],
            'SIDEBAR' => [
                $this->view->setTemplate("sidebar-left")
            ],              
            'SIDEBAR-CHILD-LEFT' => [
                $this->view->setTemplate("sidebar-dashboard")
            ]                       
        ]);

        return $this->view->output();
    }

    public function addCategory(){
        
        $row = [
            'active' => 1,
            'title' => ""
        ];

        $this->view->title("New Category");
        $this->view->data("edit", false);
        $this->view->data("action", "insert/category");
        $this->view->data("row", $row);

        $this->_form();

        $this->view->load("category-form");

        return $this->view->output();

    }

    public function editCategory(){

        $categoryId = $this->getUrlParam("categoryId", 0);

        if($categoryId == 0){
            $this->pageNotFound();
        }

        $this->hasData($this->_helper->checkCategoryId($categoryId));

        $row = $this->_helper->getCategoryById($categoryId);

        $this->view->title("Edit Category");
        $this->view->data("edit", true);
        $this->view->data("action", "update/category/{$categoryId}");
        $this->view->data("row", $row);

        $this->_form();

        $this->view->load("category-form");

        return $this->view->output();

    }

}