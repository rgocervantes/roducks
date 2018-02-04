<?php

namespace rdks\app\sites\blog\modules\Posts\json;

use rdks\core\page\JSON;
use rdks\app\sites\blog\modules\Posts\helper\Posts as PostsHelper;
use rdks\core\framework\Form;

class Posts extends JSON {

	var $code = ""; // GET Param
	
	protected $_dispatchUrl = true;

	private $_helper;

    public function __construct(array $settings){
        parent::__construct($settings);
         
        // You will be able to call all the helper's PUBLIC methods
        $this->_helper = PostsHelper::init(); // Initialize helper
    }

    /**
     * @example: /_json/posts/_latest
     */
    public function _latest(){

        Form::setKey($this->code);

        $data = $this->_helper->getLatestPost();

        if($data->rows()) {
            $row = $data->fetch();
            $post = [
                'id' => intval($row['postId']),
                'title' => $row['title'],
                'link' => PostsHelper::getPostLink($row['postId'])
            ];   
        }

        $this->data("post", $post);

        parent::output();
    }

    /**
     * @example: /latest-post.json
     */
    public function latest(){
        $this->disableUrlDispatcher();
        $this->_latest();
    }

}