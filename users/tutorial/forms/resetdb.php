<?php
/*
 * resetdb.PHP
 * DROP and re-CREATE the tables used in the tutorial, re-filling them with data
 */

$fooname = isset($T['foo']) ? $T['foo'] : 'foo';
$barname = isset($T['bar']) ? $T['bar'] : 'bar';
$init_commands = [
    "CREATE TABLE `$fooname` (
          `id` int(11) NOT NULL,
          `foo` varchar(250) DEFAULT NULL,
          `a` varchar(250) NOT NULL,
          `b` int(25) NOT NULL,
          `bool` tinyint(1) NOT NULL
      )",
    "INSERT INTO `$fooname` (`id`, `foo`, `a`, `b`, `bool`) VALUES
        (1, 'foo for id=1', 'a for id=1', 27, 1),
        (2, 'foo for id=2', 'a for id=2', 51, 0),
        (3, 'foo for id=3', 'a for id=3', 9, 1),
        (4, 'foo for id=4', 'a for id=4', 81, 0),
        (5, 'foo for id=5', 'a for id=5', 17, 1),
        (6, 'foo for id=6', 'a for id=6', 3, 0),
        (7, 'foo for id=7', 'a for id=7', 28, 1),
        (8, 'foo for id=8', 'a for id=8', 29, 0),
        (9, 'foo for id=9', 'a for id=9', 103, 1)",
    "CREATE TABLE `$barname` (
          `id` int(11) NOT NULL,
          `bar` varchar(250) DEFAULT NULL,
          `a` varchar(250) NOT NULL,
          `b` int(25) NOT NULL
      )",
    "INSERT INTO `$barname` (`id`, `bar`, `a`, `b`) VALUES
        (1, 'bar for id=1', 'a for id=1', 27),
        (2, 'bar for id=2', 'a for id=2', 51),
        (3, 'bar for id=3', 'a for id=3', 9),
        (4, 'bar for id=4', 'a for id=4', 81),
        (5, 'bar for id=5', 'a for id=5', 17),
        (6, 'bar for id=6', 'a for id=6', 3),
        (7, 'bar for id=7', 'a for id=7', 28),
        (8, 'bar for id=8', 'a for id=8', 29),
        (9, 'bar for id=9', 'a for id=9', 103)",
    "ALTER TABLE `$fooname`
          ADD PRIMARY KEY (`id`)",
    "ALTER TABLE `$barname`
          ADD PRIMARY KEY (`id`)",
    "ALTER TABLE `$fooname`
          MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10",
    "ALTER TABLE `$fooname`
          MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10",
];

$tutor_tables=[$fooname, $barname];

$db = DB::getInstance();
$existing_tables = [];
foreach ($tutor_tables as $t) {
    echo "Checking if $t exists...";
    $sql = "SELECT * FROM $t WHERE 1=2";
    if (!$db->query($sql)->error()) {
        $existing_tables[] = $t;
        echo "Yep. Sure 'nuf...<br />\n";
    } else {
        echo "Nope...<br />\n";
    }
}
if (@$_POST['save']) {
    if ($existing_tables) {
        foreach ($existing_tables as $t) {
            echo "Dropping $t<br />\n";
            $sql = "DROP TABLE $t";
            $db->query($sql);
        }
    }
    $errcount=0;
    foreach ($init_commands as $sql) {
        $db->query($sql);
        if ($db->error()) {
            echo "SQL PROBLEM: $sql<br />\n";
            echo "REPORTED SQL ERROR: ".$db->errorString()."<br />\n";
            $errcount++;
        }
    }
    if ($errcount) {
        echo "Completed with $errcount errors. Installation was NOT successful.<br />\n";
    } else {
        echo "Database initialized successfully. Theoretically you are good to go!";
    }
} else {
    if ($existing_tables) {
        $continue_msg = "<h2>These tables already exist in the database:</h2><ul>\n";
        foreach ($existing_tables as $t) {
            $continue_msg .= "<li>$t";
            if ($prefix) {
                $continue_msg .= " ({$prefix}$t with prefix)";
            }
            $continue_msg .= "</li>\n";
        }
        $continue_msg .= "</ul>\n";
        $button_label = 'DELETE and Install';
        $continue_msg .= "<h2>Press '$button_label' to delete (drop) these tables and all data in them and initialize the database from scratch. ALL EXISTING DATA WILL BE LOST. There is no undo on this action!</h2>\n";
    } else {
        $button_label = 'Continue';
        $continue_msg = "<h2>Everything looks good. No tables will be overwritten in the database. Press '$button_label' to initialize the database.</h2>\n";
    }
?>
    <html><head></head>
    <body>
        <?= $continue_msg ?>
        <br />
        <form method="post">
            <input type="submit" name="save" value="<?= $button_label ?>" />
        </form>
    </body>
    </html>
<?php
}
