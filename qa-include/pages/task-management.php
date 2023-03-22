<?php
if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
    header('Location: ../../');
    exit;
}

require_once QA_INCLUDE_DIR . 'db/recalc.php';
require_once QA_INCLUDE_DIR . 'app/admin.php';
require_once QA_INCLUDE_DIR . 'db/admin.php';
require_once QA_INCLUDE_DIR . 'app/format.php';

if (QA_FINAL_EXTERNAL_USERS) {
    header('HTTP/1.1 404 Not Found');
    echo qa_lang_html('main/page_not_found');
    qa_exit();
}

$userid = qa_get_logged_in_userid();
if (!isset($userid))
    qa_redirect('login');

if (!qa_admin_check_privileges($qa_content))
    return $qa_content;

$qa_content = qa_content_prepare(true);

$qa_content['title'] = '任务设置';

$task_type_to_desc = array(
    'q_post' => '发布?个问题',
    'a_post' => '回答?个问题',
    'c_post' => '进行?次评论',
    'vote' => '进行?次投票',
    'a_select' => '采纳?次答案',
);

if(qa_clicked('dosaveoptions')) {
    $task_type = qa_post_text('task_type');
    if (empty($task_type)) {
        $qa_content['custom'] = '<h2 style="color: red">请输入正确的任务类型</h2><a href='. qa_self_html() .'>重新填写</a>';
        return $qa_content;
    }
    $start_date = qa_post_text('start_date');
    if (empty($start_date)) {
        $qa_content['custom'] = '<h2 style="color: red">请输入正确的开始时间</h2><a href='. qa_self_html() .'>重新填写</a>';
        return $qa_content;
    }
    $end_date = qa_post_text('end_date');
    if (empty($end_date)) {
        $qa_content['custom'] = '<h2 style="color: red">请输入正确的截止时间</h2><a href='. qa_self_html() .'>重新填写</a>';
        return $qa_content;
    }
    $finish_count = qa_post_text('task_finish_count');
    if (empty($finish_count)) {
        $qa_content['custom'] = '<h2 style="color: red">请输入正确的达成次数</h2><a href='. qa_self_html() .'>重新填写</a>';
        return $qa_content;
    }
    $finish_reward = qa_post_text('task_finish_reward');
    if (empty($finish_reward)) {
        $qa_content['custom'] = '<h2 style="color: red">请输入正确的积分奖励</h2><a href='. qa_self_html() .'>重新填写</a>';
        return $qa_content;
    }
    $start_date = date("Y-m-d H:i:s",strtotime($start_date));
    $end_date = date("Y-m-d H:i:s",strtotime($end_date));

    qa_db_query_sub(
        'INSERT INTO ^task (started, ended, description, count, reward, cat) VALUES ($, $, $, #, #, $)',
        $start_date, $end_date, $task_type_to_desc[$task_type], $finish_count, $finish_reward, $task_type
    );
}
//$qa_content['custom'] = '<form>
//任务类型 <select name="taskType">
//<option value="q_post">提出问题</option>
//<option value="a_post">回答问题</option>
//<option value="c_post">进行评论</option>
//<option value="vote">进行投票</option>
//<option value="a_select">采纳答案</option>
//</select><br>
//开始时间:<input type="datetime-local" name="start_date"><br>
//截止时间:<input type="datetime-local" name="end_date"><br>
//达成次数:<input type="text" name="" oninput="value=value.replace(/[^\d]/g,\'\')"><br>
//积分奖励:<input type="text" name="" oninput="value=value.replace(/[^\d]/g,\'\')"><br>=
//<input type="submit" value="添加">
//</form>';

$qa_content['form'] = array(
    'tags' => 'method="post" action="' . qa_self_html() . '"',

    'style' => 'tall',

    'fields' => array(),

    'buttons' => array(
        'save' => array(
            'tags' => 'name="dosaveoptions"',
            'label' => '添加',
        ),
    ),
);

$task_types = array(
    'q_post' => '提出问题',
    'a_post' => '回答问题',
    'c_post' => '进行评论',
    'vote' => '参与投票',
    'a_select' => '采纳答案',

);
$qa_content['form']['fields']['task_type'] = array(
    'label' => '任务类型',
    'id' => 'task_type',
    'tags' => 'name="task_type"',
    'type' => 'select',
    'options' => $task_types,
);
$qa_content['form']['fields']['start_time'] = array(
    'type' => 'custom',
    'html' => '开始时间 <input type="datetime-local" name="start_date">'
);
$qa_content['form']['fields']['end_time'] = array(
    'type' => 'custom',
    'html' => '截止时间 <input type="datetime-local" name="end_date">'
);
$qa_content['form']['fields']['task_finish_count'] = array(
    'id' => 'task_finish_count',
    'label' => '达成次数',
    'suffix' => '次',
    'type' => 'number',
    'tags' => 'name="task_finish_count"',
);
$qa_content['form']['fields']['task_finish_reward'] = array(
    'id' => 'task_finish_reward',
    'label' => '积分奖励',
    'suffix' => '分',
    'type' => 'number',
    'tags' => 'name="task_finish_reward"',
);

$current_time = date("Y-m-d H:i:s");
$tasks = qa_db_read_all_assoc(qa_db_query_sub('SELECT * FROM ^task WHERE started <= # AND ended >= #', $current_time, $current_time));

$qa_content['custom'] = '<div>
  <h2>当前任务</h2>
  <table class="badge-management-table">
  <thead>
    <tr>
      <th>id</th>
      <th>开始时间</th>
      <th>结束时间</th>
      <th>描述</th>
      <th>所需次数</th>
      <th>奖励</th>
      <th></th>
    </tr>
  </thead>
  <tbody>';

foreach ($tasks as $task) {
    $qa_content['custom'] .= '<tr>';
    $qa_content['custom'] .= '<td>' . $task['id'] . '</td>';
    $qa_content['custom'] .= '<td>' . $task['started'] . '</td>';
    $qa_content['custom'] .= '<td>' . $task['ended'] . '</td>';
    $qa_content['custom'] .= '<td>' . $task['description'] . '</td>';
    $qa_content['custom'] .= '<td>' . $task['count'] . '</td>';
    $qa_content['custom'] .= '<td>' . $task['reward'] . '</td>';
    $qa_content['custom'] .= '<td><button class = "badge-management-delete-btn" onclick="deleteTask(this)">删除</button><button class = "badge-management-update-btn" onclick="updateTask(this)">修改</button></td>';
    $qa_content['custom'] .= '</tr>';
}

$qa_content['custom'] .= '</tbody>
</table>
</div>';


return $qa_content;