<?php

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

if (isset($_POST['search_user_stat'])) {
    $search = '%'.$_POST['search'].'%';
    $useraccount = qa_db_read_all_assoc(qa_db_query_sub(
        'SELECT * FROM ^users WHERE handle like # OR realname like #', $search, $search));

    $response = array();
    $response['userStat'] = array();
    if (empty($useraccount)) {
        echo json_encode($response);
    }
    else {
        $userids = '(';
        $useridarray = array();
        foreach ($useraccount as $user) {
            $useridarray[] = $user['userid'];
        }
        $userids .= join(',', $useridarray);
        $userids .= ')';

        $userpoint = qa_db_read_all_assoc(qa_db_query_sub(
            'SELECT * FROM ^userpoints WHERE userid in ' . $userids));

        $usersaccounts = array();
        foreach ($useraccount as $user) {
            $usersaccounts[$user['userid']] = $user;
        }

        $userpoints = array();
        foreach ($userpoint as $userp) {
            $userpoints[$userp['userid']] = $userp;
        }

        foreach ($usersaccounts as $key => $user) {
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
            $oneuser = array();
            $oneuser['username'] = $username;
            $oneuser['userrealname'] = $userrealname;
            $oneuser['col1'] = $col1;
            $oneuser['col2'] = $col2;
            $oneuser['col3'] = $col3;
            $oneuser['col4'] = $col4;
            $oneuser['col5'] = $col5;
            $oneuser['col6'] = $col6;
            $oneuser['col7'] = (int)$col7;
            $oneuser['col8'] = $col8;
            $oneuser['col10'] = $col10;
            $response['userStat'][] = $oneuser;
        }
        echo json_encode($response);
    }
}

if (isset($_POST['reset_user_stat'])) {
    $useraccount = qa_db_read_all_assoc(qa_db_query_sub(
        'SELECT * FROM ^users'));

    $response = array();
    $response['userStat'] = array();
    if (empty($useraccount)) {
        echo json_encode($response);
    }
    else {
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

        foreach ($usersaccounts as $key => $user) {
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
            $oneuser = array();
            $oneuser['username'] = $username;
            $oneuser['userrealname'] = $userrealname;
            $oneuser['col1'] = $col1;
            $oneuser['col2'] = $col2;
            $oneuser['col3'] = $col3;
            $oneuser['col4'] = $col4;
            $oneuser['col5'] = $col5;
            $oneuser['col6'] = $col6;
            $oneuser['col7'] = (int)$col7;
            $oneuser['col8'] = $col8;
            $oneuser['col10'] = $col10;
            $response['userStat'][] = $oneuser;
        }
        echo json_encode($response);
    }
}