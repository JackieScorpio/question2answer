<?php
    $currentUsername = $_POST['currentUsername'];
    $sessioncode = 0;

    // Peer Review 系统的用户名是学号，假定q2a的学生昵称也是学号，根据学号去q2a的数据库查sessioncode
    // qa_db_read_one_value 传入第二个参数，查不到数据的时候返回null
    $res = qa_db_read_one_value(qa_db_query_sub(
        "SELECT sessioncode FROM qa_users WHERE handle=#",
        $currentUsername
    ), true);

    if ($res) {
        $sessioncode = $res;
    }

    // 这里echo的值是后端php返回给前端js的值，貌似不能返回字符串,可以用json_encode返回json对象
    $response = array('sessioncode' => $sessioncode);
    
    echo json_encode($response);
