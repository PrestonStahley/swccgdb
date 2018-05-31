<?php

namespace AppBundle\Model;

/**
 * Interface for a collection of SlotInterface
 */
interface SlotCollectionInterface extends \Countable, \IteratorAggregate, \ArrayAccess
{
    /**
     * Get quantity of cards
     * @return integer
     */
    public function countCards();

    /**
     * Get included sets
     * @return \AppBundle\Entity\Set[]
     */
    public function getIncludedSets();

    /**
     * Get all slots sorted by type code (including plots)
     * @return array
     */
    public function getSlotsByType();

    /**
     * Get all slot counts sorted by type code (excluding plots)
     * @return array
     */
    public function getCountByType();

    /**
     * Get the draw deck
     * @return \AppBundle\Model\SlotCollectionInterface
     */
    public function getDrawDeck();

    /**
     * Get the content as an array card_code => qty
     * @return array
     */
    public function getContent();

    /**
     *
     * @param string $side_code
     * @return \AppBundle\Model\SlotCollectionDecorator
     */
    public function filterBySide($side_code);

    /**
     *
     * @param string $type_code
     * @return \AppBundle\Model\SlotCollectionDecorator
     */
    public function filterByType($type_code);

}
