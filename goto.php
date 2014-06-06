<?php 
$url = 'https://somedomain.com/somesite/';
$content = file_get_contents($_GET['page']);
$first_step = explode( '<div class="topic_body">' , $content );
$second_step = explode("</div>" , $first_step[1] );

echo $second_step[0];
?>