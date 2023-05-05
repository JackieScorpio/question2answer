<?php

require_once '../../qa-config.php';

$conn = new mysqli(QA_MYSQL_HOSTNAME, QA_MYSQL_USERNAME, QA_MYSQL_PASSWORD, QA_MYSQL_DATABASE);

if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

$users = $conn->query("SELECT * FROM qa_users");
$badgeinfo = $conn->query("SELECT * FROM qa_badge");

$data = array();


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

$data['badgecount']['ids'] = $userids;
$data['badgecount']['badge1'] = $badge1;
$data['badgecount']['badge2'] = $badge2;
$data['badgecount']['badge3'] = $badge3;
$data['badgecount']['totalbadge1'] = $totalBadge1;
$data['badgecount']['totalbadge2'] = $totalBadge2;
$data['badgecount']['totalbadge3'] = $totalBadge3;
$data['badgeCat'] = $badgeCat;

$data['users']['ids'] = $userids;
$data['users']['idlength'] = count($userids);

// 任务系统
// 用户完成任务数
$result = $conn->query("SELECT user_id, count(*) finish FROM qa_taskfinish group by user_id");
$taskFinish = array();
while ($row = $result->fetch_assoc()) {
    $taskFinish[$row['user_id']] = $row['finish'];
}

$user_task_finish_count = array();
foreach ($userids as $userid) {
    $user_task_finish_count[] = $taskFinish[$userid] == null ? 0 : (int)$taskFinish[$userid];
}

$data['task']['userFinishCount'] = $user_task_finish_count;

//任务完成人数
$result = $conn->query("SELECT task_id, count(*) finish FROM qa_taskfinish group by task_id");

$allTasks = $conn->query("SELECT id FROM qa_task");

$taskids = array();
$taskFinish = array();
$taskFinish1 = array();
$taskN = 0;

while ($row = $allTasks->fetch_assoc()) {
    $taskFinish1[(int)$row['id']] = 0;
    $taskids[] = (int)$row['id'];
    $taskN++;
}

while ($row = $result->fetch_assoc()) {
    $taskFinish1[(int)$row['task_id']] = $row['finish'];
}


sort($taskids);
foreach ($taskids as $taskid) {
    $taskFinish[] = $taskFinish1[$taskid];
}

$data['task']['taskFinishids'] = $taskids;
$data['task']['taskFinishCount'] = $taskFinish;
$data['task']['taskCount'] = $taskN;

//问答挑战
// TODO 需要改为问答挑战的id。
$result = $conn->query("SELECT count(*) FROM qa_posts WHERE type = 'Q' AND catidpath1 = 1")->fetch_row();
$challenge_question = $result[0];

// TODO 需要改为问答挑战的id。
$result = $conn->query("SELECT count(*) FROM qa_posts WHERE type = 'A' AND catidpath1 = 1")->fetch_row();
$challenge_answer = $result[0];

$data['challenge']['question'] = (int)$challenge_question;
$data['challenge']['answer'] = (int)$challenge_answer;


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