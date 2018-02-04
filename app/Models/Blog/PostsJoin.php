<?php

namespace rdks\app\models\Blog;
 
use rdks\core\libs\ORM\Model;
 
class PostsJoin extends Model {
     
    public function __construct(\mysqli $mysqli){
 
        $this
        ->join('p', Posts::CLASS)
        ->join('a', Authors::CLASS, ['p.id_author' => 'a.id_author']) 
        ->join('c', CategoryPost::CLASS, ['p.id_category' => 'c.id_category']);
 
        parent::__construct($mysqli);
 
    }

    public function getPostsByCategoryId($id_category, $page = 1, $limit = 10, $active = true) {
        $condition = [
            'p.id_category' => $id_category, 
            'a.active'      => 1
        ];

        if($active){
            $condition['p.active'] = 1;
            $condition['c.active'] = 1;
        }

        $orderBy = ['p.id_post' => "DESC"]; // ASC | DESC
        $fields = [
            PostsJoin::field("p.id_post", "postId"), // Alias
            PostsJoin::field("p.title", "title"), // Alias
            PostsJoin::field("p.body", "post"),  // Alias
            PostsJoin::field("p.active", "active"),  // Alias
            PostsJoin::field("a.nickname", "author"),  // Alias
            PostsJoin::field("c.title", "category"),  // Alias
            PostsJoin::field("c.id_category")
        ];
          
        return $this->pagination($condition, $orderBy, $page, $limit, $fields);
    }

    public function getAllPosts($page = 1, $limit = 10) {
        $condition = [
            'p.active'      => 1,
            'a.active'      => 1,
            'c.active'      => 1
        ];
        $orderBy = ['p.id_post' => "DESC"]; // ASC | DESC
        $fields = [
            PostsJoin::field("p.id_post", "postId"), // Alias
            PostsJoin::field("p.title", "title"), // Alias
            PostsJoin::field("p.body", "post"),  // Alias
            PostsJoin::field("a.nickname", "author"),  // Alias
            PostsJoin::field("c.title", "category"),  // Alias
            PostsJoin::field("c.id_category")
        ];
          
        return $this->pagination($condition, $orderBy, $page, $limit, $fields);
    }

    public function getPostById($id, $active = true){

        $condition = [
            'p.id_post'     => $id, 
            'a.active'      => 1,
            'c.active'      => 1
        ];

        if($active){
            $condition['p.active'] = 1;
        }

        $fields = [
            PostsJoin::field("p.id_post", "postId"), // Alias
            PostsJoin::field("p.title", "title"), // Alias
            PostsJoin::field("p.body", "post"),  // Alias
            PostsJoin::field("p.active", "active"),  // Alias
            PostsJoin::field("a.nickname", "author"),  // Alias
            PostsJoin::field("c.title", "category"),  // Alias
            PostsJoin::field("c.id_category")
        ];

        // Since this is a join, Let's filter by post ID from 'Posts' Table
        // And as a result We'll get at least ONE row.
        return $this->filter($condition, $fields);
    }

}