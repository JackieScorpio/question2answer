<?php
/*
	Question2Answer by Gideon Greenspan and contributors
	http://www.question2answer.org/

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

namespace Q2A\Controllers\User;

use Q2A\Controllers\BaseController;
use Q2A\Database\DbConnection;
use Q2A\Http\Exceptions\PageNotFoundException;

class UserPosts extends BaseController
{
	protected $userid;
	protected $userhtml;

	public function __construct(DbConnection $db)
	{
		require_once QA_INCLUDE_DIR . 'db/users.php';
		require_once QA_INCLUDE_DIR . 'db/selects.php';
		require_once QA_INCLUDE_DIR . 'app/users.php';
		require_once QA_INCLUDE_DIR . 'app/format.php';

		parent::__construct($db);
	}

	/**
	 * @param string $handle
	 *
	 * @return array
	 * @throws PageNotFoundException
	 */
	public function activity($handle)
	{
		$this->userHtml($handle);

		// Find the recent activity for this user

		$loginuserid = qa_get_logged_in_userid();
		$identifier = QA_FINAL_EXTERNAL_USERS ? $this->userid : $handle;

		list($useraccount, $questions, $answerqs, $commentqs, $editqs) = qa_db_select_with_pending(
			QA_FINAL_EXTERNAL_USERS ? null : qa_db_user_account_selectspec($handle, false),
			qa_db_user_recent_qs_selectspec($loginuserid, $identifier, qa_opt_if_loaded('page_size_activity')),
			qa_db_user_recent_a_qs_selectspec($loginuserid, $identifier),
			qa_db_user_recent_c_qs_selectspec($loginuserid, $identifier),
			qa_db_user_recent_edit_qs_selectspec($loginuserid, $identifier)
		);

		if (!QA_FINAL_EXTERNAL_USERS && !is_array($useraccount)) { // check the user exists
			throw new PageNotFoundException();
		}
		// Get information on user references

		$questions = qa_any_sort_and_dedupe(array_merge($questions, $answerqs, $commentqs, $editqs));
		$questions = array_slice($questions, 0, qa_opt('page_size_activity'));
		$usershtml = qa_userids_handles_html(qa_any_get_userids_handles($questions), false);


		// Prepare content for theme

		$qa_content = qa_content_prepare(true);

		if (count($questions)) {
			$qa_content['title'] = qa_lang_html_sub('profile/recent_activity_by_x', $this->userhtml);
		} else {
			$qa_content['title'] = qa_lang_html_sub('profile/no_posts_by_x', $this->userhtml);
		}


		// Recent activity by this user

		$qa_content['q_list']['form'] = array(
			'tags' => 'method="post" action="' . qa_self_html() . '"',

			'hidden' => array(
				'code' => qa_get_form_security_code('vote'),
			),
		);

		$qa_content['q_list']['qs'] = array();

		$htmldefaults = qa_post_html_defaults('Q');
		$htmldefaults['whoview'] = false;
		$htmldefaults['voteview'] = false;
		$htmldefaults['avatarsize'] = 0;

		foreach ($questions as $question) {
			$qa_content['q_list']['qs'][] = qa_any_to_q_html_fields(
				$question,
				$loginuserid,
				qa_cookie_get(),
				$usershtml,
				null,
				array('voteview' => false) + qa_post_html_options($question, $htmldefaults)
			);
		}


		// Sub menu for navigation in user pages

		$ismyuser = isset($loginuserid) && $loginuserid == (QA_FINAL_EXTERNAL_USERS ? $this->userid : $useraccount['userid']);
		$qa_content['navigation']['sub'] = qa_user_sub_navigation($handle, 'activity', $ismyuser);


		return $qa_content;
	}

	/**
	 * @param string $handle
	 *
	 * @return array
	 * @throws PageNotFoundException
	 */
	public function questions($handle)
	{
		$this->userHtml($handle);

		$start = qa_get_start();

		// Find the questions for this user

		$loginuserid = qa_get_logged_in_userid();
		$identifier = QA_FINAL_EXTERNAL_USERS ? $this->userid : $handle;

		list($useraccount, $userpoints, $questions) = qa_db_select_with_pending(
			QA_FINAL_EXTERNAL_USERS ? null : qa_db_user_account_selectspec($handle, false),
			qa_db_user_points_selectspec($identifier),
			qa_db_user_recent_qs_selectspec($loginuserid, $identifier, qa_opt_if_loaded('page_size_qs'), $start)
		);

		if (!QA_FINAL_EXTERNAL_USERS && !is_array($useraccount)) { // check the user exists
			throw new PageNotFoundException();
		}


		// Get information on user questions

		$pagesize = qa_opt('page_size_qs');
		$count = (int)@$userpoints['qposts'];
		$questions = array_slice($questions, 0, $pagesize);
		$usershtml = qa_userids_handles_html($questions, false);


		// Prepare content for theme

		$qa_content = qa_content_prepare(true);

		if (count($questions)) {
			$qa_content['title'] = qa_lang_html_sub('profile/questions_by_x', $this->userhtml);
		} else {
			$qa_content['title'] = qa_lang_html_sub('profile/no_questions_by_x', $this->userhtml);
		}


		// Recent questions by this user

		$qa_content['q_list']['form'] = array(
			'tags' => 'method="post" action="' . qa_self_html() . '"',

			'hidden' => array(
				'code' => qa_get_form_security_code('vote'),
			),
		);

		$qa_content['q_list']['qs'] = array();

		$htmldefaults = qa_post_html_defaults('Q');
		$htmldefaults['whoview'] = false;
		$htmldefaults['avatarsize'] = 0;

		foreach ($questions as $question) {
			$qa_content['q_list']['qs'][] = qa_post_html_fields(
				$question,
				$loginuserid,
				qa_cookie_get(),
				$usershtml,
				null,
				qa_post_html_options($question, $htmldefaults)
			);
		}

		$qa_content['page_links'] = qa_html_page_links(qa_request(), $start, $pagesize, $count, qa_opt('pages_prev_next'));


		// Sub menu for navigation in user pages

		$ismyuser = isset($loginuserid) && $loginuserid == (QA_FINAL_EXTERNAL_USERS ? $this->userid : $useraccount['userid']);
		$qa_content['navigation']['sub'] = qa_user_sub_navigation($handle, 'questions', $ismyuser);


		return $qa_content;
	}

	/**
	 * @param string $handle
	 *
	 * @return array
	 * @throws PageNotFoundException
	 */
	public function answers($handle)
	{
		$this->userHtml($handle);

		$start = qa_get_start();


		// Find the questions for this user

		$loginuserid = qa_get_logged_in_userid();
		$identifier = QA_FINAL_EXTERNAL_USERS ? $this->userid : $handle;

		list($useraccount, $userpoints, $questions) = qa_db_select_with_pending(
			QA_FINAL_EXTERNAL_USERS ? null : qa_db_user_account_selectspec($handle, false),
			qa_db_user_points_selectspec($identifier),
			qa_db_user_recent_a_qs_selectspec($loginuserid, $identifier, qa_opt_if_loaded('page_size_activity'), $start)
		);

		if (!QA_FINAL_EXTERNAL_USERS && !is_array($useraccount)) { // check the user exists
			throw new PageNotFoundException();
		}


		// Get information on user questions

		$pagesize = qa_opt('page_size_activity');
		$count = (int)@$userpoints['aposts'];
		$questions = array_slice($questions, 0, $pagesize);
		$usershtml = qa_userids_handles_html($questions, false);


		// Prepare content for theme

		$qa_content = qa_content_prepare(true);

		if (count($questions)) {
			$qa_content['title'] = qa_lang_html_sub('profile/answers_by_x', $this->userhtml);
		} else {
			$qa_content['title'] = qa_lang_html_sub('profile/no_answers_by_x', $this->userhtml);
		}


		// Recent questions by this user

		$qa_content['q_list']['form'] = array(
			'tags' => 'method="post" action="' . qa_self_html() . '"',

			'hidden' => array(
				'code' => qa_get_form_security_code('vote'),
			),
		);

		$qa_content['q_list']['qs'] = array();

		$htmldefaults = qa_post_html_defaults('Q');
		$htmldefaults['whoview'] = false;
		$htmldefaults['avatarsize'] = 0;
		$htmldefaults['ovoteview'] = true;
		$htmldefaults['answersview'] = false;

		foreach ($questions as $question) {
			$options = qa_post_html_options($question, $htmldefaults);
			$options['voteview'] = qa_get_vote_view('A', false, false);

			$qa_content['q_list']['qs'][] = qa_other_to_q_html_fields($question, $loginuserid, qa_cookie_get(), $usershtml, null, $options);
		}

		$qa_content['page_links'] = qa_html_page_links(qa_request(), $start, $pagesize, $count, qa_opt('pages_prev_next'));


		// Sub menu for navigation in user pages

		$ismyuser = isset($loginuserid) && $loginuserid == (QA_FINAL_EXTERNAL_USERS ? $this->userid : $useraccount['userid']);
		$qa_content['navigation']['sub'] = qa_user_sub_navigation($handle, 'answers', $ismyuser);


		return $qa_content;
	}

	/**
	 * Return the HTML to display for the handle, and if we're using external users, determine the userid.
	 *
	 * @param string $handle
	 * @throws PageNotFoundException
	 */
	private function userHtml($handle)
	{
		if (QA_FINAL_EXTERNAL_USERS) {
			$this->userid = qa_handle_to_userid($handle);
			if (!isset($this->userid)) { // check the user exists
				throw new PageNotFoundException();
			}

			$usershtml = qa_get_users_html(array($this->userid), false, qa_path_to_root(), true);
			$this->userhtml = @$usershtml[$this->userid];
		} else {
			$this->userhtml = qa_html($handle);
		}
	}

    // badge
    private function get_reach_count($id, $userpoints, $useraccount) {
        $number = 0;
        if ($id == 1) {
            // 回答数
            $number = $userpoints['aposts'];
        } elseif ($id == 2) {
            // 提问数
            $number = $userpoints['qposts'];
        } elseif ($id == 3) {
            // 被点赞数
            $number = $userpoints['upvoteds'];
        } elseif ($id == 4) {
            // 评论数
            $number = $userpoints['cposts'];
        } elseif ($id == 5) {
            // 投票数
            $number = $userpoints['qupvotes'] + $userpoints['aupvotes'];
        } elseif ($id == 6) {
            // 被采纳数
            $number = $userpoints['aselecteds'];
        } elseif ($id == 7) {
            // 在线时长
            $number = ((int)$useraccount['totalactiontime']) / 60;
        } elseif ($id == 8) {
            // 首答次数
            $number = (int)qa_db_read_one_value(qa_db_query_sub('SELECT count(*) from qa_posts where userid = # AND postid in (
SELECT MIN(postid) AS first_answer_id
FROM qa_posts
WHERE type = \'A\'
GROUP BY parentid)', $useraccount['userid']));
        } elseif ($id == 9) {
            // 问题被点击数
            $number = (int)qa_db_read_one_value(qa_db_query_sub('SELECT sum(clicktimes) FROM ^posts WHERE userid = # AND type = \'Q\'', $userpoints['userid']));
        } elseif ($id == 10) {
            // 登录天数
            $number = (int)$useraccount['logindays'];
        }
        return $number;
    }

    public function badge($handle) {
        $this->userHtml($handle);

        $loginuserid = qa_get_logged_in_userid();
        $useraccount = qa_db_read_one_assoc(qa_db_query_sub('SELECT * FROM ^users WHERE handle = $', $handle));
        $userpoints = qa_db_read_one_assoc(qa_db_query_sub('SELECT * FROM ^userpoints WHERE userid = #', $useraccount['userid']));


        if (!QA_FINAL_EXTERNAL_USERS && !is_array($useraccount)) { // check the user exists
            throw new PageNotFoundException();
        }

        // Prepare content for theme

        $qa_content = qa_content_prepare(true);
        $qa_content['title'] = '徽章墙';
        $badgeInfo = qa_db_read_all_assoc(qa_db_query_sub('SELECT * FROM ^badge'));
        $completed = 1;
        if (!empty($badgeInfo)) {
            $qa_content['custom'] = '';
            foreach ($badgeInfo as $key => $value) {
                $reach_count = $this->get_reach_count($value['id'], $userpoints, $useraccount);
                $level = 0;
                if ($reach_count >= $value['level_3']) $level = 3;
                else if ($reach_count >= $value['level_2']) $level = 2;
                else if ($reach_count >= $value['level_1']) $level = 1;
                if ($level == 0) $completed = 0;

                $qa_content['custom'] .= '<div class="badge-container">';
                if ($value['id'] == 7) {
                    for ($i = 1; $i <= $level; ++$i) {
                        $qa_content['custom'] .= '<div class="badge">
			<img src="./qa-theme/general/qa-badge.png"'. 'alt="Badge" title = "达成在线分钟数:' . $value['level_'.$i] . '">
			<h2>'. $value['name'.$i] . '</h2>
			<p>' . $value['description'] . '</p>
		</div>';
                    }
                    for (; $i <= 3; ++$i) {
                        $qa_content['custom'] .= '<div class="badge">
			<img src="./qa-theme/general/badge-lock.png"'. 'alt="Badge" title = "获取进度:' . $reach_count . '/' . $value['level_'.$i] . '">
			<h2>待解锁</h2>
			<p>' . $value['description'] . '</p>
		</div>';
                    }
                }
                else {
                    for ($i = 1; $i <= $level; ++$i) {
                        $qa_content['custom'] .= '<div class="badge">
			<img src="./qa-theme/general/qa-badge.png"'. 'alt="Badge" title = "达成次数:' . $value['level_'.$i] . '">
			<h2>'. $value['name'.$i] . '</h2>
			<p>' . $value['description'] . '</p>
		</div>';
                    }
                    for (; $i <= 3; ++$i) {
                        $qa_content['custom'] .= '<div class="badge">
			<img src="./qa-theme/general/badge-lock.png"' . 'alt="Badge" title = "获取进度:' . $reach_count . '/' . $value['level_' . $i] . '">
			<h2>待解锁</h2>
			<p>' . $value['description'] . '</p>
		</div>';
                    }
                }
                $qa_content['custom'] .= '</div><hr/>';

            }
            // 隐藏成就
            // 完成五次任务
            $taskcount = qa_db_read_one_value(qa_db_query_sub('SELECT count(*) FROM ^taskfinish WHERE user_id = #', $useraccount['userid']));
            if ($taskcount >= 5) {
                $desc = '完成五次任务';
                if ($loginuserid != $useraccount['userid']) {
                    $desc = '该用户已获得';
                }
                $qa_content['custom'] .= '<div class="badge-container">
		<div class="badge">
			<img src="./qa-theme/general/qa-badge.png" alt="Badge">
			<h2>赏金猎人</h2>
			<p>'. $desc .'</p>
		</div>';
            } else {
                $qa_content['custom'] .= '<div class="badge-container">
		<div class="badge">
			<img src="./qa-theme/general/badge-lock2.png" alt="Badge">
			<h2>待解锁</h2>
			<p>隐藏成就</p>
		</div>';
            }
            // 参与十次问答挑战
            // categoryid 需改为 问答挑战 的id。
            $challengecount = qa_db_read_one_value(qa_db_query_sub('SELECT count(*) FROM ^posts WHERE userid = # AND categoryid = 1 AND type = \'A\'', $useraccount['userid']));
            if ($challengecount >= 10) {
                $desc = '参与十次问答挑战';
                if ($loginuserid != $useraccount['userid']) {
                    $desc = '该用户已获得';
                }
                $qa_content['custom'] .= '
		<div class="badge">
			<img src="./qa-theme/general/qa-badge.png" alt="Badge">
			<h2>挑战达人</h2>
			<p>'. $desc .'</p>
		</div>';
            } else {
                $qa_content['custom'] .= '
		<div class="badge">
			<img src="./qa-theme/general/badge-lock2.png" alt="Badge">
			<h2>待解锁</h2>
			<p>隐藏成就</p>
		</div>';
            }

            // 完成所有一级徽章
            if ($completed == 1) {
                $desc = '获得第一列徽章';
                if ($loginuserid != $useraccount['userid']) {
                    $desc = '该用户已获得';
                }
                $qa_content['custom'] .= '
		<div class="badge">
			<img src="./qa-theme/general/qa-badge.png" alt="Badge">
			<h2>收藏家</h2>
			<p>'. $desc .'</p>
		</div>
	</div>
	<hr/>' ;
            } else {
                $qa_content['custom'] .= '
		<div class="badge">
			<img src="./qa-theme/general/badge-lock2.png" alt="Badge">
			<h2>待解锁</h2>
			<p>隐藏成就</p>
		</div>
	</div>
	<hr/>' ;
            }
        }

        // Sub menu for navigation in user pages

        $ismyuser = isset($loginuserid) && $loginuserid == (QA_FINAL_EXTERNAL_USERS ? $this->userid : $useraccount['userid']);
        $qa_content['navigation']['sub'] = qa_user_sub_navigation($handle, 'badge', $ismyuser);


        return $qa_content;
    }
}
