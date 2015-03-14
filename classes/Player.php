<?php
include_once "Desk.php";
include_once "PackOfCards.php";

class Player
{
    const CARDS_IN_HAND = 6;
    private $hand;

    public function __construct($hand = [])
    {
        $this->hand = $hand;//e.g. array('1s',5h,...)
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
            if ($handAppend != 0) {
                if (is_array($handAppend)) {
                    $this->hand = array_merge($this->hand, $handAppend);
                } elseif (is_string($handAppend)) {
                    $this->hand[] = $handAppend;
                }
            }
        }
    }

    public function passTheCard($card)
    {
        $key = array_search($card, $this->hand);
        unset($this->hand[$key]);
    }

    public function pickUpCard($card)
    {
        if ($card != 0) {
            $this->hand[] = $card;
        }
    }

    public function renderHand($desk)
    {
        $output = '';

        if (isset($this->hand)) {
            foreach ($this->hand as $key => $value) {
                $output .= '<button type="submit" name="card" value="' . $value . '"';
                if (($desk->getWhichAction() === 'attack' || $desk->getWhichAction() === 'defense') && $desk->validateCard($value)) {
                    $output .= 'class="popup-' . ++$key . '"';
                } elseif ($desk->getWhichAction() === 'extraCard' && $desk->validateExtraCard($value)) {
                    $output .= 'class="popup-' . ++$key . '"';
                } else {
                    $output .= 'disabled';
                }
                $output .= '><img src="images/cards/' . $value . '.png"></button>';
            }
        } else {
            $_SESSION['gameWinner'] = 'player';
            header("Location: " . $_SERVER['PHP_SELF']);
        }
        return $output;
    }

    public function addDataToSession()
    {
        //Pass data to the next loop
        if (!empty($_SESSION['player']['hand'])) {
            unset($_SESSION['player']['hand']);
        }
        foreach ($this->hand as $value) {
            $_SESSION['player']['hand'][] = $value;
        }
    }
}