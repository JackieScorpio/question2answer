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

use Q2A\Auth\NoPermissionException;
use Q2A\Controllers\BaseController;
use Q2A\Database\DbConnection;
use Q2A\Middleware\Auth\InternalUsersOnly;
use Q2A\Middleware\Auth\MinimumUserLevel;

class UsersList extends BaseController
{
	public function __construct(DbConnection $db)
	{
		require_once QA_INCLUDE_DIR . 'db/users.php';
		require_once QA_INCLUDE_DIR . 'db/selects.php';
		require_once QA_INCLUDE_DIR . 'app/users.php';
		require_once QA_INCLUDE_DIR . 'app/format.php';

		parent::__construct($db);

		$this->addMiddleware(new InternalUsersOnly(), array('newest', 'special', 'blocked'));
		$this->addMiddleware(new MinimumUserLevel(QA_USER_LEVEL_MODERATOR), array('blocked'));
	}

	/**
	 * Display top users page (ordered by points)
	 * @return array $qa_content
	 */
	public function top()
	{
		// callables to fetch user data
		$fetchUsers = function ($start, $pageSize) {
			return array(
				qa_opt('cache_userpointscount'),
				qa_db_select_with_pending(qa_db_top_users_selectspec($start, $pageSize))
			);
		};
		$userScore = function ($user) {
			return qa_html(qa_format_number($user['points'], 0, true));
		};

		$qa_content = $this->rankedUsersContent($fetchUsers, $userScore);

		$qa_content['title'] = empty($qa_content['ranking']['items'])
			? qa_lang_html('main/no_active_users')
			: qa_lang_html('main/highest_users');

		$qa_content['ranking']['sort'] = 'points';

		return $qa_content;
	}

	/**
	 * Display newest users page
	 *
	 * @return array $qa_content
	 * @throws NoPermissionException
	 */
	public function newest()
	{
		// check we have permission to view this page (moderator or above)
		if (qa_user_permit_error('permit_view_new_users_page')) {
			throw new NoPermissionException();
		}

		// callables to fetch user data
		$fetchUsers = function ($start, $pageSize) {
			return array(
				qa_opt('cache_userpointscount'),
				qa_db_select_with_pending(qa_db_newest_users_selectspec($start, $pageSize))
			);
		};
		$userDate = function ($user) {
			$when = qa_when_to_html($user['created'], 7);
			return $when['data'];
		};

		$qa_content = $this->rankedUsersContent($fetchUsers, $userDate);

		$qa_content['title'] = empty($qa_content['ranking']['items'])
			? qa_lang_html('main/no_active_users')
			: qa_lang_html('main/newest_users');

		$qa_content['ranking']['sort'] = 'date';

		return $qa_content;
	}

	/**
	 * Display special users page (admins, moderators, etc)
	 *
	 * @return array $qa_content
	 * @throws NoPermissionException
	 */
	public function special()
	{
		// check we have permission to view this page (moderator or above)
		if (qa_user_permit_error('permit_view_special_users_page')) {
			throw new NoPermissionException();
		}

		// callables to fetch user data
		$fetchUsers = function ($start, $pageSize) {
			// here we fetch *all* users to get the total instead of a separate query; there are unlikely to be many special users
			$users = qa_db_select_with_pending(qa_db_users_from_level_selectspec(QA_USER_LEVEL_EXPERT));
			return array(count($users), $users);
		};
		$userLevel = function ($user) {
			return qa_html(qa_user_level_string($user['level']));
		};

		$qa_content = $this->rankedUsersContent($fetchUsers, $userLevel);

		$qa_content['title'] = qa_lang_html('users/special_users');

		$qa_content['ranking']['sort'] = 'level';

		return $qa_content;
	}

	/**
	 * Display blocked users page
	 * @return array $qa_content
	 */
	public function blocked()
	{
		// callables to fetch user data
		$fetchUsers = function ($start, $pageSize) {
			list($totalUsers, $users) = qa_db_select_with_pending(
				qa_db_selectspec_count(qa_db_users_with_flag_selectspec(QA_USER_FLAGS_USER_BLOCKED)),
				qa_db_users_with_flag_selectspec(QA_USER_FLAGS_USER_BLOCKED, $start, $pageSize)
			);

			return array($totalUsers['count'], $users);
		};
		$userLevel = function ($user) {
			return qa_html(qa_user_level_string($user['level']));
		};

		$qa_content = $this->rankedUsersContent($fetchUsers, $userLevel);

		$qa_content['title'] = empty($qa_content['ranking']['items'])
			? qa_lang_html('users/no_blocked_users')
			: qa_lang_html('users/blocked_users');

		$qa_content['ranking']['sort'] = 'level';

		return $qa_content;
	}

