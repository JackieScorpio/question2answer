<?php
require_once '../../qa-config.php';

$conn = new mysqli(QA_MYSQL_HOSTNAME, QA_MYSQL_USERNAME, QA_MYSQL_PASSWORD, QA_MYSQL_DATABASE);

if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

$data = array();

// table
$result = $conn->query("SELECT qa_users.handle, qa_users.realname, 
         qa_userpoints.qposts, qa_userpoints.aposts, qa_userpoints.cposts,
         qa_userpoints.aselecteds, qa_userpoints.qupvotes, qa_userpoints.aupvotes,
         qa_userpoints.qdownvotes, qa_userpoints.adownvotes, qa_userpoints.upvoteds,
         qa_users.totalactiontime, qa_users.lastactiontime, qa_users.logindays
FROM qa_users INNER JOIN qa_userpoints ON qa_users.userid = qa_userpoints.userid");
$ausers = array();
while ($row = $result->fetch_assoc()) {
    $row['lastactiontime'] = date('m-d H:i:s', $row['lastactiontime']);
    $row['totalactiontime'] = $row['totalactiontime'] == null? 0 : (int)($row['totalactiontime']/60);
    $row['totalvotes'] = $row['qupvotes'] + $row['aupvotes'] + $row['qdownvotes'] + $row['adownvotes'];

    $ausers[] = $row;
}

$data['users'] = $ausers;


$users = $conn->query("SELECT * FROM qa_users");
$badgeinfo = $conn->query("SELECT * FROM qa_badge");
// game
// 徽章相关
$userids = array();

// 用户各级徽章数量
$badge1 = array();
$badge2 = array();
$badge3 = array();

// 各级徽章获取总数
$totalBadge1 = 0;
$totalBadge2 = 0;
$totalBadge3 = 0;

// 各级徽章名称和类别
$badgeCat = array();
$badgeCat1 = array();
$badgeCat2 = array();
$badgeCat3 = array();
$badgeCatD = array();

// 各级徽章获取数量和各类别获取数量
$badgeCat1count = array();
$badgeCat2count = array();
$badgeCat3count = array();
$badgeCatDcount = array();

$badges = array();
while ($badge = $badgeinfo->fetch_assoc()) {
    $badges[] = $badge;
}

foreach ($badges as $badge) {
    $description = array();
    $description[$badge['name1']] = 0;
    $description[$badge['name2']] = 0;
    $description[$badge['name3']] = 0;
    $badgeCat[$badge['description']] = $description;
    $badgeCat1[] = $badge['name1'];
    $badgeCat2[] = $badge['name2'];
    $badgeCat3[] = $badge['name3'];
    $badgeCatD[] = $badge['description'];
}

$games = array();
while ($user = $users->fetch_assoc()) {
    $userid = $user['userid'];
    $userpoints = $conn->query('SELECT * FROM qa_userpoints WHERE userid = ' . $userid)->fetch_assoc();
    $b1 = 0;
    $b2 = 0;
    $b3 = 0;
    foreach ($badges as $badge) {
        $count = get_reach_count($badge['id'], $userpoints, $user, $conn);
        $level = 0;
        if ($count >= $badge['level_3']) {
            $b3++;
            $b2++;
            $b1++;
            $totalBadge3++;
            $totalBadge2++;
            $totalBadge1++;
            $badgeCat[$badge['description']][$badge['name3']]++;
            $badgeCat[$badge['description']][$badge['name2']]++;
            $badgeCat[$badge['description']][$badge['name1']]++;
        }
        else if ($count >= $badge['level_2']) {
            $b2++;
            $b1++;
            $totalBadge2++;
            $totalBadge1++;
            $badgeCat[$badge['description']][$badge['name2']]++;
            $badgeCat[$badge['description']][$badge['name1']]++;
        }
        else if ($count >= $badge['level_1']) {
            $b1++;
            $totalBadge1++;
            $badgeCat[$badge['description']][$badge['name1']]++;
        }
    }
    $badge1[] = $b1;
    $badge2[] = $b2;
    $badge3[] = $b3;
    $userids[] = $userid;

    $result = $conn->query("SELECT count(*) FROM qa_taskfinish WHERE user_id = $userid")->fetch_row();
    $taskcount = $result[0];

    // TODO 更改问答挑战id为6
    $result = $conn->query("SELECT count(*) FROM qa_posts WHERE type = 'A' AND catidpath1 = 6 AND userid = $userid")->fetch_row();
    $challengecount = $result[0];

    $game = array();
    $game['userid'] = $userid;
    $game['name'] = $user['handle'];
    $game['realname'] = $user['realname'];
    $game['badge1'] = $b1;
    $game['badge2'] = $b2;
    $game['badge3'] = $b3;

    $game['badge'] = $game['badge1'] + $game['badge2'] + $game['badge3'];
    $game['badge'] .= '(' . $game['badge1'] . ',' . $game['badge2'] . ',' . $game['badge3'] . ')';

    $game['badgeUrl'] = '../../../index.php?qa=user&qa_1=' . $user['handle'] . '&qa_2=badge';

    $game['task'] = $taskcount;

    $game['challenge'] = $challengecount;

    $games[] = $game;
}

$badgeCat['cat1'] = $badgeCat1;
$badgeCat['cat2'] = $badgeCat2;
$badgeCat['cat3'] = $badgeCat3;
$badgeCat['catD'] = $badgeCatD;

foreach ($badges as $badge) {
    $level1count = $badgeCat[$badge['description']][$badge['name1']];
    $level2count = $badgeCat[$badge['description']][$badge['name2']];
    $level3count = $badgeCat[$badge['description']][$badge['name3']];

    $badgeCat1count[] = $level1count;
    $badgeCat2count[] = $level2count;
    $badgeCat3count[] = $level3count;
    $badgeCatDcount[] = $level1count + $level2count + $level3count;
}

$badgeCat['cat1count'] = $badgeCat1count;
$badgeCat['cat2count'] = $badgeCat2count;
$badgeCat['cat3count'] = $badgeCat3count;
$badgeCat['catDcount'] = $badgeCatDcount;


$data['gamedata'] = $games;

// 任务
$taskdata = array();
$tasks = $conn->query("SELECT * FROM qa_task");
while ($task = $tasks->fetch_assoc()) {
    $taskid = $task['id'];

    $task['description'] = str_replace('?', $task['count'], $task['description']);

    $result = $conn->query("SELECT count(*) FROM qa_taskfinish WHERE task_id = $taskid")->fetch_row();
    $taskcount = $result[0];

    $task['finish'] = $taskcount;

    $taskdata[] = $task;
}
$data['taskdata'] = $taskdata;

echo json_encode($data);

function get_reach_count($id, $userpoints, $useraccount, $conn)
{
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
        $result = $conn->query('SELECT count(*) from qa_posts where userid = ' . $useraccount['userid'] . ' AND postid in (
SELECT MIN(postid) AS first_answer_id
FROM qa_posts
WHERE type = \'A\'
GROUP BY parentid)')->fetch_row();
        $number = $result[0];
    } elseif ($id == 9) {
        // 问题被点击数
        $result = $conn->query('SELECT sum(clicktimes) FROM qa_posts WHERE userid = ' . $userpoints['userid'] . ' AND type = \'Q\'')->fetch_row();
        $number = $result[0];
    } elseif ($id == 10) {
        // 登录天数
        $number = (int)$useraccount['logindays'];
    }
    return $number;
}