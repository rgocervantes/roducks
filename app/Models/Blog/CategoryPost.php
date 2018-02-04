<?php

namespace rdks\app\models\Blog;
 
use rdks\core\libs\ORM\Model;
 
class CategoryPost extends Model {
 
    var $id = "id_category";
    var $fields = [
        'title'              => Model::TYPE_VARCHAR,
        'active'             => Model::TYPE_BOOL,
        'created_date'       => Model::TYPE_DATETIME,
        'updated_date'       => Model::TYPE_DATETIME
    ];
 
    public function getAll($active = true){

        if($active){
            return $this->filter([
                'condition' => ['active' => 1],
                'orderby' => ['id_category' => "DESC"]
            ]);
        }

        return $this->select();
    }

    public function getList($page = 1, $limit = 10){
        return $this->pagination([], ['id_category' => "DESC"], $page, $limit);
    }

}