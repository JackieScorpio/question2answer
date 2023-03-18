<?php
require_once QA_INCLUDE_DIR . 'app/admin.php';
require_once QA_INCLUDE_DIR . 'db/admin.php';
require_once QA_INCLUDE_DIR . 'app/format.php';
require_once QA_INCLUDE_DIR . 'db/selects.php';



$data = array();

$data['qcount'] = (int)qa_opt('cache_qcount');

$data['acount'] = (int)qa_opt('cache_acount');

$data['vcount'] = qa_db_read_one_value(qa_db_query_sub(
    'select sum(clicktimes) clickcounts from ^posts where type = \'Q\''
));

$data['ucount'] = qa_db_read_one_value(qa_db_query_sub(
    'select count(*) from ^users'
));

echo json_encode($data);

