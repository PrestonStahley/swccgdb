<?php

namespace AppBundle\Helper;

use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Card;
use AppBundle\Entity\Side;

class AgendaHelper
{
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Get the minor side code
     * @param Card $agenda
     * @return string
     */
    public function getMinorSideCode(Card $agenda)
    {
        if (empty($agenda)) {
            return null;
        }
    
        // special case for the Core Set Banners
        $banners_core_set = [
                '01198' => 'baratheon',
                '01199' => 'greyjoy',
                '01200' => 'lannister',
                '01201' => 'martell',
                '01202' => 'thenightswatch',
                '01203' => 'stark',
                '01204' => 'targaryen',
                '01205' => 'tyrell'
        ];
        if (isset($banners_core_set[$agenda->getCode()])) {
            return $banners_core_set[$agenda->getCode()];
        }
        return null;
    }

    /**
     * Get the minor side
     * @param Card $agenda
     * @return Side
     */
    public function getMinorSide(Card $agenda)
    {
        $code = $this->getMinorSideCode($agenda);
        if ($code) {
            return $this->entityManager->getRepository('AppBundle:Side')->findOneBy([ 'code' => $code ]);
        }
        return null;
    }
}
