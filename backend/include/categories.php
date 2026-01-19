<?php
$info = (object)[];

$query = "select * from category order by name";
$result = $DB->read($query);

$info->data_type = 'get_categories';
$info->categories = $result ? $result : [];

echo json_encode($info);
