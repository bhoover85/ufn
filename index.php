<?php error_reporting(E_ALL) ?>

<!doctype html>
<html class="no-js" lang="">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>IMDb Top 10</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="stylesheet" href="css/normalize.min.css">
        <link rel="stylesheet" href="css/main.css">

        <script src="js/vendor/modernizr-2.8.3.min.js"></script>
    </head>
    <body>

        <?php
            include 'php/TopList.php';
            include 'php/TopListController.php';
            include 'php/TopListView.php';

            $memcache = new Memcache;
            $memcache->connect('localhost', 11211) or die ("Could not connect");

            $model = new TopList();
            $controller = new TopListController($model);

            // Gets list of top 250 movies from IMDb. Pulls from cache if valid, otherwise queries database.
            $controller->getTop250($memcache);
            
            // Checks if user has entered anything in the input box and if so, gets filtered list of movies.
            if (isset($_GET['action'])) {
                $controller->{$_GET['action']}($memcache, $_POST);
            }

            $TopListView = new TopListView($memcache, $model, $date);

            // Outputs list of movies.
            echo $TopListView->output();
        ?>

        <script src="js/main.js"></script>
    </body>
</html>
