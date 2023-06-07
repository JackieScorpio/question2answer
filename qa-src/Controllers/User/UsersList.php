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
        // TODO 管理员和助教不参与排名
        // 所有学生的userid：35, 31, 21, 30, 24, 20, 15, 18, 16, 26, 14, 32, 8, 9, 26, 22, 11, 25, 29, 33, 19, 28, 13, 7, 17, 12

        // $adminids 中包含除学生之外的用户id，保证只在学生中计算topsis
        $adminids = array(1, 2, 3, 4, 6, 10, 23, 34, 66, 67, 75);

        foreach ($userpoint as $userp) {
            if (in_array((int)$userp['userid'], $adminids)) {
                continue;
            }
            $userpoints[$userp['userid']] = $userp;
        }

        $badgeInfo = qa_db_read_all_assoc(qa_db_query_sub('SELECT * FROM ^badge'));

        $usersort = array();
        foreach ($userpoints as $userp) {
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

            //$sortcol['value'] = $userp['points'] + $levels[1] * 200 +  $levels[2] * 500 + $levels[3] * 1000;
            $sortcol['handle'] = $useraccounts[$userid]['handle'];

            $usersort[$userid] = $sortcol;
        }

        // $input_matrix = array();

        // 一级指标-回答贡献(二维数组)
        $input_matrix_answer_contribution = array();
        // 一级指标-提问贡献(二维数组)
        $input_matrix_question_contribution = array();
        // 一级指标-参与贡献(二维数组)
        $input_matrix_participation_contribution = array();

        foreach ($userpoints as $userp) {
            $topsis_user = array();
            $userid = $userp['userid'];

            
            /* 一级指标：回答贡献 */
            $input_matrix_answer_contribution_row = array();
                // 二级指标：首答的次数Fa（First answer）
            $input_matrix_answer_contribution_row[] = (int)qa_db_read_one_value(qa_db_query_sub('SELECT count(*) from qa_posts where userid = # AND postid in (
            SELECT MIN(postid) AS first_answer_id
            FROM qa_posts
            WHERE type = \'A\'
            GROUP BY parentid)', $userid));

                // 二级指标：回答被采纳次数Sa (Sloved of answer)
            $input_matrix_answer_contribution_row[] = (int)$userp['aselecteds'];

                // 二级指标：回答的平均得票数Va (Votes of answer)
            $input_matrix_answer_contribution_row[] = (int)($userp['avoteds'] / 2); // avoteds => 回答被投票对应的分数，upvote加2分，downvote减2分

                // 二级指标：回答次数Pa (Posts of answer)
            $input_matrix_answer_contribution_row[] = (int)$userp['aposts'];

            // 把一名学生的 4 个二级指标放到一行中
            $input_matrix_answer_contribution[] = $input_matrix_answer_contribution_row; 

            /* 一级指标：提问贡献 */
            $input_matrix_question_contribution_row = array();

                // 二级指标：提问次数Pq (Posts of question)
            $input_matrix_question_contribution_row[] = (int)$userp['qposts'];

                // 二级指标：问题平均得票数Vq (Votes of question)
            $input_matrix_question_contribution_row[] = (int)$userp['qvoteds'];

                // 二级指标：问题被点击数Cq (Clicks of question)
            $input_matrix_question_contribution_row[] = (int)qa_db_read_one_value(qa_db_query_sub('SELECT sum(clicktimes) FROM ^posts WHERE userid = # AND type = \'Q\'', $userid));

            $input_matrix_question_contribution[] = $input_matrix_question_contribution_row;

            /* 一级指标：参与贡献 */
            $input_matrix_participation_contribution_row = array();

                // 二级指标：投票次数Vp (Voting of post) 对问题或回答进行投票的次数
            $input_matrix_participation_contribution_row[] = (int)($userp['qupvotes'] + $userp['qdownvotes'] + $userp['aupvotes'] + $userp['adownvotes']);

                // 二级指标：论坛访问总天数 Dv (Days of visiting)
            $input_matrix_participation_contribution_row[] = (int)$useraccounts[$userid]['logindays'];

                // 二级指标：活跃在线总时长Od (active Online duration)
            $input_matrix_participation_contribution_row[] = ((int)$useraccounts[$userid]['totalactiontime']) / 60;

                // 二级指标：发表的评论数Pc (Posts of comments)
            $input_matrix_participation_contribution_row[] = (int)$userp['cposts'];

            $input_matrix_participation_contribution[] = $input_matrix_participation_contribution_row;

            // // 在线时长
            // $topsis_user[] = ((int)$useraccounts[$userid]['totalactiontime']) / 60;
            // // 登陆天数
            // $topsis_user[] = (int)$useraccounts[$userid]['logindays'];

            // // 问答数量
            // $topsis_user[] = (int)$userp['aposts'];
            // $topsis_user[] = (int)$userp['qposts'];

            // // 问答质量
            // $topsis_user[] = (int)$userp['upvoteds'];
            // $topsis_user[] = (int)$userp['aselecteds'];

            // // 徽章系统，根据获取难度权重，低级徽章1，中级徽章5，高级徽章10
            // $topsis_user[] = $usersort[$userid]['badge1'] * 1 + $usersort[$userid]['badge2'] * 5 + $usersort[$userid]['badge1'] * 10;

            // // 任务系统 完成任务数
            $taskFinish = (int)qa_db_read_one_value(qa_db_query_sub('SELECT count(*) FROM ^taskfinish WHERE user_id = #', $userid));
            // $topsis_user[] = $taskFinish;

            // // 积分
            // $topsis_user[] = (int)$userp['points'];

            // $input_matrix[] = $topsis_user;

            // 记录各个信息以显示
            $usersort[$userid]['totalactiontime'] = ((int)$useraccounts[$userid]['totalactiontime']) / 60;
            $usersort[$userid]['logindays'] = (int)$useraccounts[$userid]['logindays'];
            $usersort[$userid]['aposts'] = (int)$userp['aposts'];
            $usersort[$userid]['qposts'] = (int)$userp['qposts'];
            $usersort[$userid]['upvoteds'] = (int)$userp['upvoteds'];
            $usersort[$userid]['aselecteds'] = (int)$userp['aselecteds'];
            $usersort[$userid]['taskfinish'] = $taskFinish;
        }

        // $totalnum = 9;

        // $weight_matrix = array(0.0285, 0.0168, 0.0799, 0.0799, 0.0799, 0.21, 0.1838, 0.1373, 0.1838);

        // $impact_matrix[] = array();
        // for ($i = 0; $i<$totalnum; ++$i) {
        //     $impact_matrix[$i] = 1;
        // }
        //        echo var_dump($input_matrix);
        //        echo var_dump(count($weight_matrix));
        //        echo var_dump(count($impact_matrix));

        $topsis_ans = $this -> getComprehensivePoints($input_matrix_answer_contribution, $input_matrix_question_contribution, $input_matrix_participation_contribution);
        
        // 展示 topsis 分数， 算最终分数
        // $counter = 0;
        // foreach ($userpoints as $userp) {
        //     // $topsis_ans 数组中元素的顺序，就是使用 foreach 遍历 $userpoints 的顺序，即 $topsis_ans 中的 si 值和 $userp['userid']是一一对应的
        //     $userid = $userp['userid'];

        //     // 此处由老师指定了Xmax（最高得分）是5分，Xmin（最低得分）是0分
        //     $final_point = (($topsis_ans[$counter] - min($topsis_ans))/(max($topsis_ans) - min($topsis_ans))) * (5 - 0) + 0;
        //     echo $final_point.'<br>';
        //     $counter++;
        // }

        $topsis_index = 0;

        foreach ($userpoints as $userp) {
            $userid = $userp['userid'];
            $usersort[$userid]['value'] = $topsis_ans[$topsis_index];
            $topsis_index++;
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
                $qa_content['custom'] .= '<li class="" style="list-style-type:none;">';
            }
            elseif ($index == 2) {
                $index++;
                $itemicon = 'item-icon002';
                $qa_content['custom'] .= '<li class="" style="list-style-type:none;">';
            }
            elseif ($index == 3) {
                $index++;
                $itemicon = 'item-icon003';
                $qa_content['custom'] .= '<li class="" style="list-style-type:none;">';
            } else {
                $qa_content['custom'] .= '<li class="" style="list-style-type:none;">';
                $index++;
            }

            $badgeinamge = '';
            if ($item['qposts'] != 0)
            $badgeinamge .= $item['qposts'] . '<img src = "./qa-theme/general/rank-question.png" style="width: 20px;height: 20px" title="提问数"> &ensp;';

            if ($item['aposts'] != 0)
            $badgeinamge .= $item['aposts'] . '<img src = "./qa-theme/general/rank-answer.png" style="width: 20px;height: 20px" title="回答数"> &ensp;';

            if ($item['aselecteds'] != 0)
                $badgeinamge .= $item['aselecteds'] . '<img src = "./qa-theme/general/rank-selected.png" style="width: 20px;height: 20px" title="回答采纳数"> &ensp;';

            if ($item['taskfinish'] != 0)
            $badgeinamge .= $item['taskfinish'] . '<img src = "./qa-theme/general/rank-task.png" style="width: 20px;height: 20px" title="任务完成数"> &ensp;';


            for ($i = 1; $i <= 3; ++$i) {
                $levels = $item['levels'];
                if ($levels[$i] > 0) {
                    $badgeinamge .= $levels[$i] . '<img src = "./qa-theme/general/badge-' . $i . '.png" style="width: 20px;height: 20px"> ';
                }
            }

//            $qa_content['custom'] .= '<div class="ques-list-box">
//					<div class="ques-list-head">
//						<div class="ques-list-image"><img src="./qa-theme/general/rank/user.png" alt=""></div>
//					</div>
//					<div class="ques-list-name">
//						<div class="ques-list-name-head"><a href='. $userurl .'>'. $item['handle'] . '</a></div>
//						<div class="ques-list-name-text">积分: '. $item['points'] .'</div>
//					</div>
//					<div class="ques-list-badge-icon">
//					    ' .$badgeinamge .'
//					</div>
//					<span class="ques-qa-top-users-score">'. (int)($item['value']*1000) .'</span>
//					<span class="ques-list-name-icon '. $itemicon .'">'. ($index-1) .'</span>
//				</div>';
            $qa_content['custom'] .= '<div class="ques-list-box">
					
					<div class="ques-list-name">
						<div class="ques-list-name-head"><a href='. $userurl .'>'. $item['handle'] . '</a></div>
						<div class="ques-list-name-text">积分: '. $item['points'] .'</div>
					</div>
					<div class="ques-list-badge-icon">
					    ' .$badgeinamge .'
					</div>
					<span class="ques-qa-top-users-score">'. round($item['value'], 4) .'</span>
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

    private function getComprehensivePoints($input_matrix_answer_contribution, $input_matrix_question_contribution, $input_matrix_participation_contribution) {
        $answer_contribution_weight_matrix = array(0.0723, 0.5623, 0.2931, 0.0723);
        $question_contribution_weight_matrix = array(0.1634, 0.5396, 0.2970);
        $participation_contribution_weight_matrix = array(0.1034, 0.2082, 0.0949, 0.5935);

        // 根据 topsis 方法算出一级指标综合分数，拿到三个 si 的一维数组
        // 第i个学生的第k个一级指标的综合分数为S(ik)
        $si_answer_contribution = $this -> topsis($input_matrix_answer_contribution, $answer_contribution_weight_matrix, true);
        $si_question_contribution = $this -> topsis($input_matrix_question_contribution, $question_contribution_weight_matrix, true);
        $si_participation_contribution = $this -> topsis($input_matrix_participation_contribution, $participation_contribution_weight_matrix, true);
    
        // 总体贡献权重向量
        $total_weight_matrix = array(0.6328, 0.2872, 0.0780);
    
        // 整理数组，满足每一行为一名学生的三个一级指标的 Si
        $final_prepared_matrix = array();

        for ($i = 0; $i < count($si_answer_contribution); $i++) {
            $final_prepared_matrix[] = [$si_answer_contribution[$i], $si_question_contribution[$i], $si_participation_contribution[$i]];
        }

        // echo count($final_prepared_matrix);
        // foreach ($final_prepared_matrix as $item) {
        //     foreach($item as $k) {
        //         echo $k.' ;';
        //     }
        //     echo '<br>';
        // }

        $final_si = $this -> topsis($final_prepared_matrix, $total_weight_matrix);

        return $final_si;
    }

    private function normalize($input_array) {
        // for ($i=0; $i < count($input_array); $i++) {
        //     for ($j=0; $j < count($input_array[$i]); $j++) {
        //         echo '第'.$i.'行第'.$j.'列:'.$input_array[$i][$j].' ';
        //     }
        // }

        // 行数
        $row_number = count($input_array);
        // 列数
        $column_number = count($input_array[0]);

        // 求出除数：每一列的平方和开根号
        $divisor = array();

        for ($column = 0; $column < $column_number; $column++) {
            $value_for_one_column = 0;
            for ($row = 0; $row < $row_number; $row++) {
                $value_for_one_column += pow($input_array[$row][$column], 2);
            }
            $divisor[] = sqrt($value_for_one_column);
        }

        // for ($i=0; $i < count($divisor); $i++) {
        //     echo $divisor[$i].' ';
        // }
        // 求解标准化数组
        $normalized_array = array();

        for ($row = 0; $row < $row_number; $row++) {
            $new_row = array();
            for ($column = 0; $column < $column_number; $column++) {
                $new_row[] = $input_array[$row][$column]/$divisor[$column];
                // echo '第'.$row.'行第'.$column.'列:'.$normalized_array[$i][$j].' ';
                // echo '上'.$input_array[$row][$column];
                // echo '值'.$input_array[$row][$column]/$divisor[$column];
            }
            $normalized_array[] = $new_row;
        }
        // echo '============'.count($normalized_array[0]);
        // for ($i=0; $i < count($normalized_array); $i++) {
        //     for ($j=0; $j < count($normalized_array[$i]); $j++) {
        //         echo '第'.$i.'行第'.$j.'列:'.$normalized_array[$i][$j].' ';
        //     }
        // }

        return $normalized_array;
    }

    private function getMaximumAndMinimum($input_matrix) {
        $max_and_min = array();
        for ($row = 0; $row < count($input_matrix); $row++) {
            for ($column = 0; $column < count($input_matrix[0]); $column++) {
                $current_value = $input_matrix[$row][$column];
                if (!isset($max_and_min[$column])) {
                    $max_and_min[$column] = array(0 => $current_value, 1=>$current_value);
                } else {
                    $max_and_min[$column][0] = max($max_and_min[$column][0], $current_value);
                    $max_and_min[$column][1] = min($max_and_min[$column][1], $current_value);
                }
            }
        }
        // for($i=0;$i< count($max_and_min);$i++) {
        //     echo 'the'.$i.'column:'.'max-'.$max_and_min[$i][0].' min-'.$max_and_min[$i][1].' .';
        // }
        return $max_and_min;
    }

    private function topsis($input_matrix, $weight_matrix, $needNormalization = false) {
        $prepared_input_matrix = $input_matrix;

        if ($needNormalization) {
            $prepared_input_matrix = $this -> normalize($input_matrix);
        }

        // 获取矩阵每一列的最大/最小值，最大/最小值以数组 item_array 的形式存放在一个包含数组中，item_array 中第一个元素为 max，第二个元素为 min
        // eg: [[0.4,0], [0.6,0], .....]
        $max_and_min = $this -> getMaximumAndMinimum($prepared_input_matrix);

        // 计算各位同学的 Si
        $si_array = array();

        for ($row = 0; $row < count($prepared_input_matrix); $row++) {
            $di_plus_sum = 0;
            $di_minus_sum = 0;
            for ($column = 0; $column < count($prepared_input_matrix[0]); $column++) {
                $di_plus_sum += $weight_matrix[$column] * pow(($max_and_min[$column][0] - $prepared_input_matrix[$row][$column]), 2);
                $di_minus_sum += $weight_matrix[$column] * pow(($max_and_min[$column][1] - $prepared_input_matrix[$row][$column]), 2);
            }
            // 计算一位同学的 Di+
            $di_plus = sqrt($di_plus_sum);
            // 计算一位同学的 Di-
            $di_minus = sqrt($di_minus_sum);

            $si_array[$row] = $di_minus / ($di_minus + $di_plus);
        }

        return $si_array;
    }

    // private function topsis($input_matrix, $weight_matrix) {
    //     // 定义标准化矩阵为具有n行m列的2D阵列
    //     $normalized_matrix = array();

    //     // 每列的平方和
    //     $sum_of_squares = array();

    //     // 每列平方和的平方根
    //     $sqrt_sum_of_squares = array();

    //     for ($j = 0; $j < count($input_matrix[0]); $j++) {
    //         $sum_of_squares[$j] = 0;
    //     }

    //     // 构建标准化矩阵
    //     for ($i = 0; $i < count($input_matrix); $i++) {
    //         $row = array();
    //         for ($j = 0; $j < count($input_matrix[$i]); $j++) {
    //             $row[] = $input_matrix[$i][$j];
    //             $sum_of_squares[$j] += pow($input_matrix[$i][$j], 2);
    //         }
    //         $normalized_matrix[] = $row;
    //     }

    //     for ($i = 0; $i < count($sum_of_squares); $i++) {
    //         $sqrt_sum_of_squares[$i] = sqrt($sum_of_squares[$i]);
    //     }

    //     $weighted_normalized_matrix = array();
    //     for ($i = 0; $i < count($normalized_matrix); $i++) {
    //         $row = array();
    //         for ($j = 0; $j < count($normalized_matrix[$i]); $j++) {
    //             $row[] = $normalized_matrix[$i][$j] / $sqrt_sum_of_squares[$j];
    //         }
    //         $weighted_normalized_matrix[] = $row;
    //     }

    //     // 计算最优解和最劣解
    //     $ideal_solution = array();
    //     $negative_ideal_solution = array();
    //     for ($i = 0; $i < count($weighted_normalized_matrix[0]); $i++) {
    //         $column = array_column($weighted_normalized_matrix, $i);

    //         $ideal_solution[] = max($column);
    //         $negative_ideal_solution[] = min($column);
    //         // if ($impact_matrix[$i] == 1) {
    //         //     $ideal_solution[] = max($column);
    //         //     $negative_ideal_solution[] = min($column);
    //         // } else {
    //         //     $ideal_solution[] = min($column);
    //         //     $negative_ideal_solution[] = max($column);
    //         // }
    //     }

    //     // 计算每个备选方案到最优解和最劣解的距离
    //     $distance_to_ideal = array();
    //     $distance_to_negative_ideal = array();
    //     for ($i = 0; $i < count($weighted_normalized_matrix); $i++) {
    //         $row = $weighted_normalized_matrix[$i];
    //         $d_plus = 0;
    //         $d_minus = 0;
    //         for ($j = 0; $j < count($row); $j++) {
    //             $d_plus += $weight_matrix[$j] * pow($row[$j] - $ideal_solution[$j], 2);
    //             $d_minus += $weight_matrix[$j] * pow($row[$j] - $negative_ideal_solution[$j], 2);
    //         }
    //         $distance_to_ideal[] = sqrt($d_plus);
    //         $distance_to_negative_ideal[] = sqrt($d_minus);
    //     }

    //     // 计算每个备选方案的得分
    //     $performance_score = array();
    //     for ($i = 0; $i < count($distance_to_negative_ideal); $i++) {
    //         // echo 'userid'.$userid.' si is :'.$distance_to_negative_ideal[$i];
    //         $performance_score[] = $distance_to_negative_ideal[$i] / ($distance_to_ideal[$i] + $distance_to_negative_ideal[$i]);
    //     }

    //     return $performance_score;

    //     // Rank the alternatives based on their performance score
    //     //        arsort($performance_score);
    //     //        $ranked_alternatives = array_keys($performance_score);
    //     //
    //     //
    //     //        return array($ranked_alternatives, $performance_score);
    // }
}
