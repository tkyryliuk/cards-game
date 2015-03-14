<?php

include_once 'classes/Desk.php';
session_start();

$desk = new Desk();
if (empty($_SESSION)) {
    if (empty($_POST['start_the_game'])) {
        echo $desk->renderHeader();
        echo $desk->renderStartPage();
        echo $desk->renderFooter();
    } else {
        $desk->startTheGame();
        echo $desk->renderHeader();
        $desk->renderForm();
        echo $desk->renderFooter();
    }
} else {
    if (empty($_SESSION['gameWinner'])) {
        $desk->continueTheGame();
        echo $desk->renderHeader();
        $desk->renderForm();
        echo $desk->renderFooter();
    } else {
        echo $desk->renderHeader();
        echo $desk->renderGameFinish();
        echo $desk->renderFooter();
    }
}
?>