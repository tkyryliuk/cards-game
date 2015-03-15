<?php

/**
 * @file
 * Provide functionality for computer side of the game
 * (like interacting with desk and packOfCards,
 * automatic extra cards, beating cards and attack).
 */
include_once 'Desk.php';
include_once 'PackOfCards.php';

class Opponent
{
    const CARDS_IN_HAND = 6;
    private $hand = [];

    public function __construct($hand = [])
    {
        $this->hand = $hand; //e.g. array('1s',5h,...)
    }

    public function getHand()
    {
        return $this->hand;
    }

    public function setHand($packOfCards)
    {
        if (empty($this->hand)) {
            $amount = self::CARDS_IN_HAND;
        } else {
            $amount = self::CARDS_IN_HAND - count($this->hand);
        }

        if ($amount > 0) {
            $handAppend = $packOfCards->dealTheCards($amount);
            if (is_array($handAppend)) {
                $this->hand = array_merge($this->hand, $handAppend);
            } elseif (is_string($handAppend)) {
                $this->hand[] = $handAppend;
            }
        }
    }

    public function attack($trump)
    {
        $card = 9;
        $cardNumber = -1;
        $lowestTrump = 9;
        $trumpNumber = -1;

        foreach ($this->hand as $key => $value) {
            if (substr($value, 1, 1) != $trump) {
                if (substr($value, 0, 1) <= substr($card, 0, 1)) {
                    $card = $value;
                    $cardNumber = $key;
                }
            } else {
                if (substr($value, 0, 1) <= substr($lowestTrump, 0, 1)) {

                    $lowestTrump = $value;
                    $trumpNumber = $key;
                }
            }
        }

        if ($cardNumber === -1) {
            $card = $lowestTrump;
            unset($this->hand[$trumpNumber]);
        } else {
            unset($this->hand[$cardNumber]);
        }
        return $card;
    }

    public function defense($card, $trump)
    {
        $answer = 10;
        $answerKey = -1;

        foreach ($this->hand as $key => $value) {//trying to find lowest regular card
            if (substr($card, 1) == substr($value, 1)) {
                if (substr($card, 0) < substr($value, 0)) {
                    if (substr($value, 0) < $answer) {
                        $answer = $value;
                        $answerKey = $key;
                    }
                }
            }
        }
        if ($answer == 10) {
            foreach ($this->hand as $key => $value) {//trying to find lowest trumpCard
                if (substr($value, 1) == $trump) {
                    if (substr($value, 0) < $answer) {
                        $answer = substr($value, 0);
                        $answerKey = $key;
                    }
                }
            }

        }

        if ($answer == 10) {
            $answer = 0;
        } else {
            unset($this->hand[$answerKey]);
        }
        return $answer;
    }

    public function pickUpCard($card)
    {
        if ($card != 0) {
            $this->hand[] = $card;
        }
    }

    public function passExtraCard($cardsOnTable)
    {
        if (count(array_keys($cardsOnTable)) <= self::CARDS_IN_HAND) {
            foreach ($cardsOnTable as $bottomCard => $topCard) {
                $bottomCardNum = substr($bottomCard, 0);
                $topCardNum = substr($topCard, 0);

                foreach ($this->hand as $myCard) {
                    $myCardNumber = substr($myCard, 0);

                    if ($myCardNumber === $bottomCardNum) {
                        return $myCard;
                    }
                    if ($myCardNumber === $topCardNum) {
                        return $myCard;
                    }
                }
            }
        }
        return 0;
    }

    public function renderHand()
    {
        $output = '';

        if (!empty($this->hand)) {
            $rows = count($this->hand);
            for ($i = 0; $i < $rows; $i++) {
                $output .= '<div><img src="images/red_back.png"></div>';
            }
        } else {
            $_SESSION['gameWinner'] = 'opponent';
            header("Location: " . $_SERVER['PHP_SELF']);
        }
        return $output;
    }

    public function addDataToSession()
    {
        //Pass data to the next loop
        if (!empty($_SESSION['opponent']['hand'])) {
            unset($_SESSION['opponent']['hand']);
        }
        foreach ($this->hand as $value) {
            $_SESSION['opponent']['hand'][] = $value;
        }
    }
}