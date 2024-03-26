<?php
    if (isset($_POST['logout']))
    {
        unset($_SESSION["user"]);
        unset($_SESSION["university"]);
        header("Location: /");
    }