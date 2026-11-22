<?php

    try{
        $user = "root";
        $pass = "";
        $db = "graille";
        $host = "localhost";
        $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
        $bdd = new PDO($dsn, $user, $pass);
        $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $bdd->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        print "Erreur! :" . $e->getMessage() . "<br/>";
        die();
    }
