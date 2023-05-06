<?php

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
    header('Location: ../../');
    exit;
}

require_once QA_INCLUDE_DIR . 'db/selects.php';
require_once QA_INCLUDE_DIR . 'app/format.php';
require_once QA_INCLUDE_DIR . 'db/points.php';
require_once QA_INCLUDE_DIR . 'db/admin.php';
require_once QA_INCLUDE_DIR . 'db/maxima.php';
require_once QA_INCLUDE_DIR . 'app/options.php';
require_once QA_INCLUDE_DIR . 'app/admin.php';


// Check that we're logged in

$userid = qa_get_logged_in_userid();

if (!isset($userid))
    qa_redirect('login');

// Prepare and return content for theme
$qa_content = qa_content_prepare(true);

$qa_content['title'] = '徽章管理';

/*
 * create table qa_badge
(
    id          int auto_increment
        primary key,
    name        varchar(128) not null,
    level_1     int          null,
    level_2     int          null,
    level_3     int          null,
    description varchar(128) null
);
 */


$badges = qa_db_read_all_assoc(qa_db_query_sub('SELECT * FROM ^badge'));

$qa_content['custom'] = '<div>
  <table class="badge-management-table">
  <thead>
    <tr>
      <th>id</th>
      <th>一级名称</th>
      <th>二级名称</th>
      <th>三级名称</th>
      <th>level_1</th>
      <th>level_2</th>
      <th>level_3</th>
      <th>描述</th>
      <th></th>
    </tr>
  </thead>
  <tbody>';

foreach ($badges as $badge) {
    $qa_content['custom'] .= '<tr>';
    $qa_content['custom'] .= '<td>' . $badge['id'] . '</td>';
    $qa_content['custom'] .= '<td>' . $badge['name1'] . '</td>';
    $qa_content['custom'] .= '<td>' . $badge['name2'] . '</td>';
    $qa_content['custom'] .= '<td>' . $badge['name3'] . '</td>';
    $qa_content['custom'] .= '<td>' . $badge['level_1'] . '</td>';
    $qa_content['custom'] .= '<td>' . $badge['level_2'] . '</td>';
    $qa_content['custom'] .= '<td>' . $badge['level_3'] . '</td>';
    $qa_content['custom'] .= '<td>' . $badge['description'] . '</td>';
    $qa_content['custom'] .= '<td><button class = "badge-management-delete-btn" onclick="deleteBadge(this)">删除</button><button class = "badge-management-update-btn" onclick="updateBadge(this)">修改</button></td>';
    $qa_content['custom'] .= '</tr>';
}

$qa_content['custom'] .= '</tbody>
</table>
</div>';
// Sub navigation for account pages and suggestion
$qa_content['navigation']['sub'] = qa_admin_sub_navigation();
$qa_content['navigation']['sub']['admin/badge']['selected'] = true;

return $qa_content;