    public function rank() {
        $qa_content = qa_content_prepare();

        $qa_content['title'] = qa_lang_html('main/highest_rank');

        $useraccount = qa_db_read_all_assoc(qa_db_query_sub(
            'SELECT * FROM ^users'));

        $userpoint = qa_db_read_all_assoc(qa_db_query_sub(
            'SELECT * FROM ^userpoints'));

        $useraccounts = array();
        foreach ($useraccount as $user) {
            $useraccounts[$user['userid']] = $user;
        }

        $userpoints = array();
        foreach ($userpoint as $userp) {
            $userpoints[$userp['userid']] = $userp;
        }
        $badgeInfo = qa_db_read_all_assoc(qa_db_query_sub('SELECT * FROM ^badge'));

        $usersort = array();
        foreach ($userpoint as $userp) {
            $userid = $userp['userid'];

            $sortcol = array();
            $sortcol['points'] = $userp['points'];

            $levels = array(0,0,0,0);
            foreach ($badgeInfo as $value) {
                $level = $this -> get_badge_levels($value, $userp, $useraccounts[$userid]);
                $levels[$level] += 1;
            }
            $levels[1] += $levels[2] + $levels[3];
            $levels[2] += $levels[3];
            $sortcol['badge1'] = $levels[1];
            $sortcol['badge2'] = $levels[2];
            $sortcol['badge3'] = $levels[3];
            $sortcol['levels'] = $levels;

            $sortcol['value'] = $userp['points'] + $levels[1] * 200 +  $levels[2] * 500 + $levels[3] * 1000;
            $sortcol['handle'] = $useraccounts[$userid]['handle'];

            $usersort[$userid] = $sortcol;
        }

        array_multisort(array_column($usersort,'value'),SORT_DESC, $usersort);



        $qa_content['custom'] = '
	<div>
		<ul class="ques-card-list">';

        $index = 1;
        foreach ($usersort as $item) {
            $userurl = qa_path_html('user/' . $item['handle']);
            $itemicon = 'item-icon004';
            if ($index == 1) {
                $index++;
                $itemicon = 'item-icon001';
                $qa_content['custom'] .= '<li class="ques-card-list-noe" style="list-style-type:none;">';
            }
            elseif ($index == 2) {
                $index++;
                $itemicon = 'item-icon002';
                $qa_content['custom'] .= '<li class="ques-card-list-two" style="list-style-type:none;">';
            }
            elseif ($index == 3) {
                $index++;
                $itemicon = 'item-icon003';
                $qa_content['custom'] .= '<li class="ques-card-list-three" style="list-style-type:none;">';
            } else {
                $qa_content['custom'] .= '<li class="" style="list-style-type:none;">';
                $index++;
            }
            $badgeinamge = '';
            for ($i = 1; $i <= 3; ++$i) {
                $levels = $item['levels'];
                if ($levels[$i] > 0) {
                    $badgeinamge .= $levels[$i] . '<img src = "./qa-theme/general/badge-' . $i . '.png" style="width: 20px;height: 20px"> ';
                }
            }
            $qa_content['custom'] .= '<div class="ques-list-box">
					<div class="ques-list-head">
						<div class="ques-list-image"><img src="./qa-theme/general/rank/user.png" alt=""></div>
					</div>
					<div class="ques-list-name">
						<div class="ques-list-name-head"><a href='. $userurl .'>'. $item['handle'] . '</a></div>
						<div class="ques-list-name-text">积分: '. $item['points'] .'</div>
					</div>
					<div class="ques-list-badge-icon">
					    ' .$badgeinamge .'
					</div>
					<span class="ques-qa-top-users-score">'. $item['value'] .'</span>
					<span class="ques-list-name-icon '. $itemicon .'">'. ($index-1) .'</span>
				</div>';

            $qa_content['custom'] .= '</li>';
        }


        $qa_content['custom'] .= '    
		</ul>
	</div>';


        $qa_content['navigation']['sub'] = qa_users_sub_navigation();

        return $qa_content;
    }

