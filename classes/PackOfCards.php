<?php

/**
 * @file
 * Provide functionality for pack of playing cards.
 */
class PackOfCards
{
    private $numberOfCards;
    private $cardsInHand;
    private $pack;
    private $trumpCard;
    private $trump;
    private $dealCounter = 0;

    public function __construct(
        $numberOfCards = 36,
        $cardsInHand = 6,
        $pack = -1,
        $trumpCard = 0,
        $trump = 0,
        $dealCounter = 0
    ) {
        if ($pack == -1) {
            $this->pack = [
                '1c' => '1c',
                '2c' => '2c',
                '3c' => '3c',
                '4c' => '4c',
                '5c' => '5c',
                '6c' => '6c',
                '7c' => '7c',
                '8c' => '8c',
                '9c' => '9c',//clubs
                '1d' => '1d',
                '2d' => '2d',
                '3d' => '3d',
                '4d' => '4d',
                '5d' => '5d',
                '6d' => '6d',
                '7d' => '7d',
                '8d' => '8d',
                '9d' => '9d',//diamonds
                '1h' => '1h',
                '2h' => '2h',
                '3h' => '3h',
                '4h' => '4h',
                '5h' => '5h',
                '6h' => '6h',
                '7h' => '7h',
                '8h' => '8h',
                '9h' => '9h',//hearts
                '1s' => '1s',
                '2s' => '2s',
                '3s' => '3s',
                '4s' => '4s',
                '5s' => '5s',
                '6s' => '6s',
                '7s' => '7s',
                '8s' => '8s',
                '9s' => '9s',//spades
            ];
        } else {
            $this->pack = $pack;
        }
        $this->numberOfCards = $numberOfCards;
        $this->cardsInHand = $cardsInHand;
        $this->trumpCard = $trumpCard;
        $this->trump = $trump;
        $this->dealCounter = $dealCounter;
    }

    public function getTrump()
    {
        return $this->trump;
    }

    public function dealTheCards($amount)
    {
        if ($this->numberOfCards === 0) {
            return 0;
        }
        if ($amount >= $this->numberOfCards) {
            if (isset($this->trumpCard)) {
                $this->pack[$this->trumpCard] = $this->trumpCard;
                $this->trumpCard = 0;
            }
            $amount = $this->numberOfCards;
        }
        $hand = array_rand($this->pack, $amount);
        if (is_array($hand)) {
            foreach ($hand as $value) {
                unset($this->pack[$value]);
            }
        } else {
            unset($this->pack[$hand]);
        }

        $this->numberOfCards -= $amount;
        if ($this->cardsInHand < $this->numberOfCards) {
            $this->cardsInHand = $this->numberOfCards;
        }

        $this->dealCounter += 1;
        if ($this->dealCounter == 2) {
            $this->trumpCard = array_rand($this->pack);
            $this->trump = substr($this->trumpCard, 1);
            unset($this->pack[$this->trumpCard]);
        }

        return $hand;
    }

    public function renderPack()
    {
        $output = '';
        if ($this->trumpCard != 0) {
            $output .= '<div class="trump"><img src="images/cards/' . $this->trumpCard . '.png"></div>';
        } else {
            $output .= '<div class="trump"><img src="images/cards/' . $this->trump . '.png"></div>';
        }
        if ($this->numberOfCards >= 2) {
            $output .= '<div class="pack"><img src="images/half_pack.png"></div>';
        }
        return $output;
    }

    public function addDataToSession()
    {
        //Pass data to the next loop
        $_SESSION['PackOfCards']['numberOfCards'] = $this->numberOfCards;
        $_SESSION['PackOfCards']['cardsInHand'] = $this->cardsInHand;
        $_SESSION['PackOfCards']['trump'] = $this->trump;
        $_SESSION['PackOfCards']['dealCounter'] = $this->dealCounter;
        if (isset($this->trumpCard)) {
            $_SESSION['PackOfCards']['trumpCard'] = $this->trumpCard;
        }
        if (!empty($_SESSION['PackOfCards']['pack'])) {
            unset($_SESSION['PackOfCards']['pack']);
        }
        if (!empty($this->pack) && is_array($this->pack)) {
            foreach ($this->pack as $value) {
                $_SESSION['PackOfCards']['pack'][$value] = $value;
            }
        } else {
            $_SESSION['PackOfCards']['pack'][] = 0;
        }
    }
}




















