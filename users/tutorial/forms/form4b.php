<?php
$fooId = Input::get('id');
$myForm = new Form([], [
    'dbtable' => 'foo',
    'dbtableid' => $fooId,
    'default_everything' => true,
]);
