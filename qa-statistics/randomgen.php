<?php
require_once '../qa-config.php';

$conn = new mysqli(QA_MYSQL_HOSTNAME, QA_MYSQL_USERNAME, QA_MYSQL_PASSWORD, QA_MYSQL_DATABASE);

if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}
/*
 * insert into qa_posts (type, parentid, categoryid, catidpath1, catidpath2, catidpath3, acount, amaxvote, selchildid, closedbyid, userid, cookieid, createip, lastuserid, lastip, upvotes, downvotes, netvotes, lastviewip, views, hotness, flagcount, format, created, updated, updatetype, title, content, tags, name, notify, clicktimes)
values  ('Q', null, 2, 2, null, null, 1, 0, null, null, 1, null, 0x00000000000000000000000000000001, null, null, 0, 0, 0, 0x00000000000000000000000000000001, 1, 83404898000, 0, '', '2023-03-19 17:26:03', null, null, '随机生成的题目2', '随机生成的文本', '', null, null, 5),
        ('A', 36, 2, 2, null, null, 0, 0, null, null, 1, null, 0x00000000000000000000000000000001, null, null, 0, 0, 0, null, 0, null, 0, '', '2023-03-19 17:26:19', null, null, null, '随机随机随机随机生成的答案2', null, null, null, 0),
        ('C', 37, 2, 2, null, null, 0, 0, null, null, 1, null, 0x00000000000000000000000000000001, null, null, 0, 0, 0, null, 0, null, 0, '', '2023-03-19 17:26:30', null, null, null, '随机随机随机随机生成的评论2', null, null, null, 0);
 */

for ($i = 502; $i <= 1000;) {
    $stmt = $conn->prepare("insert into qa_posts (type, parentid, categoryid, catidpath1, catidpath2, catidpath3, acount, amaxvote, selchildid, closedbyid, userid, cookieid, createip, lastuserid, lastip, upvotes, downvotes, netvotes, lastviewip, views, hotness, flagcount, format, created, updated, updatetype, title, content, tags, name, notify, clicktimes)
values  ('Q', null, 2, 2, null, null, 1, 0, null, null, ?, null, 0x00000000000000000000000000000001, null, null, 0, 0, 0, 0x00000000000000000000000000000001, 1, 83404898000, 0, '', ?, null, null, ?, '随机生成的文本', '', null, null, 2)");
    $stmt->bind_param("iss", $userid, $created, $title);
    // 设置参数并执行
    $userid = rand(1, 4);
    $random_time = time() - rand(0, 7*24*3600);
    $created = date('Y-m-d H:i:s', $random_time);
    $title = '随机生成的题目' . $i;
    $stmt->execute();
    $i++;

    $stmt = $conn->prepare("insert into qa_posts (type, parentid, categoryid, catidpath1, catidpath2, catidpath3, acount, amaxvote, selchildid, closedbyid, userid, cookieid, createip, lastuserid, lastip, upvotes, downvotes, netvotes, lastviewip, views, hotness, flagcount, format, created, updated, updatetype, title, content, tags, name, notify, clicktimes)
values  ('A', ?, 2, 2, null, null, 0, 0, null, null, ?, null, 0x00000000000000000000000000000001, null, null, 0, 0, 0, null, 0, null, 0, '', ?, null, null, null, '随机随机随机随机生成的答案2', null, null, null, 0)");
    $stmt->bind_param("iis", $parentid, $userid, $created);
    // 设置参数并执行
    $parentid = $i;
    $userid = rand(1, 4);
    $random_time = max($random_time + 1, time() - rand(0, 7*24*3600));
    $created = date('Y-m-d H:i:s', $random_time);
    $stmt->execute();
    $i++;
    if ($i%2 == 0) {
        $stmt = $conn->prepare("insert into qa_posts (type, parentid, categoryid, catidpath1, catidpath2, catidpath3, acount, amaxvote, selchildid, closedbyid, userid, cookieid, createip, lastuserid, lastip, upvotes, downvotes, netvotes, lastviewip, views, hotness, flagcount, format, created, updated, updatetype, title, content, tags, name, notify, clicktimes)
values  ('A', ?, 2, 2, null, null, 0, 0, null, null, ?, null, 0x00000000000000000000000000000001, null, null, 0, 0, 0, null, 0, null, 0, '', ?, null, null, null, '随机随机随机随机生成的答案2', null, null, null, 0)");
        $stmt->bind_param("iis", $parentid, $userid, $created);
        // 设置参数并执行
        $parentid = $i;
        $userid = rand(1, 4);
        $random_time = max($random_time + 1, time() - rand(0, 36*3600));
        $created = date('Y-m-d H:i:s', $random_time);
        $stmt->execute();
        $i++;
    }


    $stmt = $conn->prepare("insert into qa_posts (type, parentid, categoryid, catidpath1, catidpath2, catidpath3, acount, amaxvote, selchildid, closedbyid, userid, cookieid, createip, lastuserid, lastip, upvotes, downvotes, netvotes, lastviewip, views, hotness, flagcount, format, created, updated, updatetype, title, content, tags, name, notify, clicktimes)
values  ('C', ?, 2, 2, null, null, 0, 0, null, null, ?, null, 0x00000000000000000000000000000001, null, null, 0, 0, 0, null, 0, null, 0, '', ?, null, null, null, '随机随机随机随机生成的答案2', null, null, null, 0)");
    $stmt->bind_param("iis", $parentid, $userid, $created);
    // 设置参数并执行
    $parentid = $i;
    $userid = rand(1, 4);
    $random_time = max($random_time + 1, time() - rand(0, 7*24*3600));
    $created = date('Y-m-d H:i:s', $random_time);
    $stmt->execute();
    $i++;
    if ($i%2 == 0) {
        $stmt = $conn->prepare("insert into qa_posts (type, parentid, categoryid, catidpath1, catidpath2, catidpath3, acount, amaxvote, selchildid, closedbyid, userid, cookieid, createip, lastuserid, lastip, upvotes, downvotes, netvotes, lastviewip, views, hotness, flagcount, format, created, updated, updatetype, title, content, tags, name, notify, clicktimes)
values  ('C', ?, 2, 2, null, null, 0, 0, null, null, ?, null, 0x00000000000000000000000000000001, null, null, 0, 0, 0, null, 0, null, 0, '', ?, null, null, null, '随机随机随机随机生成的答案2', null, null, null, 0)");
        $stmt->bind_param("iis", $parentid, $userid, $created);
        // 设置参数并执行
        $parentid = $i - 1;
        $userid = rand(1, 4);
        $random_time = max($random_time + 1, time() - rand(0, 36*3600));
        $created = date('Y-m-d H:i:s', $random_time);
        $stmt->execute();
        $i++;
    }
}
//
//
//
//
//
