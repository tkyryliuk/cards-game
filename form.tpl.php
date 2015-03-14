<form name="game" action="index.php" method="post" id="game">
    <div class="left-sidebar">
        <div class="pack-of-cards">
            <?php  global $desk; ?>
            <?php echo $desk->packOfCards->renderPack(); ?>
        </div>
    </div>
    <div class="middle">
        <div class="opponent">
            <div class="wrapper">
                <?php echo $desk->opponent->renderHand(); ?>
            </div>
        </div>
        <div class="desk">
            <div class="wrapper">
                <?php echo $desk->renderCards(); ?>
            </div>
        </div>
        <div class="player">
            <div class="hand">
                <?php echo $desk->player->renderHand($desk); ?>
            </div>
        </div>
    </div>
    <div class="right-sidebar">
        <div class="messages">
            <div>
                <?php echo $desk->printMessage(); ?>
            </div>
        </div>
        <div class="info-bar">
            <?php echo $desk->renderPickUpButton(); ?><br>
            <?php echo $desk->renderDiscardButton(); ?><br>
            <div class="end-game">
                <?php echo $desk->renderEndButton(); ?>
            </div>
        </div>
    </div>
</form>