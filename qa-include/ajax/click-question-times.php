<?php
/*
	Question2Answer by Gideon Greenspan and contributors
	http://www.question2answer.org/

	Description: Server-side response to Ajax create answer requests


	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	More about this license: http://www.question2answer.org/license.php
*/

// add 1 on field clicktimes when clicking a question title
require_once QA_INCLUDE_DIR . 'app/users.php';

$userid = qa_get_logged_in_userid();


$postid = qa_post_text('postid');

$result = qa_db_read_one_assoc(qa_db_query_sub(
		'SELECT clicktimes FROM ^posts WHERE postid=#',
		$postid
	), true);

$currentClicktimes = $result['clicktimes'];
$newClicktimes = $currentClicktimes + 1;

qa_db_query_sub(
	'UPDATE ^posts SET clicktimes=$ WHERE postid=#',
	$newClicktimes, $postid
);
