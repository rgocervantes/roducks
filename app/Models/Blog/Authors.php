<?php

namespace rdks\app\models\Blog;
 
use rdks\core\libs\ORM\Model;
 
class Authors extends Model {
 
    var $id = "id_author";
    var $fields = [
        'first_name'         => Model::TYPE_VARCHAR,
        'last_name'          => Model::TYPE_VARCHAR,
        'nickname'           => Model::TYPE_VARCHAR,
        'active'             => Model::TYPE_BOOL,
        'created_date'       => Model::TYPE_DATETIME,
        'updated_date'       => Model::TYPE_DATETIME
    ];
 
}