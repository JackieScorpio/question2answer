<?php

/*
	Question2Answer by Gideon Greenspan and contributors
	http://www.question2answer.org/

	Description: Controller for page manage user's badges


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

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
    header('Location: ../../');
    exit;
}

require_once QA_INCLUDE_DIR . 'db/selects.php';
require_once QA_INCLUDE_DIR . 'app/format.php';
require_once QA_INCLUDE_DIR . 'db/points.php';
require_once QA_INCLUDE_DIR . 'db/admin.php';
require_once QA_INCLUDE_DIR . 'db/maxima.php';
require_once QA_INCLUDE_DIR . 'app/options.php';
require_once QA_INCLUDE_DIR . 'app/admin.php';


// Check that we're logged in

$userid = qa_get_logged_in_userid();

if (!isset($userid))
    qa_redirect('login');

// Prepare and return content for theme
$qa_content = qa_content_prepare(true);

$qa_content['title'] = '用户统计';

/*
 * create table qa_badge
(
    id          int auto_increment
        primary key,
    name        varchar(128) not null,
    level_1     int          null,
    level_2     int          null,
    level_3     int          null,
    description varchar(128) null
);
 */
function get_reach_count($id, $useraccount, $userpoints) {
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
GROUP BY parentid)', $userpoints['userid']));
    } elseif ($id == 9) {
        // 问题被点击数
        $number = (int)qa_db_read_one_value(qa_db_query_sub('SELECT sum(clicktimes) FROM ^posts WHERE userid = # AND type = \'Q\'', $userpoints['userid']));
    } elseif ($id == 10) {
        // 登录天数
        $number = (int)$useraccount['logindays'];
    }
    return $number;
}

$useraccount = qa_db_read_all_assoc(qa_db_query_sub(
    'SELECT * FROM ^users'));

$userpoint = qa_db_read_all_assoc(qa_db_query_sub(
    'SELECT * FROM ^userpoints'));

$usersaccounts = array();
foreach ($useraccount as $user) {
    $usersaccounts[$user['userid']] = $user;
}

$userpoints = array();
foreach ($userpoint as $userp) {
    $userpoints[$userp['userid']] = $userp;
}

$qa_content['custom'] = '<div style="overflow-x: scroll">';

$qa_content['custom'] .= '<input id = "user-statistics-search" placeholder="请输入要查找的用户名或姓名" style="width: 200px">
<button class="task-management-search-btn" onclick="searchUserStat()">搜索</button>
<button class="task-management-reset-btn" onclick="resetUserStat()">重置</button>
<a href="./qa-statistics/pages/samples/login.html"><button class="task-management-visualization-btn"">可视化界面</button></a>';

$qa_content['custom'] .= '
  <table class="user-statistics-management-table">
  <thead>
    <tr>
      <th>
        用户名
      </th>
      <th>
        真实姓名
      </th>
      <th onclick="sortStatisticsTable(2, this)" class = "user-statistics-desc">回答数</th>
      <th onclick="sortStatisticsTable(3, this)" class = "user-statistics-desc">提问数</th>
      <th onclick="sortStatisticsTable(4, this)" class = "user-statistics-desc">被点赞数</th>
      <th onclick="sortStatisticsTable(5, this)" class = "user-statistics-desc">评论数</th>
      <th onclick="sortStatisticsTable(6, this)" class = "user-statistics-desc">投票数</th>
      <th onclick="sortStatisticsTable(7, this)" class = "user-statistics-desc">被采纳数</th>
      <th onclick="sortStatisticsTable(8, this)" class = "user-statistics-desc">在线时长</th>
      <th onclick="sortStatisticsTable(9, this)" class = "user-statistics-desc">首答次数</th>
      <th onclick="sortStatisticsTable(10, this)" class = "user-statistics-desc">登录天数</th>
    </tr>
  </thead>
  <tbody id = "user-stat-body">';

foreach ($usersaccounts as $key => $user) {
    // 忽略管理员 先不忽略
    if ($key != 1 || 1 == 1) {
        $username = $user['handle'];
        $userrealname = $user['realname'];
        $col1 = get_reach_count(1, $user, $userpoints[$key]);
        $col2 = get_reach_count(2, $user, $userpoints[$key]);
        $col3 = get_reach_count(3, $user, $userpoints[$key]);
        $col4 = get_reach_count(4, $user, $userpoints[$key]);
        $col5 = get_reach_count(5, $user, $userpoints[$key]);
        $col6 = get_reach_count(6, $user, $userpoints[$key]);
        $col7 = get_reach_count(7, $user, $userpoints[$key]);
        $col8 = get_reach_count(8, $user, $userpoints[$key]);
        $col10 = get_reach_count(10, $user, $userpoints[$key]);

        $qa_content['custom'] .= '<tr>';
        $qa_content['custom'] .= '<td>' . $username . '</td>';
        $qa_content['custom'] .= '<td>' . $userrealname . '</td>';
        $qa_content['custom'] .= '<td>' . $col1 . '</td>';
        $qa_content['custom'] .= '<td>' . $col2 . '</td>';
        $qa_content['custom'] .= '<td>' . $col3 . '</td>';
        $qa_content['custom'] .= '<td>' . $col4 . '</td>';
        $qa_content['custom'] .= '<td>' . $col5 . '</td>';
        $qa_content['custom'] .= '<td>' . $col6 . '</td>';
        $qa_content['custom'] .= '<td>' . (int)$col7 . '</td>';
        $qa_content['custom'] .= '<td>' . $col8 . '</td>';
        $qa_content['custom'] .= '<td>' . $col10 . '</td>';
        $qa_content['custom'] .= '</tr>';
    }
}

$qa_content['custom'] .= '</tbody>
</table>
</div>';

// Sub navigation for account pages and suggestion
$qa_content['navigation']['sub'] = qa_admin_sub_navigation();
$qa_content['navigation']['sub']['admin/statistics']['selected'] = true;

return $qa_content;
