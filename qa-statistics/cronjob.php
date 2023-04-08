<?php
require_once '../qa-config.php';

$conn = new mysqli(QA_MYSQL_HOSTNAME, QA_MYSQL_USERNAME, QA_MYSQL_PASSWORD, QA_MYSQL_DATABASE);

if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

$users = $conn->query("SELECT * FROM qa_users");
$badgeinfo = $conn->query("SELECT * FROM qa_badge");
$badges = array();
while ($badge = $badgeinfo->fetch_assoc()) {
    $badges[] = $badge;
}
while ($user = $users->fetch_assoc()) {
    $userid = $user['userid'];
    $userpoints = $conn->query('SELECT * FROM qa_userpoints WHERE userid = ' . $userid)->fetch_assoc();
    foreach ($badges as $badge) {
        $count = get_reach_count($badge['id'], $userpoints, $user, $conn);
        $level = 0;
        if ($count >= $badge['level_3']) $level = 3;
        else if ($count >= $badge['level_2']) $level = 2;
        else if ($count >= $badge['level_1']) $level = 1;
        echo $userid . " " . $badge['id'] . " " . $count . " " . $level . "\n";
        for($i = 1; $i <= $level; ++$i) {
            $info = $conn -> query('SELECT * FROM qa_userbadge WHERE userid =' . $userid . ' AND badgeid = ' . $badge['id'] . ' AND level = ' . $level);
            if ($info -> num_rows == 0) {
                $stmt = $conn->prepare("INSERT INTO qa_userbadge (userid, badgeid, level, reachtime) VALUES (?, ?, ?, NOW())");
                $stmt->bind_param("sss", $p_userid, $p_badgeid, $p_level);
                $p_userid = $userid;
                $p_badgeid = $badge['id'];
                $p_level = $i;
                $stmt->execute();
            }
        }
    }
}


function get_reach_count($id, $userpoints, $useraccount, $conn) {
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
        $result = $conn -> query('SELECT sum(clicktimes) FROM qa_posts WHERE userid = ' . $userpoints['userid'] . ' AND type = \'Q\'')->fetch_row();
        $number = $result[0];
    } elseif ($id == 10) {
        // 登录天数
        $number = (int)$useraccount['logindays'];
    }
    return $number;
}