	/**
	 * Fetch $qa_content array for a set of ranked users.
	 * @param  callable $fnUsersAndCount Function that returns the list of users for a page and the user total.
	 * @param  callable $fnUserScore Function that returns the "score" (points, date, etc) that will be displayed.
	 * @return array $qa_content
	 */
	private function rankedUsersContent($fnUsersAndCount, $fnUserScore)
	{
		// get the users to display on this page

		$request = qa_request();
		$start = qa_get_start();
		$pageSize = qa_opt('page_size_users');

		list($totalUsers, $users) = $fnUsersAndCount($start, $pageSize);

		// get userids and handles of retrieved users
		$usersHtml = qa_userids_handles_html($users);

		// prepare content for theme

		$content = qa_content_prepare();

		$content['ranking'] = array(
			'items' => array(),
			'rows' => ceil($pageSize / qa_opt('columns_users')),
			'type' => 'users',
			// 'sort' is handled by calling code
		);

		foreach ($users as $user) {
			if (QA_FINAL_EXTERNAL_USERS) {
				$avatarHtml = qa_get_external_avatar_html($user['userid'], qa_opt('avatar_users_size'), true);
			} else {
				$avatarHtml = qa_get_user_avatar_html(
					$user['flags'],
					$user['email'],
					$user['handle'],
					$user['avatarblobid'],
					$user['avatarwidth'],
					$user['avatarheight'],
					qa_opt('avatar_users_size'),
					true
				);
			}

			$content['ranking']['items'][] = array(
				'avatar' => $avatarHtml,
				'label' => $usersHtml[$user['userid']],
				'score' => $fnUserScore($user),
				'raw' => $user,
			);
		}

		$content['page_links'] = qa_html_page_links($request, $start, $pageSize, $totalUsers, qa_opt('pages_prev_next'));

		$content['canonical'] = qa_get_canonical();

		$content['navigation']['sub'] = qa_users_sub_navigation();

		return $content;
	}

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
            $number = (int)qa_db_read_one_value(qa_db_query_sub(
                'SELECT count(*) from qa_posts where userid = # AND postid in (
SELECT MIN(postid) AS first_answer_id
FROM qa_posts
WHERE type = \'A\'
GROUP BY parentid)', $useraccount['userid']));
        } elseif ($id == 9) {
            // 问题被点击数
            $number = (int)qa_db_read_one_value(qa_db_query_sub(
                'SELECT sum(clicktimes) FROM ^posts 
                       WHERE userid = # AND type = \'Q\'', $userpoints['userid']));
        } elseif ($id == 10) {
            // 登录天数
            $number = (int)$useraccount['logindays'];
        }
        return $number;
    }

    private function get_badge_levels($value, $userpoints, $useraccount) {
        $number = 0;
        if ($value['id'] == 1) {
            // 知无不言
            $number = $userpoints['aposts'];
        } elseif ($value['id'] == 2) {
            // 好奇宝宝
            $number = $userpoints['qposts'];
        } elseif ($value['id'] == 3) {
            // 有口皆碑
            $number = $userpoints['upvoteds'];
        } elseif ($value['id'] == 4) {
            // 乐于交流
            $number = $userpoints['cposts'];
        } elseif ($value['id'] == 5) {
            // 表示赞同
            $number = $userpoints['qupvotes'] + $userpoints['aupvotes'];
        } elseif ($value['id'] == 6) {
            // 优质回答
            $number = $userpoints['aselecteds'];
        } elseif ($value['id'] == 7) {
            // 在线时长
            $number = ((int)$useraccount['totalactiontime']) / 60;
        } elseif ($value['id'] == 8) {
            // 首答次数
            $number = (int)qa_db_read_one_value(qa_db_query_sub('SELECT count(*) from qa_posts where userid = # AND postid in (
SELECT MIN(postid) AS first_answer_id
FROM qa_posts
WHERE type = \'A\'
GROUP BY parentid)', $userpoints['userid']));
        } elseif ($value['id'] == 9) {
            // 问题被点击数
            $number = (int)qa_db_read_one_value(qa_db_query_sub('SELECT sum(clicktimes) FROM ^posts WHERE userid = # AND type = \'Q\'', $userpoints['userid']));
        } elseif ($value['id']== 10) {
            // 登录天数
            $number = (int)$useraccount['logindays'];
        }
        if ($number >= $value['level_3']) {
            return 3;
        } elseif ($number >= $value['level_2']) {
            return 2;
        } elseif  ($number >= $value['level_1']) {
            return 1;
        }
        return 0;
    }
}
