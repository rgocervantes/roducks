<?php

namespace rdks\app\models\Blog;
 
use rdks\core\libs\ORM\Model;
 
class Posts extends Model {
 
    var $id = "id_post";
    var $fields = [
        'id_author'      => Model::TYPE_INTEGER,
        'id_category'    => Model::TYPE_INTEGER,
        'title'          => Model::TYPE_VARCHAR,
        'body'           => Model::TYPE_VARCHAR,
        'active'         => Model::TYPE_BOOL,
        'created_date'   => Model::TYPE_DATETIME,
        'updated_date'   => Model::TYPE_DATETIME
    ];
 
    public function getList($id_category, $page, $limit) {
        $condition = ['id_category' => $id_category, 'active' => 1];
        $orderBy = ['id_post' => "DESC"]; // ASC | DESC
        $fields = [
            Posts::field("title"),
            Posts::field("body")
        ];
          
        return $this->pagination($condition, $orderBy, $page, $limit, $fields);
    }

    public function getTotalPosts(){
        return $this->count('id_post', ['active' => 1]);
    }

    public function getTotalPostsByCategoryId($id_category, $active = true){
        $condition = ['id_category' => $id_category];

        if($active){
            $condition['active'] = 1;
        }

        return $this->count('id_post', $condition);
    }

}