<?php

namespace rdks\app\sites\admin\modules\Posts\helper;
 
use rdks\core\framework\Core;
use rdks\core\framework\URL;
use rdks\core\page\HelperPage;
use rdks\app\models\Blog\Posts as PostsTable;
use rdks\app\models\Blog\PostsJoin;
use rdks\app\models\Blog\CategoryPost;
 
class Posts extends HelperPage {
 
	static function getPostsLink($path = ""){
		return "/posts{$path}";
	}

	static function getPostLink($id){
		return "/post/id/{$id}";
	}

	static function getPostsCategoryLink($id = ""){
		return self::getCategoriesLink("/id/{$id}");
	}

	static function getEditCategoryLink($id){
		return self::getCategoriesLink("/edit/id/{$id}");	
	}

	static function getEditPostLink($id){
		return self::getPostsLink("/edit/id/{$id}");
	}	

	static function getAddPostLink($id){
		return self::getPostsLink("/add/category/id/{$id}");
	}

	static function getCategoriesLink($path = ""){
		return self::getPostsLink("/categories{$path}");
	}	

	static function getCategoryLink($id){
		return self::getPostsLink("/category/id/{$id}");
	}

	static function getPostPublicLink($id){
		return URL::goToURL("blog.local", "blog") . self::getPostLink($id);
	}

	static function stGetTotalPostsByCategoryId($id, $active = true){
		$db = Core::db(RDKS_ERRORS);
		return PostsTable::open($db)->getTotalPostsByCategoryId($id, $active);
	}

	public function getTotalPosts(){
		$db = $this->db();
		return PostsTable::open($db)->getTotalPosts();
	}

	public function getTotalCategories(){
		$db = $this->db();
		return CategoryPost::open($db)->getTableTotalRows();
	}

	public function getCategories($active = true){
		$db = $this->db();
		return CategoryPost::open($db)->getAll($active);
	}

	public function getPagedCategories($page = 1, $limit = 10){
		$db = $this->db();
		return CategoryPost::open($db)->getList($page, $limit);
	}

	public function getCategoryTitle($id){
		$db = $this->db();
		$category = CategoryPost::open($db)->load($id);
		return $category->getTitle();
	}

	public function getCategoryById($id){
		$db = $this->db();
		// We use 'row' instead of 'load' because we need to return an array instead of an object
		return CategoryPost::open($db)->row($id);
	}

	public function checkCategoryId($id){
		$category = $this->getCategoryById($id);
		return count($category);
	}

	public function setCategoryVisibility($id, $value){
		$db = $this->db();
		return CategoryPost::open($db)->update($id, ['active' => $value]);
	}

	public function insertCategory($data){
		$db = $this->db();
		$data['created_date'] = CategoryPost::NOW;
		$data['updated_date'] = CategoryPost::NOW;

		return CategoryPost::open($db)->insert($data);
	}

	public function updateCategoryById($id, $data){
		$db = $this->db();
		$data['updated_date'] = CategoryPost::NOW;

		return CategoryPost::open($db)->update($id, $data);
	}

	public function getAllPosts($page = 1, $limit = 10){
		$db = $this->db();
		return PostsJoin::open($db)->getAllPosts($page, $limit);
	}

	public function getLatestPost(){
		return $this->getAllPosts(1,1);
	}

	public function getPostsByCategoryId($id_category, $page = 1, $limit = 10, $active = true){
		$db = $this->db();
		return PostsJoin::open($db)->getPostsByCategoryId($id_category, $page, $limit, $active);
	}

	public function getPostById($id){
		$db = $this->db();
		return PostsJoin::open($db)->getPostById($id, false);
	}

	public function getTotalPostsByCategoryId($id_category, $active = true){
		$db = $this->db();
		return PostsTable::open($db)->getTotalPostsByCategoryId($id_category, $active);
	}

	public function setPostVisibility($id, $value){
		$db = $this->db();
		return PostsTable::open($db)->update($id, ['active' => $value]);
	}

	public function insertPost($data){
		$db = $this->db();
		$data['id_author'] = 1;
		$data['created_date'] = PostsTable::NOW;
		$data['updated_date'] = PostsTable::NOW;

		return PostsTable::open($db)->insert($data);
	}

	public function updatePostById($id, $data){
		$db = $this->db();
		$data['updated_date'] = PostsTable::NOW;

		return PostsTable::open($db)->update($id, $data);
	}

}