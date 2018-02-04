<?php

namespace rdks\app\sites\admin\modules\Posts\json;
 
use rdks\core\page\_JSON;
use rdks\app\sites\admin\modules\Posts\helper\Posts as PostsHelper;
 
class Posts extends _JSON {

	protected $_dispatchUrl = true;
 
    private $_helper;
 
    public function __construct(array $settings){
        parent::__construct($settings);
          
        // You will be able to call all the helper's PUBLIC methods
        $this->_helper = PostsHelper::init(); // Initialize helper
    }

    public function acents(){


        $acents = ['á','é','í','ó','ú','ñ','Á','É','Í','Ó','Ú','Ñ'];


        $this->data("acents", $acents);


        parent::output();

    }

    private function _getPostFormData(){
        $data = [
            'active' => $this->post->checkbox("active", 0),
            'title' => $this->post->text("title"),
            'body' => $this->post->textarea("body"),
            'id_category' => $this->post->select("id_category")
        ];

        return $data;
    }

    private function _getCategoryFormData(){
        $data = [
            'active' => $this->post->checkbox("active", 0),
            'title' => $this->post->text("title")
        ];

        return $data;
    }

    public function visibility($type){

        $this->post->required();

        $id = $this->post->param("id", 0);
        $value = $this->post->param("value", 1);

        if($id == 0) {
            $this->setError(1, "Something went wrong!");
        } else {
            
            switch ($type) {
                case 'post':
                    $tx = $this->_helper->setPostVisibility($id, $value);
                    break;
                case 'category':
                    $tx = $this->_helper->setCategoryVisibility($id, $value);
                    break;
                default:
                    $tx = false;
                    break;
            }

            if($tx === false){
                $this->setError(2, "Something went wrong!");
            }
        }

        $this->data("type", $type);

        parent::output();
    }

    public function insert($type){

        $this->post->required();

        switch ($type) {
            case 'post':

                $data = $this->_getPostFormData();
                $tx = $this->_helper->insertPost($data);

                break;
            
            case 'category':

                $data = $this->_getCategoryFormData();
                $tx = $this->_helper->insertCategory($data);

                break;
            default:
                $tx = false;
                break;  
        }

        if($tx === false){
            $this->setError(1, "Something went wrong!");
        }

        parent::output();
    }

    public function update($type, $id){

        $this->post->required();

        switch ($type) {
            case 'post':

                $data = $this->_getPostFormData();
                $tx = $this->_helper->updatePostById($id, $data);

                break;
            
            case 'category':

                $data = $this->_getCategoryFormData();
                $tx = $this->_helper->updateCategoryById($id, $data);

                break;
            default:
                $tx = false;
                break;  
        }

        if($tx === false){
            $this->setError(1, "Something went wrong!");
        }

    	parent::output();
    }
}