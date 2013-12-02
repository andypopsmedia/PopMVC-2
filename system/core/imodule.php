<?php
interface iModule
{
	public function view($block_id);
	public function add_new($block_tag);
	public function save_new($block_id, $post);
	public function edit_existing($block_id);
	public function save_existing($block_id, $post);
	public function delete($block_id);
	public function validate($post);
}