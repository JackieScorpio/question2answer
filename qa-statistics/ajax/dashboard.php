<?php
require_once '../../qa-config.php';

$conn = new mysqli(QA_MYSQL_HOSTNAME, QA_MYSQL_USERNAME, QA_MYSQL_PASSWORD, QA_MYSQL_DATABASE);

if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

$data = array();

// overview
$result = $conn->query("select count(*) qcount, sum(clicktimes) vcount from qa_posts where type = 'Q'") -> fetch_row();
$data['qcount'] = $result[0];
$data['vcount'] = $result[1];
$result = $conn->query("SELECT count(*) FROM qa_posts WHERE type = 'A'")->fetch_row();
$data['acount'] = $result[0];
$result = $conn->query("SELECT count(*) FROM qa_users")->fetch_row();
$data['ucount'] = $result[0];

// chart
// left chart
$qcount = array();
$weekdate = array();
$ccount = array();
$acount = array();
$maxcount = 0;

for ($i = 6; $i >= 0; $i--) {
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
    $weekdate[] = $date;
}
$data['chart1']['qcount'] = $qcount;
$data['chart1']['ccount'] = $ccount;
$data['chart1']['acount'] = $acount;
$data['chart1']['weekdate'] = $weekdate;
$data['chart1']['maxcount'] = $maxcount;

// right chart
$qcount = array();
$ccount = array();
$acount = array();
$total = array();
$topvalue = 0;
$topindex = 0;
for ($i = 35; $i >= 0; $i--) {
    $date = date('m-d H:i', strtotime("-$i hour"));
    $result = $conn->query("SELECT count(*) FROM qa_posts 
                WHERE type = 'Q' AND created >= DATE_SUB(NOW(), INTERVAL $i HOUR) 
                  AND created < DATE_SUB(NOW(), INTERVAL $i-1 HOUR)")->fetch_row();
    $qcount[] = $result[0];
    $maxcount = max($maxcount, $result[0]);

    $result = $conn->query("SELECT count(*) FROM qa_posts 
                WHERE type = 'A' AND created >= DATE_SUB(NOW(), INTERVAL $i HOUR) 
                  AND created < DATE_SUB(NOW(), INTERVAL $i-1 HOUR)")->fetch_row();
    $acount[] = $result[0];
    $maxcount = max($maxcount, $result[0]);

    $result = $conn->query("SELECT count(*) FROM qa_posts 
                WHERE type = 'C' AND created >= DATE_SUB(NOW(), INTERVAL $i HOUR) 
                  AND created < DATE_SUB(NOW(), INTERVAL $i-1 HOUR)")->fetch_row();
    $ccount[] = $result[0];
    $total[] = $qcount[35-$i] + $acount[35-$i] + $ccount[35-$i];

    if ($topvalue <= $total[35-$i]) {
        $topvalue = $total[35-$i];
        $topindex = 35-$i;
    }

}
$data['chart2']['qcount'] = $qcount;
$data['chart2']['ccount'] = $ccount;
$data['chart2']['acount'] = $acount;
$data['chart2']['total'] = $total;
$data['chart2']['maxcount'] = $topvalue;
$data['chart2']['topvaluetext'] = $topvalue . ' (提问:' . $qcount[$topindex] . ' 回答:' . $acount[$topindex] . ' 评论:' . $ccount[$topindex] . ')';

// login number
$logincount = array();
$weeklogindate = array();
$maxlogincount = 0;

for ($i = 6; $i >= 0; $i--) {
    $date = date('m-d', strtotime("-$i day"));

    $next = $i - 1;
    $date1 = date('y-m-d', strtotime("-$i day"));
    $date2 = date('y-m-d', strtotime("-$next day"));
    $date1 .= ' 00:00:00"';
    $date2 .= ' 00:00:00"';
    $date1 = '"20'.$date1;
    $date2 = '"20'.$date2;

    $result = $conn->query('SELECT count(*) FROM qa_eventlog
                WHERE event = \'u_login\' AND datetime >= '.$date1.'
                  AND datetime < '.$date2)->fetch_row();
    $logincount[] = $result[0];
    $maxlogincount = max($maxlogincount, $result[0]);
    $weeklogindate[] = $date;
}
$data['chartlogin']['logincount'] = $logincount;
$data['chartlogin']['weekdate'] = $weeklogindate;
$data['chartlogin']['maxcount'] = $maxlogincount;

// table
$result = $conn->query("SELECT qa_users.handle, qa_users.realname, 
       qa_users.email, qa_userpoints.points, qa_userpoints.qposts, 
       qa_userpoints.aposts, qa_users.totalactiontime, qa_users.lastactiontime
FROM qa_users INNER JOIN qa_userpoints ON qa_users.userid = qa_userpoints.userid");
$users = array();
while ($row = $result->fetch_assoc()) {
    $row['lastactiontime'] = date('m-d H:i:s', $row['lastactiontime']);
    $row['totalactiontime'] = $row['totalactiontime'] == null? 0 : (int)($row['totalactiontime']/60);
    $users[] = $row;
}
$data['users'] = $users;
echo json_encode($data);