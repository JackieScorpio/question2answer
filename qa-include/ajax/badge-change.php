<?php

if (isset($_POST['delete_badge'])) {
    $badge_id = (int)$_POST['id'];
    $result = qa_db_query_sub('DELETE FROM ^badge WHERE id = #', $badge_id);
    echo '1';
}

if (isset($_POST['delete_task'])) {
    $task_id = (int)$_POST['id'];
    $result = qa_db_query_sub('DELETE FROM ^task WHERE id = #', $task_id);
    echo '1';
}

if (isset($_POST['update_badge'])) {
    $id = (int)$_POST['id'];
    $name = ($_POST['name']);
    $name2 = ($_POST['name2']);
    $name3 = ($_POST['name3']);
    $level_1 = (int)$_POST['level1'];
    $level_2 = (int)$_POST['level2'];
    $level_3 = (int)$_POST['level3'];
    $description = ($_POST['description']);
    qa_db_query_sub(
        'UPDATE ^badge SET name1=$, name2=$, name3=$, level_1=$, level_2=$, level_3=$, description=$ WHERE id=$',
        $name, $name2, $name3, $level_1, $level_2, $level_3, $description, $id
    );
    echo '1';
}

if (isset($_POST['update_task'])) {
    $id = (int)$_POST['id'];
    $count = (int)$_POST['count'];
    $reward = (int)$_POST['reward'];
    qa_db_query_sub(
        'UPDATE ^task SET count=$, reward=$ WHERE id=$',
        $count, $reward, $id
    );
    echo '1';
}


