<?php
/*
	Question2Answer by Gideon Greenspan and contributors
	http://www.question2answer.org/

	Description: Controller for page listing user's favorites


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

function get_reach_count($id, $userpoints, $useraccount) {
    $number = 0;
    if ($id == 1) {
        // 知无不言
        $number = $userpoints['aposts'];
    } elseif ($id == 2) {
        // 好奇宝宝
        $number = $userpoints['qposts'];
    } elseif ($id == 3) {
        // 有口皆碑
        $number = $userpoints['upvoteds'];
    } elseif ($id == 4) {
        // 乐于交流
        $number = $userpoints['cposts'];
    } elseif ($id == 5) {
        // 表示赞同
        $number = $userpoints['qupvotes'] + $userpoints['aupvotes'];
    } elseif ($id == 6) {
        // 优质回答
        $number = $userpoints['aselecteds'];
    } elseif ($id == 7) {
        // 在线时长
        $number = ((int)$useraccount['totalactiontime']) / 60;
    }
    return $number;
}

// Check that we're logged in

$userid = qa_get_logged_in_userid();

if (!isset($userid))
    qa_redirect('login');


$badgeInfo = qa_db_read_all_assoc(qa_db_query_sub('SELECT * FROM ^badge'));
$useraccount = qa_db_read_one_assoc(qa_db_query_sub('SELECT totalactiontime FROM ^users WHERE userid = #', $userid));
$userpoints = qa_db_read_one_assoc(qa_db_query_sub('SELECT * FROM ^userpoints WHERE userid = #', $userid));



// Prepare and return content for theme

$qa_content = qa_content_prepare(true);

$qa_content['title'] = '徽章详情';

if (!empty($badgeInfo)) {
    $qa_content['custom'] = '';
    foreach ($badgeInfo as $key => $value) {
        $reach_count = get_reach_count($value['id'], $userpoints, $useraccount);
        $percentage = min(100, (int)$reach_count*100/$value['level_3']);
        if ($value['id'] == 7) {
            $qa_content['custom'] = $qa_content['custom'] . '<details>
<summary class="qa-badge-list"><div class="g-progress" style="--progress: '.$percentage.'%">'. $value['name'] . '</div></summary>
<ol class="qa-badge-list-item">获取条件: ' . $value['description'] . '</ol>
<ol class="qa-badge-list-item">各级所需在线分钟数: ' . $value['level_1'] . '/' . $value['level_2'] . '/' . $value['level_3'] . '</ol>
<ol class="qa-badge-list-item">已在线分钟数: '. (int)$reach_count .'<div class="g-progress" style="--progress: '.$percentage.'%"></div></ol>
</details>';
        } else {
            $qa_content['custom'] = $qa_content['custom'] . '<details>
<summary class="qa-badge-list"><div class="g-progress" style="--progress: '.$percentage.'%">'. $value['name'] . '</div></summary>
<ol class="qa-badge-list-item">获取条件: ' . $value['description'] . '</ol>
<ol class="qa-badge-list-item">各级所需次数: ' . $value['level_1'] . '/' . $value['level_2'] . '/' . $value['level_3'] . '</ol>
<ol class="qa-badge-list-item">达成次数: '. $reach_count .'<div class="g-progress" style="--progress: '. $percentage .'%"></div></ol>
</details>';
        }
    }
}

// Sub navigation for account pages and suggestion


$qa_content['navigation']['sub'] = qa_user_sub_navigation(qa_get_logged_in_handle(), 'badge', true);


return $qa_content;
