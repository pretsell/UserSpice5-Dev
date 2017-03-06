<?php
    $myForm = new Form ([
        'foo' => new FormField_Text,
        'a' => new FormField_Text,
        'b' => new FormField_Text,
        'bool' => new FormField_Checkbox,
        'save' => new FormField_ButtonSubmit,
    ]);
    echo $myForm->getHTML();
