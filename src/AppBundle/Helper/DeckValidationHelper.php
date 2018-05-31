<?php

namespace AppBundle\Helper;

use Symfony\Component\Translation\TranslatorInterface;
use AppBundle\Model\SlotCollectionProviderInterface;

class DeckValidationHelper
{
    public function __construct(AgendaHelper $agenda_helper, TranslatorInterface $translator)
    {
        $this->agenda_helper = $agenda_helper;
        $this->translator = $translator;
    }

    public function getInvalidCards($deck)
    {
        $invalidCards = [];
        foreach ($deck->getSlots() as $slot) {
            if (!$this->canIncludeCard($deck, $slot->getCard())) {
                $invalidCards[] = $slot->getCard();
            }
        }
        return $invalidCards;
    }

    public function canIncludeCard($deck, $card)
    {
        if ($card->getSide()->getCode() === $deck->getSide()->getCode()) {
            return true;
        }

        return false;
    }

    public function findProblem(SlotCollectionProviderInterface $deck)
    {
        $expectedCardCount = 60;
        $deckCardCount = $deck->getSlots()->getDrawDeck()->countCards();

        if ($deckCardCount < $expectedCardCount) {
            return 'too_few_cards';
        }
        if ($deckCardCount > $expectedCardCount) {
            return 'too_many_cards';
        }
        if (!empty($this->getInvalidCards($deck))) {
            return 'invalid_cards';
        }
        return null;
    }

    public function getProblemLabel($problem)
    {
        if (!$problem) {
            return '';
        }
        return $this->translator->trans('decks.problems.' . $problem);
    }
}
