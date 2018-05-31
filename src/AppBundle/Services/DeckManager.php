<?php

namespace AppBundle\Services;

use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Deck;
use AppBundle\Entity\Deckslot;
use Symfony\Bridge\Monolog\Logger;
use AppBundle\Entity\Deckchange;
use AppBundle\Helper\DeckValidationHelper;
use AppBundle\Helper\AgendaHelper;

class DeckManager
{
    public function __construct(EntityManager $doctrine, DeckValidationHelper $deck_validation_helper, AgendaHelper $agenda_helper, Diff $diff, Logger $logger)
    {
        $this->doctrine = $doctrine;
        $this->deck_validation_helper = $deck_validation_helper;
        $this->agenda_helper = $agenda_helper;
        $this->diff = $diff;
        $this->logger = $logger;
    }

    public function getByUser($user, $decode_variation = false)
    {
        $decks = $user->getDecks();
        $list = [];
        foreach ($decks as $deck) {
            $list[] = $deck->jsonSerialize(false);
        }

        return $list;
    }

    /**
     *
     * @param unknown $user
     * @param Deck $deck
     * @param unknown $decklist_id
     * @param unknown $name
     * @param unknown $side
     * @param unknown $description
     * @param unknown $tags
     * @param unknown $content
     * @param unknown $source_deck
     */
    public function save($user, $deck, $decklist_id, $name, $side, $description, $tags, $content, $source_deck)
    {
        $deck_content = [];

        if ($decklist_id) {
            $decklist = $this->doctrine->getRepository('AppBundle:Decklist')->find($decklist_id);
            if ($decklist) {
                $deck->setParent($decklist);
            }
        }

        $deck->setName($name);
        $deck->setSide($side);
        $deck->setDescriptionMd($description);
        $deck->setUser($user);
        $deck->setMinorVersion($deck->getMinorVersion() + 1);
        $cards = [];
        /* @var $latestSet \AppBundle\Entity\Set */
        $latestSet = null;
        foreach ($content as $card_code => $qty) {
            $card = $this->doctrine->getRepository('AppBundle:Card')->findOneBy(array(
                "code" => $card_code
            ));
            if (!$card) {
                continue;
            }

            $cards [$card_code] = $card;

            $set = $card->getSet();
            if (!$latestSet) {
                $latestSet = $set;
            } elseif (empty($set->getDateRelease())) {
                $latestSet = $set;
            } elseif (empty($latestSet->getDateRelease())) {
            } elseif ($latestSet->getDateRelease() < $set->getDateRelease()) {
                $latestSet = $set;
            }
        }
        $deck->setLastSet($latestSet);
        if (empty($tags)) {
            // tags can never be empty. if it is we put side in
            $tags = [$side->getCode()];
        }
        if (is_string($tags)) {
            $tags = preg_split('/\s+/', $tags);
        }
        $tags = implode(' ', array_unique(array_values($tags)));
        $deck->setTags($tags);
        $this->doctrine->persist($deck);

        // on the deck content

        if ($source_deck) {
            // compute diff between current content and saved content
            list($listings) = $this->diff->diffContents(array(
                $content,
                $source_deck->getSlots()->getContent()
            ));
            // remove all change (autosave) since last deck update (changes are sorted)
            $changes = $this->getUnsavedChanges($deck);
            foreach ($changes as $change) {
                $this->doctrine->remove($change);
            }
            $this->doctrine->flush();
            // save new change unless empty
            if (count($listings [0]) || count($listings [1])) {
                $change = new Deckchange();
                $change->setDeck($deck);
                $change->setVariation(json_encode($listings));
                $change->setIsSaved(true);
                $change->setVersion($deck->getVersion());
                $this->doctrine->persist($change);
                $this->doctrine->flush();
            }
            // copy version
            $deck->setMajorVersion($source_deck->getMajorVersion());
            $deck->setMinorVersion($source_deck->getMinorVersion());
        }
        foreach ($deck->getSlots() as $slot) {
            $deck->removeSlot($slot);
            $this->doctrine->remove($slot);
        }

        foreach ($content as $card_code => $qty) {
            $card = $cards [$card_code];
            $slot = new Deckslot();
            $slot->setQuantity($qty);
            $slot->setCard($card);
            $slot->setDeck($deck);
            $deck->addSlot($slot);
            $deck_content [$card_code] = array(
                'card' => $card,
                'qty' => $qty
            );
        }

        $deck->setProblem($this->deck_validation_helper->findProblem($deck));

        return $deck->getId();
    }

    public function revert($deck)
    {
        $changes = $this->getUnsavedChanges($deck);
        foreach ($changes as $change) {
            $this->doctrine->remove($change);
        }
        $this->doctrine->flush();
    }

    public function getUnsavedChanges($deck)
    {
        return $this->doctrine->getRepository('AppBundle:Deckchange')->findBy(array(
                    'deck' => $deck,
                    'isSaved' => false
        ));
    }
}
