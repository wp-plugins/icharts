<?php
class wv35v_mu extends bv35v_base {
	public function blogs_current_user_can($capabilities) {
		$blogs = array ();
		$capabilities = ( array ) $capabilities;
		foreach ( get_blogs_of_user ( $this->user ()->ID ) as $blog ) {
			//switch_to_blog ( $blog->userblog_id);
			$user_can = true;
			foreach ( $capabilities as $capability ) {
				if (! current_user_can_for_blog ( $blog->userblog_id, $capability )) {
					$user_can = false;
					break;
				}
			}
			if ($user_can) {
				$blogs [$blog->blogname] = $blog;
			}
		
		//restore_current_blog ();
		}
		return $blogs;
	}
	function is_plugin_active_on_blog($blog_id, $plugin) {
		return in_array ( $plugin, ( array ) get_blog_option ( $blog_id, 'active_plugins' ) ) || is_plugin_active_for_network ( $plugin );
	}
	public function copy_post($dest_name, $post_id) {
		$blogs = $this->mu ()->blogs_current_user_can ( 'administrator' );
		$dest_id = $blogs [$dest_name]->userblog_id;
		return $this->_copy_post ( $dest_id, $post_id );
	}
	public function _copy_post($dest_id, $post_id, $new_parent = 0) {
		$post = get_post ( $post_id, ARRAY_A );
		$post ['post_parent'] = $new_parent;
		unset ( $post ['ID'] );
		$role = $this->user ( $post ['post_author'] )->get_role ();
		add_user_to_blog ( $dest_id, $post ['post_author'], $role );
		switch_to_blog ( $dest_id );
		$new_post_id = wp_insert_post ( $post );
		restore_current_blog ();
		$ids = $this->posts ()->child_ids ( $post_id );
		foreach ( $ids as $id ) {
			$this->_copy_post ( $dest_id, $id, $new_post_id );
		}
		$ids = $this->posts ()->meta_ids ( $post_id );
		foreach ( $ids as $id ) {
			$this->_copy_postmeta ( $dest_id, $id, $new_post_id );
		}
		$ids = $this->posts ()->comment_ids ( $post_id );
		foreach ( $ids as $id ) {
			$this->_copy_comments ( $dest_id, $id, $new_post_id );
		}
	}
	private function _copy_postmeta($dest_id, $meta_id, $new_post_id) {
		$meta = $this->posts ()->get_post_meta_by_id ( $meta_id );
		switch_to_blog ( $dest_id );
		add_post_meta ( $new_post_id, $meta ['meta_key'], $meta ['meta_value'] );
		restore_current_blog ();
	}
	private function _copy_commentmeta($dest_id, $meta_id, $new_comment_id) {
		$meta = $this->comments ()->get_comment_meta_by_id ( $meta_id );
		switch_to_blog ( $dest_id );
		add_comment_meta ( $new_comment_id, $meta ['meta_key'], $meta ['meta_value'] );
		restore_current_blog ();
	}
	private function _copy_comments($dest_id, $comment_id, $new_post_id, $new_comment_parent = 0) {
		$data = get_comment ( $comment_id, ARRAY_A );
		unset ( $data ['comment_ID'] );
		$data ['comment_post_ID'] = $new_post_id;
		$data ['comment_parent'] = $new_comment_parent;
		switch_to_blog ( $dest_id );
		$new_comment_id = wp_insert_comment ( $data );
		restore_current_blog ();
		$ids = $this->comments ()->child_ids ( $comment_id );
		foreach ( $ids as $id ) {
			$this->_copy_comments ( $dest_id, $id, $new_post_id, $new_comment_id );
		}
		$ids = $this->comments ()->meta_ids ( $comment_id );
		foreach ( $ids as $id ) {
			$this->_copy_commentmeta ( $dest_id, $id, $new_comment_id );
		}
	}
}	