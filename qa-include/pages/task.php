<?php


if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
    header('Location: ../../');
    exit;
}

require_once QA_INCLUDE_DIR . 'db/selects.php';
require_once QA_INCLUDE_DIR . 'app/format.php';
require_once QA_INCLUDE_DIR . 'db/points.php';

/**
 * @param $cat
 * @param $user_id
 * @param $start
 * @param $end
 * @return string|null
 *
 * 获取该用户此任务的次数
 * 通过从eventlog中获取任务持续时间内触发该动作的条数
 */
function task_finished_count($cat, $user_id, $start, $end) {
    if ($cat == 'vote') {
        return qa_db_read_one_value(qa_db_query_sub(
            "SELECT COUNT(*) FROM ^eventlog WHERE userid = # AND datetime > # AND datetime < # AND event like #",
            $user_id, $start, $end, '%vote%'
        ));
    }
    return qa_db_read_one_value(qa_db_query_sub(
        "SELECT COUNT(*) FROM ^eventlog WHERE userid = # AND event = # AND datetime > # AND datetime < #",
        $user_id, $cat, $start, $end
    ));
}
function task_finished_time($cat, $user_id, $start, $end, $num) {
    if ($cat == 'vote') {
        $taskinfo = qa_db_read_all_assoc(qa_db_query_sub(
            "SELECT * FROM ^eventlog WHERE userid = # AND datetime > # AND datetime < # AND event like # order by datetime",
            $user_id, $start, $end, '%vote%'
        ));
        $index = 0;
        foreach ($taskinfo as $key => $value) {
            $index++;
            if ($index == $num) {
                return $value['datetime'];
            }
        }
    }
    else {
        $taskinfo = qa_db_read_all_assoc(qa_db_query_sub(
            "SELECT * FROM ^eventlog WHERE userid = # AND event = # AND datetime > # AND datetime < # order by datetime",
            $user_id, $cat, $start, $end
        ));
        $index = 0;
        foreach ($taskinfo as $key => $value) {
            $index++;
            if ($index == $num) {
                return $value['datetime'];
            }
        }
    }

}


// Check that we're logged in

$userid = qa_get_logged_in_userid();

if (!isset($userid))
    qa_redirect('login');


// 获取当前时间的所有任务
$taskInfo = qa_db_read_all_assoc(qa_db_query_sub(
    'SELECT 
                *
           FROM 
               ^task
           where now() >= started and now() <= ended 
          '
));

// Prepare and return content for theme

$qa_content = qa_content_prepare(true);

$qa_content['title'] = qa_lang_html('misc/my_task_title');

$qa_content['custom'] = '<div><h2>当前任务</h2>';
if (!empty($taskInfo)) {
    foreach ($taskInfo as $key => $value) {
        $taskdesc = str_replace('?', $value['count'], $value['description']);

        $finished = qa_db_read_one_value(qa_db_query_sub('SELECT count(*) FROM ^taskfinish where user_id = # and task_id = #', $userid, $value['id']));

        if ($finished > 0) {
            $qa_content['custom'] = $qa_content['custom'] . '<details>
<summary class="qa-task-list-finish">'. $taskdesc . '</summary>
<ol class="qa-task-list-item">截止时间: ' . $value['ended'] . '</ol>
<ol class="qa-task-list-item">完成奖励: ' . $value['reward'] . '积分</ol>
<ol class="qa-task-list-item">完成情况: 已完成</ol>
</details>';
        } else {
            $finished_count = task_finished_count($value['cat'], $userid, $value['started'], $value['ended']);
            if ($finished_count >= $value['count']) {
                $qa_content['custom'] = $qa_content['custom'] . '<details>
<summary class="qa-task-list-finish">'. $taskdesc . '</summary>
<ol class="qa-task-list-item">截止时间: ' . $value['ended'] . '</ol>
<ol class="qa-task-list-item">完成奖励: ' . $value['reward'] . '积分</ol>
<ol class="qa-task-list-item">完成情况: 已完成</ol>
</details>';
                // 插入完成记录
                qa_db_query_sub(
                    'INSERT INTO ^taskfinish (user_id, task_id) VALUES (#, #)',
                    $userid,  $value['id']
                );

                // 更新用户积分
                $userbonus = qa_db_read_one_value(qa_db_query_sub('SELECT bonus FROM ^userpoints WHERE userid = #', $userid));

                qa_db_points_set_bonus($userid, $userbonus + $value['reward']);
                qa_db_points_update_ifuser($userid, null);
            } else {
                $qa_content['custom'] = $qa_content['custom'] . '<details>
<summary class="qa-task-list">'. $taskdesc . '</summary>
<ol class="qa-task-list-item">截止时间: ' . $value['ended'] . '</ol>
<ol class="qa-task-list-item">完成奖励: ' . $value['reward'] . '积分</ol>
<ol class="qa-task-list-item">完成情况: ' . $finished_count . '/' . $value['count'] . '</ol>
</details>';
            }
        }

    }
}

// 已完成任务

// 获取用户的所有完成任务
$taskFinishInfo = qa_db_read_all_assoc(qa_db_query_sub(
    'SELECT 
                *
           FROM 
               ^taskfinish tf, ^task t
           where tf.user_id = # and tf.task_id = t.id
          ', $userid
));


$qa_content['custom'] .= '</div>
<div>
<h2>已完成任务</h2>';

if (!empty($taskFinishInfo)) {
    foreach ($taskFinishInfo as $key => $value) {
        $taskdesc = str_replace('?', $value['count'], $value['description']);

        $finished_time = task_finished_time($value['cat'], $userid, $value['started'], $value['ended'], $value['count']);

        $qa_content['custom'] = $qa_content['custom'] . '<details>
<summary class="qa-task-list-finish">'. $taskdesc . '</summary>
<ol class="qa-task-list-item">完成时间: ' . $finished_time . '</ol>
<ol class="qa-task-list-item">完成奖励: ' . $value['reward'] . '积分-(已自动获取)</ol>
</details>';

    }
}


$qa_content['custom'] .= '</div>';


// Sub navigation for account pages and suggestion


$qa_content['navigation']['sub'] = qa_user_sub_navigation(qa_get_logged_in_handle(), 'task', true);


return $qa_content;
