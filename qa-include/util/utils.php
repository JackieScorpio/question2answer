<?php
/*
	Question2Answer by Gideon Greenspan and contributors
	http://www.question2answer.org/

	File: qa-include/util/image.php
	Description: Some useful image-related functions (using GD)


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



/**
 * This function is to record total online time for a user
 * almost every time a user clicks a button on the website, we give him a new page, so we can add a trigger in 'qa-include/qa-page.php'
 * every time a user sends a request to server(by clicking some button), we can know it in 'qa-include/qa-ajax.php'
 * so this function will be called in 'qa-include/qa-page.php' and 'qa-include/qa-ajax.php'
 * @return array
 */
function qa_record_user_online_time()
{
	require_once QA_INCLUDE_DIR . 'app/users.php';
	
	// get loggedin user's id
	$userid = qa_get_logged_in_userid();

	// if we have users logged in, we can calculate their online time then
	if(isset($userid)) {
		// get last action time and total action time(total online time)
		$result = qa_db_read_one_assoc(qa_db_query_sub(
			'SELECT lastactiontime, totalactiontime FROM ^users WHERE userid=#',
			$userid
		), true);
	
		$lastactiontime = $result['lastactiontime'];
		$totalactiontime = $result['totalactiontime'];
		$currentactiontime = time();

		if((int)$lastactiontime > (int)$currentactiontime) {
			// (int)null == 0
			// lastactiontime must not be bigger than currentactiontime, time's set wrong
			return [];
		}

		$newLastactiontime;
		$newTotalactiontime = $totalactiontime;

		if(isset($lastactiontime)) {
			$idleDuration = 300; // after more than 5 minutes, the user is deemed to have left
			$diff = (int)$currentactiontime - (int)$lastactiontime;

			$newLastactiontime = $currentactiontime;

			if ($diff <= $idleDuration) {
				$newTotalactiontime = (int)$totalactiontime + $diff;
			}
		} else {
			// lastactiontime is null
			$newLastactiontime = time();
		}
		
		qa_db_query_sub(
			'UPDATE ^users SET lastactiontime=$, totalactiontime=$ WHERE userid=#',
			(string)$newLastactiontime, (string)$newTotalactiontime, $userid
		);
	}

	return [];
}
