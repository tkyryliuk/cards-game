<?php

/**
 * @file
 * Provide functionality Desk, which control whole flow of the game.
 * Interact with pack of cards, player and opponent (computer side).
 */
include_once 'Player.php';
include_once 'Opponent.php';
include_once 'PackOfCards.php';

class Desk
{
    //fields
    const CARDS_IN_HAND = 6;
    private $cards;
    private $message;
    private $trump;
    private $whoPickUp;
    private $isDiscard;
    private $whichAction;
    //objects
    public $player;
    public $opponent;
    public $packOfCards;

    public function __construct()
    {
        $this->cards = [];
        $this->isDiscard = false;
    }

    public function startTheGame()
    {
        $this->packOfCards = new PackOfCards();
        $this->opponent = new Opponent();
        $this->player = new Player();

        //Get cards from pack
        $this->playersGetCards();

        //Find who will attack first
        $this->trump = $this->packOfCards->getTrump();
        $playerHand = $this->player->getHand();
        $opponentHand = $this->opponent->getHand();
        $playerLowestTrump = 10;
        $opponentLowestTrump = 10;

        foreach ($playerHand as $value) {
            if (substr($value, 1) == $this->trump) {
                $currentTrumpNum = substr($value, 0);
                if ($currentTrumpNum < $playerLowestTrump) {
                    $playerLowestTrump = $currentTrumpNum;
                }
            }
        }
        foreach ($opponentHand as $value) {
            if (substr($value, 1) == $this->trump) {
                $currentTrumpNum = substr($value, 0);
                if ($currentTrumpNum < $opponentLowestTrump) {
                    $opponentLowestTrump = $currentTrumpNum;
                }
            }
        }

        if ($playerLowestTrump <= $opponentLowestTrump) {
            $this->playerAttack();
        } else {
            $this->opponentAttack();
        }

        //Pass data to the next loop
        $this->addDataToSession();
        $this->packOfCards->addDataToSession();
        $this->player->addDataToSession();
        $this->opponent->addDataToSession();
        //Clearing the $_POST
        header("Location: " . $_SERVER['PHP_SELF']);
    }

    public function continueTheGame()
    {
        $this->createObjects();

        if ($this->whichAction === 'attack' && !empty($_POST['card'])) {
            $this->player->passTheCard($_POST['card']);
            $this->cards[$_POST['card']] = $this->opponent->defense($_POST['card'],
                $this->trump);
            if ($this->cards[$_POST['card']] === 0) {
                $this->message = 'Can\'t beat this card!<br>Put some extra cards, or push "Take"';
                $this->whichAction = 'extraCard';
                $this->whoPickUp = 'opponent';
            } else {
                $this->whichAction = 'extraCard';
                $this->message = 'Your opponent beat this card.<br> Have you any extra cards?';
                $this->isDiscard = true;
                if (!$this->validatePlayerHand()) {
                    $this->whoPickUp = 0;
                }
            }
            //Clearing the $_POST
            header("Location: " . $_SERVER['PHP_SELF']);
        } elseif ($this->whichAction == 'defense' && !empty($_POST['card'])) {
            $this->player->passTheCard($_POST['card']);

            end($this->cards);
            $key = key($this->cards);
            $this->cards[$key] = $_POST['card'];

            $extraCard = $this->opponent->passExtraCard($this->cards);
            if ($extraCard != 0) {
                $this->cards[$extraCard] = 0;
                $this->message = 'Your opponent pass an extra card to you!';
            } else {
                unset($this->cards);
                $this->whichAction = 'attack';
                $this->whoPickUp = 0;
                $this->message = 'Congratulations!<br> Now is your turn to attack!';
                $this->playersGetCards();
            }
            //Clearing the $_POST
            header("Location: " . $_SERVER['PHP_SELF']);
        } elseif ($this->whichAction == 'extraCard' && !empty($_POST['card'])) {
            $this->isDiscard = false;
            $this->whoPickUp = 'opponent';

            $this->player->passTheCard($_POST['card']);
            $this->cards[$_POST['card']] = 0;
            $this->message = 'Can\'t beat!<br>Pass more extra cards, <br>or just push "Take" button!';
            //Clearing the $_POST
            header("Location: " . $_SERVER['PHP_SELF']);
        }

        //When submit "End game" button
        if (!empty($_POST['endgame'])) {
            session_destroy();
            header("Location: " . $_SERVER['PHP_SELF']);
        }

        //When submit Pick Up button
        if (!empty($_POST['pick_up'])) {
            if ($_POST['pick_up'] === 'player') {
                foreach ($this->cards as $key => $value) {
                    $this->player->pickUpCard($key);
                    $this->player->pickUpCard($value);
                }
                unset($this->cards);
                unset($this->whoPickUp);
                $this->playersGetCards();
                $this->opponentAttack();
                //Clearing the $_POST
                header("Location: " . $_SERVER['PHP_SELF']);

            } else {
                foreach ($this->cards as $key => $value) {
                    $this->opponent->pickUpCard($key);
                    $this->opponent->pickUpCard($value);
                }
                unset($this->cards);
                unset($this->whoPickUp);
                $this->playersGetCards();
                $this->playerAttack();
                //Clearing the $_POST
                header("Location: " . $_SERVER['PHP_SELF']);
            }
        }

        if (!empty($_POST['discard'])) {
            unset($this->cards);
            $this->whoPickUp = 0;
            $this->playersGetCards();
            $this->whichAction = 'defense';
            $this->opponentAttack();
            //Clearing the $_POST
            header("Location: " . $_SERVER['PHP_SELF']);
        }

        //Pass data to the next loop
        $this->addDataToSession();
        $this->packOfCards->addDataToSession();
        $this->player->addDataToSession();
        $this->opponent->addDataToSession();
    }

