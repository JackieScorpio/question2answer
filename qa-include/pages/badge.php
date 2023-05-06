<?php

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


// Check that we're logged in

$userid = qa_get_logged_in_userid();

if (!isset($userid))
    qa_redirect('login');


$badgeInfo = qa_db_read_all_assoc(qa_db_query_sub(
    'SELECT * FROM ^badge'));

$useraccount = qa_db_read_one_assoc(qa_db_query_sub(
    'SELECT totalactiontime FROM ^users WHERE userid = #', $userid));

$userpoints = qa_db_read_one_assoc(qa_db_query_sub(
    'SELECT * FROM ^userpoints WHERE userid = #', $userid));


// Prepare and return content for theme

$qa_content = qa_content_prepare(true);

$qa_content['title'] = '徽章详情';

if (!empty($badgeInfo)) {
    $qa_content['custom'] = '';
    foreach ($badgeInfo as $key => $value) {
        $reach_count = get_reach_count($value['id'], $userpoints, $useraccount);

        $grayscale1 = $reach_count >= $value['level_1'] ? '' : ' style="filter:grayscale(100%)" ';
        $grayscale2 = $reach_count >= $value['level_2'] ? '' : ' style="filter:grayscale(100%)" ';
        $grayscale3 = $reach_count >= $value['level_3'] ? '' : ' style="filter:grayscale(100%)" ';

        if ($value['id'] == 7) {
            $qa_content['custom'] .= '<div class="badge-container">
		<div class="badge">
			<img src="./qa-theme/general/qa-badge.png"' . $grayscale1 . 'alt="Badge 1" title = "所需在线分钟数:' . $value['level_1'] . '">
			<h2>'. $value['name1'] . '</h2>
			<p>' . $value['description'] . '</p>
		</div>
		<div class="badge">
			<img src="./qa-theme/general/qa-badge.png"' . $grayscale2 . 'alt="Badge 2" title = "所需在线分钟数:' . $value['level_2'] . '">
			<h2>'. $value['name2'] . '</h2>
			<p>' . $value['description'] . '</p>
		</div>
		<div class="badge">
			<img src="./qa-theme/general/qa-badge.png"' . $grayscale3 . 'alt="Badge 3" title = "所需在线分钟数:' . $value['level_3'] . '">
			<h2>'. $value['name3'] . '</h2>
			<p>' . $value['description'] . '</p>
		</div>
	</div>
	<hr/>' ;
        } else {
            $qa_content['custom'] .= '<div class="badge-container">
		<div class="badge">
			<img src="./qa-theme/general/qa-badge.png"' . $grayscale1 . 'alt="Badge 1" title = "所需次数:' . $value['level_1'] . '">
			<h2>'. $value['name1'] . '</h2>
			<p>' . $value['description'] . '</p>
		</div>
		<div class="badge">
			<img src="./qa-theme/general/qa-badge.png"' . $grayscale2 . 'alt="Badge 2" title = "所需次数:' . $value['level_2'] . '">
			<h2>'. $value['name2'] . '</h2>
			<p>' . $value['description'] . '</p>
		</div>
		<div class="badge">
			<img src="./qa-theme/general/qa-badge.png"' . $grayscale3 . 'alt="Badge 3" title = "所需次数:' . $value['level_3'] . '">
			<h2>'. $value['name3'] . '</h2>
			<p>' . $value['description'] . '</p>
		</div>
	</div>
	<hr/>' ;
        }
    }
}


// Sub navigation for account pages and suggestion


$qa_content['navigation']['sub'] = qa_user_sub_navigation(qa_get_logged_in_handle(), 'badge', true);


return $qa_content;
