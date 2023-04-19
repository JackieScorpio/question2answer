<?php
require_once '../../qa-config.php';

$conn = new mysqli(QA_MYSQL_HOSTNAME, QA_MYSQL_USERNAME, QA_MYSQL_PASSWORD, QA_MYSQL_DATABASE);

if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

$data = array();

$startDate = $_POST['startDate'];
$endDate = $_POST['endDate'];

if ($startDate == null || strlen($startDate) == 0) {
    $startDate = "2023-02-19";
}
if ($endDate == null || strlen($endDate) == 0) {
    $endDate = date("Y-m-d");
}

$data['startDate'] = $startDate;
$data['endDate'] = $endDate;

// 基本数据表
$date_1 = $endDate;
$date_2= $startDate;
$date1_arr = explode("-",$date_1);
$date2_arr = explode("-",$date_2);
$day1 = mktime(0,0,0,$date1_arr[1],$date1_arr[2],$date1_arr[0]);
$day2 = mktime(0,0,0,$date2_arr[1],$date2_arr[2],$date2_arr[0]);
$daysdiff = round(($day1 - $day2)/3600/24);

$now = date("Y-m-d");
$date3_arr = explode("-",$now);
$day3 = mktime(0,0,0,$date3_arr[1],$date3_arr[2],$date3_arr[0]);
$startDiff = round(($day3 - $day1)/3600/24);

$qcount = array();
$totaldate = array();
$ccount = array();
$acount = array();
$maxcount = 0;
for ($i = $daysdiff + $startDiff; $i >= $startDiff; $i--) {
    $date = date('m-d', strtotime("-$i day"));

    $next = $i - 1;
    $date1 = date('y-m-d', strtotime("-$i day"));
    $date2 = date('y-m-d', strtotime("-$next day"));
    $date1 .= ' 00:00:00"';
    $date2 .= ' 00:00:00"';
    $date1 = '"20'.$date1;
    $date2 = '"20'.$date2;

    $result = $conn->query('SELECT count(*) FROM qa_posts
                WHERE type = \'Q\' AND created >= '.$date1.'
                  AND created < '.$date2)->fetch_row();
    $qcount[] = $result[0];
    $maxcount = max($maxcount, $result[0]);
    $result = $conn->query('SELECT count(*) FROM qa_posts
                WHERE type = \'A\' AND created >= '.$date1.'
                  AND created < '.$date2)->fetch_row();
    $acount[] = $result[0];
    $maxcount = max($maxcount, $result[0]);
    $result = $conn->query('SELECT count(*) FROM qa_posts
                WHERE type = \'C\' AND created >= '.$date1.'
                  AND created < '.$date2)->fetch_row();
    $ccount[] = $result[0];
    $maxcount = max($maxcount, $result[0]);
    $totaldate[] = $date;
}

$data['baseChart']['qcount'] = $qcount;
$data['baseChart']['ccount'] = $ccount;
$data['baseChart']['acount'] = $acount;
$data['baseChart']['totaldate'] = $totaldate;
$data['baseChart']['maxcount'] = $maxcount;

// 用户基本数据表
$result = $conn->query("SELECT qa_users.userid, qa_users.handle, qa_users.realname, qa_users.email, 
       qa_userpoints.points, qa_userpoints.qposts, qa_userpoints.aposts, 
       qa_users.totalactiontime, qa_users.lastactiontime
FROM qa_users INNER JOIN qa_userpoints ON qa_users.userid = qa_userpoints.userid");

$users = array();
$userids = array();
$userqcount = array();
$useracount = array();
while ($row = $result->fetch_assoc()) {
    $row['lastactiontime'] = date('m-d H:i:s', $row['lastactiontime']);
    $row['totalactiontime'] = $row['totalactiontime'] == null? 0 : (int)($row['totalactiontime']/60);
    $users[] = $row;
    $userids[] = $row['userid'];
    $useracount[] = $row['aposts'];
    $userqcount[] = $row['qposts'];
}
$data['users'] = array();
$data['users']['data'] = $users;
$data['users']['ids'] = $userids;
$data['users']['idlength'] = count($userids);
$data['users']['qcounts'] = $userqcount;
$data['users']['acounts'] = $useracount;

// 饼图
// 提问数已回答数、提问未回答数
$result = $conn->query("SELECT count(*) FROM qa_posts WHERE type = 'Q'")->fetch_row();
$totalquestion = $result[0];
$data['pie4']['question'] = $totalquestion;

$result = $conn->query("SELECT count(*) FROM (SELECT distinct parentid FROM qa_posts WHERE type = 'A') t")->fetch_row();
$question_with_answer = $result[0];

$data['pie1']['answered'] = $question_with_answer;
$data['pie1']['unanswered'] = $totalquestion - $question_with_answer;

// 问题已解决、问题未解决
$result = $conn->query("SELECT count(*) FROM qa_posts WHERE type = 'Q' AND selchildid is not null")->fetch_row();
$question_with_select = $result[0];

$data['pie2']['solved'] = $question_with_select;
$data['pie2']['unsolved'] = $totalquestion - $question_with_select;


//回答被投票、回答未被投票
$result = $conn->query("SELECT count(*) FROM qa_posts WHERE type = 'A'")->fetch_row();
$totalanswer = $result[0];
$data['pie4']['answer'] = $totalanswer;

$result = $conn->query("SELECT count(*) FROM qa_posts WHERE type = 'A' AND upvotes > 0")->fetch_row();
$answer_with_vote = $result[0];

$data['pie3']['voted'] = $answer_with_vote;
$data['pie3']['unvoted'] = $totalanswer - $answer_with_vote;

$result = $conn->query("SELECT count(*) FROM qa_posts WHERE type = 'C'")->fetch_row();
$data['pie4']['comment'] = $result[0];

// 24小时数据
$result = $conn->query("SELECT created FROM qa_posts WHERE type = 'Q'");
$hour_count_data = array();
for ($i = 0; $i <= 23; ++$i) {
    $hour_count_data[$i] = 0;
}
while ($row = $result->fetch_assoc()) {
    $hour = substr($row['created'], 11, 2);
    $hour_count_data[(int)$hour]++;
}
$data['timeBar']['question'] = $hour_count_data;

$result = $conn->query("SELECT created FROM qa_posts WHERE type = 'A'");
for ($i = 0; $i <= 23; ++$i) {
    $hour_count_data[$i] = 0;
}
while ($row = $result->fetch_assoc()) {
    $hour = substr($row['created'], 11, 2);
    $hour_count_data[(int)$hour]++;
}
$data['timeBar']['answer'] = $hour_count_data;

$result = $conn->query("SELECT created FROM qa_posts WHERE type = 'C'");
for ($i = 0; $i <= 23; ++$i) {
    $hour_count_data[$i] = 0;
}
while ($row = $result->fetch_assoc()) {
    $hour = substr($row['created'], 11, 2);
    $hour_count_data[(int)$hour]++;
}
$data['timeBar']['comment'] = $hour_count_data;


echo json_encode($data);