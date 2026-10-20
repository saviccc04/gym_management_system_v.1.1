<?php 
    function redirect_if ($condition, $location) {
        if($condition) {
            header("location: $location");
            exit();
        }
    };