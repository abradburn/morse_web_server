<?php
        $return = array('ip_address' => $_SERVER['REMOTE_ADDR']);

        echo json_encode($return);
?>