    /**
     * Create objects from $_SESSION
     */
    private function createObjects()
    {
        $this->cards = $_SESSION['desk']['cards'];
        $this->message = $_SESSION['desk']['message'];
        $this->trump = $_SESSION['PackOfCards']['trump'];
        $this->whoPickUp = $_SESSION['desk']['whoPickUp'];
        $this->isDiscard = $_SESSION['desk']['isDiscard'];
        $this->whichAction = $_SESSION['desk']['whichAction'];

        $this->packOfCards = new PackOfCards(
            $_SESSION['PackOfCards']['numberOfCards'],
            $_SESSION['PackOfCards']['cardsInHand'],
            $_SESSION['PackOfCards']['pack'],
            $_SESSION['PackOfCards']['trumpCard'],
            $_SESSION['PackOfCards']['trump'],
            $_SESSION['PackOfCards']['dealCounter']
        );
        $this->player = new Player($_SESSION['player']['hand']);
        $this->opponent = new Opponent($_SESSION['opponent']['hand']);
    }
    /**
     * Validate whether or not user have eny extra cards to pass on desk
     */
    private function validatePlayerHand()
    {
        //if there are less than 6 cards(pairs) on desk
        if (count($this->cards) <= self::CARDS_IN_HAND) {
            $playerHand = $this->player->getHand();
            $uniqueNumbers = [];

            foreach ($this->cards as $bottomCard => $topCard) {
                $uniqueNumbers[substr($bottomCard, 0, 1)] = 0;
                $uniqueNumbers[substr($topCard, 0, 1)] = 0;
            }
            foreach ($uniqueNumbers as $key => $value) {
                if (is_array($playerHand)) {
                    foreach ($playerHand as $card) {
                        if ($key == substr($card, 0, 1)) {
                            return true;
                        }
                    }

                } else {
                    if ($key == substr($playerHand, 0, 1)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    private function playersGetCards()
    {
        $this->player->setHand($this->packOfCards);
        $this->opponent->setHand($this->packOfCards);

    }

    private function playerAttack()
    {
        $this->isDiscard = false;
        $this->whoPickUp = 0;
        $this->whichAction = 'attack';
        $this->message = 'Your move!';
    }

    private function opponentAttack()
    {
        $putCardOnDesk = $this->opponent->attack($this->trump);
        $this->cards[$putCardOnDesk] = 0;
        $this->isDiscard = false;
        $this->whoPickUp = 'player';
        $this->whichAction = 'defense';
        $this->message = 'You have to beat the card, or pick it up';
    }

    public function validateCard($card)
    {
        //Can user beat with this card?
        $isValid = false;

        if (empty($this->cards)) {
            $isValid = true;
        } else {
            end($this->cards);
            $cardOnDesk = key($this->cards); //String e.g. '1h' (means 6 of hearts)

            if (substr($card, 1) == substr($cardOnDesk, 1)) {
                if (substr($card, 0) > substr($cardOnDesk, 0)) {
                    $isValid = true;
                }
            } elseif (substr($card, 1) == $this->trump) {
                $isValid = true;
            }
        }
        return $isValid;
    }

    public function validateExtraCard($card)
    {
        //Can user pass this card as an Extra card?
        $uniqueNumbers = [];

        foreach ($this->cards as $bottomCard => $topCard) {
            $uniqueNumbers[substr($bottomCard, 0, 1)] = 0;
            $uniqueNumbers[substr($topCard, 0, 1)] = 0;
        }
        foreach ($uniqueNumbers as $key => $value) {
            if ($key == substr($card, 0, 1)) {
                return true;
            }
        }
        return false;
    }

    public function renderHeader()
    {
        $output = '';
        $output .= '<!DOCTYPE html>';
        $output .= '<meta charset="utf-8">';
        $output .= '<head>';
        $output .= '<title>Durak - Card game</title>';
        $output .= '<link rel="stylesheet" type="text/css" href="style.css">';
        $output .= '<script src="jquery.js"></script>';
        $output .= '<script src="animation.js"></script>';
        $output .= '</head>';
        $output .= '<body>';
        return $output;
    }

    public function renderForm()
    {
        $basePath = $_SERVER['DOCUMENT_ROOT'] . str_replace('index.php', '',
                $_SERVER['PHP_SELF']); //regular method doesn't work
        $filePath = $basePath . 'form.tpl.php';
        require_once $filePath;
    }

    public function renderFooter()
    {
        $output = '';
        $output .= '</body>';
        $output .= '</html>';
        return $output;
    }

    public function renderStartPage()
    {
        $output = '';
        $output .= '<form name="start-the-game" action="index.php" method="post" id="start-form">';
        $output .= '<div><h1>"Durak" - Card game</h1></div>';
        $output .= '<div><img src="images/deck-of-cards.png"></div>';
        $output .= '<div class="description">Durak is a Russian card game that is popular in post-Soviet states. The object of the game is to get rid of all one\'s cards. At the end of the game, the last player with cards in their hand is referred to as the fool ("durak").</div>';
        $output .= '<button class="button" name="start_the_game" value="start_the_game" type="submit">Start the game</button>';
        $output .= '</form>';
        return $output;
    }

    public function renderCards()
    {
        $output = '';
        if (!empty($this->cards)) {
            foreach ($this->cards as $key => $value) {
                $output .= '<div class="bottom-card"><img src="images/cards/' . $key . '.png"></div>';
                if ($value != 0) {
                    $output .= '<div class="top-card"><img src="images/cards/' . $value . '.png"></div>';
                }
            }
        }
        return $output;
    }

    public function renderGameFinish()
    {
        $output = '';

        if ($_SESSION['gameWinner'] == 'player') {
            $message = 'Congratulations!<br>You win this game!';
        } else {
            $message = 'You lose this game.<br>Try next Time!';
        }

        $output .= '<div id="end-the-game">';
        $output .= '<div><h1>' . $message . '</h1></div>';
        $output .= '<div><img src="images/deck-of-cards-back.png"></div>';
        $output .= '<a href="' . $_SERVER['PHP_SELF'] . '">';
        $output .= '<button class="button">Finish</button>';
        $output .= '</a>';
        $output .= '</div>';


        session_destroy();
        return $output;
    }

    public function getWhichAction()
    {
        return $this->whichAction;
    }

    private function addDataToSession()
    {
        //Pass data to the next loop
        $_SESSION['desk']['message'] = $this->message;
        $_SESSION['desk']['whoPickUp'] = $this->whoPickUp;
        $_SESSION['desk']['isDiscard'] = $this->isDiscard;
        $_SESSION['desk']['whichAction'] = $this->whichAction;

        if (!empty($_SESSION['desk']['cards'])) {
            unset($_SESSION['desk']['cards']);
        }
        $_SESSION['desk']['cards'] = [];
        if (!empty($this->cards)) {
            foreach ($this->cards as $key => $value) {
                $_SESSION['desk']['cards'][$key] = $value;
            }
        }
    }

    public function printMessage()
    {
        return $this->message;
    }

    public function renderPickUpButton()
    {
        $output = '<div>';
        if ($this->whoPickUp === 'player') {
            $output .= '<button class="button" name="pick_up" value="player" type="submit">Pick up cards</button>';
        } elseif ($this->whoPickUp === 'opponent') {
            $output .= '<button class="button" name="pick_up" value="opponent" type="submit">Take</button>';
        }
        $output .= '</div>';
        return $output;
    }

    public function renderDiscardButton()
    {
        $output = '<div>';
        if ($this->isDiscard) {
            $output .= '<button class="button" name="discard" value="discard" type="submit">Discard</button>';
        }
        $output .= '</div>';
        return $output;
    }

    public function renderEndButton()
    {
        $output = '<button class="button" name="endgame" value="endGame" type="submit">End the game</button>';
        return $output;
    }
}
