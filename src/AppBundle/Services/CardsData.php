<?php

namespace AppBundle\Services;

use AppBundle\Entity\Card;
use AppBundle\Entity\Cycle;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Templating\Helper\AssetsHelper;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/*
 *
 */

class CardsData
{
    /** @var \Symfony\Component\Asset\Packages $packages */
    private $packages;
    
    private $card_image_base_url = "https://res.starwarsccg.org/cards/Images-HT/starwars/";

    public function __construct(Registry $doctrine, RequestStack $request_stack, Router $router, \Symfony\Component\Asset\Packages $packages, TranslatorInterface $translator, $rootDir)
    {
        $this->doctrine = $doctrine;
        $this->request_stack = $request_stack;
        $this->router = $router;
        $this->packages = $packages;
        $this->translator = $translator;
        $this->rootDir = $rootDir;
    }

    /**
     * Searches for and replaces symbol tokens with markup in a given text.
     * @param string $text
     * @return string
     */
    public function replaceSymbols($text)
    {
        static $displayTextReplacements = [
            '[maintain]' => '<span class="icon-maintain"></span>',
            '[recycle]' => '<span class="icon-recycle"></span>',
            '[sacrifice]' => '<span class="icon-martell"></span>',
        ];

        return str_replace(array_keys($displayTextReplacements), array_values($displayTextReplacements), $text);
    }

    /**
     * Remove weird symbols or replace with markup.
     * @param string $text
     * @return string
     */
    public function formatKeywords($text, $api = false)
    {
        if ($api) {
          static $displayTextReplacements = [
              '\\b0' => '',
              '\\b' => '',
              '\\ul0' => '',
              '\\ul' => '',
              '\\par' => '',
          ];
        } else {
          static $displayTextReplacements = [
              '\\b0' => '</b>',
              '\\b' => '<b>',
              '\\ul0' => '</i>',
              '\\ul' => '<i>',
              '\\par' => '<br/>',
          ];
        }
        return str_replace(array_keys($displayTextReplacements), array_values($displayTextReplacements), $text);
    }

    /**
     * Remove uniqueness indicators (*) or replace with markup.
     * @param string $text
     * @return string
     */
    public function formatUniqueness($text, $api = false)
    {
      if ($api) {
        static $displayTextReplacements = [
            '*' => '',
            '<>' => '',
        ];
      } else {
        static $displayTextReplacements = [
            '*' => '&bull;',
            '<>' => '&loz;',
        ];
      }
        return str_replace(array_keys($displayTextReplacements), array_values($displayTextReplacements), $text);
    }

    public function splitInParagraphs($text)
    {
        if (empty($text)) {
            return '';
        }
        return implode(array_map(function ($l) {
            return "<p>$l</p>";
        }, preg_split('/[\r\n]+/', $text)));
    }

    public function allsetsdata()
    {
        /** @var Cycle[] $list_cycles */
        $list_cycles = $this->doctrine->getRepository('AppBundle:Cycle')->findAll();
        $lines = [];

        foreach ($list_cycles as $cycle) {
            $sets = $cycle->getSets();

            foreach ($sets as $set) {
                if ($cycle->getSize() === 1) {
                    $label = $set->getName();
                } else {
                    $label = $set->getPosition() . '. ' . $set->getName();
                }
                $lines[] = array(
                    "code" => $set->getCode(),
                    "label" => $label,
                    "available" => $set->getDateRelease() ? true : false,
                    "url" => $this->router->generate('cards_list', array('set_code' => $set->getCode()), UrlGeneratorInterface::ABSOLUTE_URL),
                );
            }
        }
        return $lines;
    }

    public function allsetsdatathreaded()
    {
        $list_cycles = $this->doctrine->getRepository('AppBundle:Cycle')->findBy([], array("position" => "ASC"));
        $cycles = [];

        /* @var $cycle \AppBundle\Entity\Cycle */
        foreach ($list_cycles as $cycle) {
            $list_sets = $cycle->getSets();
            $sets = [];

            /* @var $set \AppBundle\Entity\Set */
            foreach ($list_sets as $set) {
                $label = $set->getName();

                $sets[] = [
                    "code" => $set->getCode(),
                    "label" => $label,
                    "available" => $set->getDateRelease() ? true : false,
                    "url" => $this->router->generate('cards_list', array('set_code' => $set->getCode()), UrlGeneratorInterface::ABSOLUTE_URL),
                ];
            }

            if ($cycle->getSize() === 1) {
                $cycles[] = $sets[0];
            } else {
                $cycles[] = [
                    "code" => $cycle->getCode(),
                    "label" => $cycle->getName(),
                    "sets" => $sets,
                    "url" => $this->router->generate('cards_cycle', array('cycle_code' => $cycle->getCode()), UrlGeneratorInterface::ABSOLUTE_URL),
                ];
            }
        }

        return $cycles;
    }

    public function getPrimarySides()
    {
        $sides = $this->doctrine->getRepository('AppBundle:Side')->findAllAndOrderByName();
        return $sides;
    }

    public function get_search_rows($conditions, $sortorder, $forceempty = false)
    {
        $i = 0;

        // construction de la requete sql
        $repo = $this->doctrine->getRepository('AppBundle:Card');
        $qb = $repo->createQueryBuilder('c')
                ->select('c', 'p', 'y', 't', 'b', 's', 'r')
                ->leftJoin('c.set', 'p')
                ->leftJoin('p.cycle', 'y')
                ->leftJoin('c.type', 't')
                ->leftJoin('c.subtype', 'b')
                ->leftJoin('c.side', 's')
                ->leftJoin('c.rarity', 'r');
        $qb2 = null;
        $qb3 = null;

        foreach ($conditions as $condition) {
            $searchCode = array_shift($condition);
            $searchName = \AppBundle\Controller\SearchController::$searchKeys[$searchCode];
            $searchType = \AppBundle\Controller\SearchController::$searchTypes[$searchCode];
            $operator = array_shift($condition);

            switch ($searchType) {
                case 'boolean': {
                        switch ($searchCode) {
                            default: {
                                    if (($operator == ':' && $condition[0]) || ($operator == '!' && !$condition[0])) {
                                        $qb->andWhere("(c.$searchName = 1)");
                                    } else {
                                        $qb->andWhere("(c.$searchName = 0)");
                                    }
                                    $i++;
                                    break;
                                }
                        }
                        break;
                    }
                case 'integer': {
                        switch ($searchCode) {
                            case 'c': // cycle
                                {
                                    $or = [];
                                    foreach ($condition as $arg) {
                                        switch ($operator) {
                                            case ':': $or[] = "(y.position = ?$i)";
                                                break;
                                            case '!': $or[] = "(y.position != ?$i)";
                                                break;
                                        }
                                        $qb->setParameter($i++, $arg);
                                    }
                                    $qb->andWhere(implode($operator == '!' ? " and " : " or ", $or));
                                    break;
                                }
                            default: {
                                    $or = [];
                                    foreach ($condition as $arg) {
                                        switch ($operator) {
                                            case ':': $or[] = "(c.$searchName = ?$i)";
                                                break;
                                            case '!': $or[] = "(c.$searchName != ?$i)";
                                                break;
                                            case '<': $or[] = "(c.$searchName < ?$i)";
                                                break;
                                            case '>': $or[] = "(c.$searchName > ?$i)";
                                                break;
                                        }
                                        $qb->setParameter($i++, $arg);
                                    }
                                    $qb->andWhere(implode($operator == '!' ? " and " : " or ", $or));
                                    break;
                                }
                        }
                        break;
                    }
                case 'code': {
                        switch ($searchCode) {
                            case 'e': {
                                    $or = [];
                                    foreach ($condition as $arg) {
                                        switch ($operator) {
                                            case ':': $or[] = "(p.code = ?$i)";
                                                break;
                                            case '!': $or[] = "(p.code != ?$i)";
                                                break;
                                            case '<':
                                                if (!isset($qb2)) {
                                                    $qb2 = $this->doctrine->getRepository('AppBundle:Set')->createQueryBuilder('p2');
                                                    $or[] = $qb->expr()->lt('p.dateRelease', '(' . $qb2->select('p2.dateRelease')->where("p2.code = ?$i")->getDql() . ')');
                                                }
                                                break;
                                            case '>':
                                                if (!isset($qb3)) {
                                                    $qb3 = $this->doctrine->getRepository('AppBundle:Set')->createQueryBuilder('p3');
                                                    $or[] = $qb->expr()->gt('p.dateRelease', '(' . $qb3->select('p3.dateRelease')->where("p3.code = ?$i")->getDql() . ')');
                                                }
                                                break;
                                        }
                                        $qb->setParameter($i++, $arg);
                                    }
                                    $qb->andWhere(implode($operator == '!' ? " and " : " or ", $or));
                                    break;
                                }
                            case 'r': {
                                    $or = [];
                                    foreach ($condition as $arg) {
                                        switch ($operator) {
                                            case ':': $or[] = "(r.code like ?$i)";
                                                break;
                                            case '!': $or[] = "(r.code not like ?$i)";
                                                break;
                                        }
                                        $qb->setParameter($i++, "%$arg%");
                                    }
                                    $qb->andWhere(implode($operator == '!' ? " and " : " or ", $or));
                                    break;
                                }
                            default: // type, subtype, side
                                {
                                    $or = [];
                                    foreach ($condition as $arg) {
                                        switch ($operator) {
                                            case ':': $or[] = "($searchCode.code = ?$i)";
                                                break;
                                            case '!': $or[] = "($searchCode.code != ?$i)";
                                                break;
                                        }
                                        $qb->setParameter($i++, $arg);
                                    }
                                    $qb->andWhere(implode($operator == '!' ? " and " : " or ", $or));
                                    break;
                                }
                        }
                        break;
                    }
                case 'string': {
                        switch ($searchCode) {
                            case '': // name or index
                                {
                                    $or = [];
                                    foreach ($condition as $arg) {
                                        $code = preg_match('/^\d\d\d\d\d$/u', $arg);
                                        $acronym = preg_match('/^[A-Z]{2,}$/', $arg);
                                        if ($code) {
                                            $or[] = "(c.code = ?$i)";
                                            $qb->setParameter($i++, $arg);
                                        } elseif ($acronym) {
                                            $or[] = "(BINARY(c.name) like ?$i)";
                                            $qb->setParameter($i++, "%$arg%");
                                            $like = implode('% ', str_split($arg));
                                            $or[] = "(REPLACE(c.name, '-', ' ') like ?$i)";
                                            $qb->setParameter($i++, "$like%");
                                        } else {
                                            $or[] = "(c.name like ?$i)";
                                            $qb->setParameter($i++, "%$arg%");
                                        }
                                    }
                                    $qb->andWhere(implode(" or ", $or));
                                    break;
                                }
                            case 'x': // gametext
                                {
                                    $or = [];
                                    foreach ($condition as $arg) {
                                        switch ($operator) {
                                            case ':': $or[] = "(c.gametext like ?$i)";
                                                break;
                                            case '!': $or[] = "(c.gametext is null or c.text not like ?$i)";
                                                break;
                                        }
                                        $qb->setParameter($i++, "%$arg%");
                                    }
                                    $qb->andWhere(implode($operator == '!' ? " and " : " or ", $or));
                                    break;
                                }
                            case 'a': // lore
                                {
                                    $or = [];
                                    foreach ($condition as $arg) {
                                        switch ($operator) {
                                            case ':': $or[] = "(c.lore like ?$i)";
                                                break;
                                            case '!': $or[] = "(c.lore is null or c.lore not like ?$i)";
                                                break;
                                        }
                                        $qb->setParameter($i++, "%$arg%");
                                    }
                                    $qb->andWhere(implode($operator == '!' ? " and " : " or ", $or));
                                    break;
                                }
                            case 'k': // characteristics
                                {
                                    $or = [];
                                    foreach ($condition as $arg) {
                                        switch ($operator) {
                                            case ':': $or[] = "(c.characteristics like ?$i)";
                                                break;
                                            case '!': $or[] = "(c.characteristics is null or c.characteristics not like ?$i)";
                                                break;
                                        }
                                        $qb->setParameter($i++, "%$arg%");
                                    }
                                    $qb->andWhere(implode($operator == '!' ? " and " : " or ", $or));
                                    break;
                                }
                            case 'r': // release
                                {
                                    $or = [];
                                    foreach ($condition as $arg) {
                                        switch ($operator) {
                                            case '<': $or[] = "(p.dateRelease <= ?$i)";
                                                break;
                                            case '>': $or[] = "(p.dateRelease > ?$i or p.dateRelease IS NULL)";
                                                break;
                                        }
                                        if ($arg == "now") {
                                            $qb->setParameter($i++, new \DateTime());
                                        } else {
                                            $qb->setParameter($i++, new \DateTime($arg));
                                        }
                                    }
                                    $qb->andWhere(implode(" or ", $or));
                                    break;
                                }
                        }
                        break;
                    }
            }
        }

        if (!$i && !$forceempty) {
            return;
        }
        switch ($sortorder) {
            case 'set': $qb->orderBy('y.position')->addOrderBy('p.position')->addOrderBy('c.position');
                break;
            case 'side': $qb->orderBy('c.side')->addOrderBy('c.type');
                break;
            case 'type': $qb->orderBy('c.type')->addOrderBy('c.side');
                break;
            case 'cost': $qb->orderBy('c.type')->addOrderBy('c.cost')->addOrderBy('c.income');
                break;
            case 'strength': $qb->orderBy('c.type')->addOrderBy('c.strength')->addOrderBy('c.initiative');
                break;
        }
        $qb->addOrderBy('c.name');
        $qb->addOrderBy('c.code');
        $rows = $qb->getQuery()->getResult();

        return $rows;
    }

    /**
     *
     * @param \AppBundle\Entity\Card $card
     * @param string $api
     * @return multitype:multitype: string number mixed NULL unknown
     */
    public function getCardInfo(Card $card, $api = false)
    {
        $cardinfo = [];

        $metadata = $this->doctrine->getManager()->getClassMetadata('AppBundle:Card');
        $fieldNames = $metadata->getFieldNames();
        $associationMappings = $metadata->getAssociationMappings();

        foreach ($associationMappings as $fieldName => $associationMapping) {
            if ($associationMapping['isOwningSide']) {
                $getter = str_replace(' ', '', ucwords(str_replace('_', ' ', "get_$fieldName")));
                $associationEntity = $card->$getter();
                if (!$associationEntity) {
                    continue;
                }

                $cardinfo[$fieldName . '_code'] = $associationEntity->getCode();
                $cardinfo[$fieldName . '_name'] = $associationEntity->getName();
            }
        }

        foreach ($fieldNames as $fieldName) {
            $getter = str_replace(' ', '', ucwords(str_replace('_', ' ', "get_$fieldName")));
            $value = $card->$getter();
            switch ($metadata->getTypeOfField($fieldName)) {
                case 'datetime':
                case 'date':
                    continue 2;
                    break;
                case 'boolean':
                    $value = (boolean) $value;
                    break;
            }
            $fieldName = ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $fieldName)), '_');
            $cardinfo[$fieldName] = $value;
        }

        $cardinfo['url'] = $this->router->generate('cards_zoom', array('card_code' => $card->getCode()), UrlGeneratorInterface::ABSOLUTE_URL);

        $cardinfo['label'] = $card->getName();
        $cardinfo['image_url'] = $this->card_image_base_url . $cardinfo['image_url'];

        if($cardinfo['image_url2']) {
          $cardinfo['image_url2'] = $this->card_image_base_url . $cardinfo['image_url2'];
        }

        $cardinfo['icon'] = $card->getIcon();

        if ($api) {
            unset($cardinfo['id']);
        } else {
            $cardinfo['uniqueness'] = $this->formatUniqueness($cardinfo['uniqueness']);
            $cardinfo['gametext'] = $this->replaceSymbols($cardinfo['gametext']);
            $cardinfo['gametext'] = $this->splitInParagraphs($cardinfo['gametext']);
        }

        $cardinfo['characteristics'] = $this->formatKeywords($cardinfo['characteristics'], $api);
        $cardinfo['gametext'] = $this->formatKeywords($cardinfo['gametext'], $api);
        $cardinfo['lore'] = $this->formatKeywords($cardinfo['lore'], $api);

        return $cardinfo;
    }

    public function syntax($query)
    {
        // renvoie une liste de conditions (array)
        // chaque condition est un tableau à n>1 éléments
        // le premier est le type de condition (0 ou 1 caractère)
        // les suivants sont les arguments, en OR

        $query = preg_replace('/\s+/u', ' ', trim($query));

        $list = [];
        $cond = null;
        // l'automate a 3 états :
        // 1:recherche de type
        // 2:recherche d'argument principal
        // 3:recherche d'argument supplémentaire
        // 4:erreur de parsing, on recherche la prochaine condition
        // s'il tombe sur un argument alors qu'il est en recherche de type, alors le type est vide
        $etat = 1;
        while ($query != "") {
            if ($etat == 1) {
                if (isset($cond) && $etat != 4 && count($cond) > 2) {
                    $list[] = $cond;
                }
                // on commence par rechercher un type de condition
                $match = [];
                if (preg_match('/^(\p{L})([:<>!])(.*)/u', $query, $match)) { // jeton "condition:"
                    $cond = array(mb_strtolower($match[1]), $match[2]);
                    $query = $match[3];
                } else {
                    $cond = array("", ":");
                }
                $etat = 2;
            } else {
                if (preg_match('/^"([^"]*)"(.*)/u', $query, $match) // jeton "texte libre entre guillements"
                        || preg_match('/^([\p{L}\p{N}\-\&]+)(.*)/u', $query, $match) // jeton "texte autorisé sans guillements"
                ) {
                    if (($etat == 2 && count($cond) == 2) || $etat == 3) {
                        $cond[] = $match[1];
                        $query = $match[2];
                        $etat = 2;
                    } else {
                        // erreur
                        $query = $match[2];
                        $etat = 4;
                    }
                } elseif (preg_match('/^\|(.*)/u', $query, $match)) { // jeton "|"
                    if (($cond[1] == ':' || $cond[1] == '!') && (($etat == 2 && count($cond) > 2) || $etat == 3)) {
                        $query = $match[1];
                        $etat = 3;
                    } else {
                        // erreur
                        $query = $match[1];
                        $etat = 4;
                    }
                } elseif (preg_match('/^ (.*)/u', $query, $match)) { // jeton " "
                    $query = $match[1];
                    $etat = 1;
                } else {
                    // erreur
                    $query = substr($query, 1);
                    $etat = 4;
                }
            }
        }
        if (isset($cond) && $etat != 4 && count($cond) > 2) {
            $list[] = $cond;
        }
        return $list;
    }

    public function validateConditions($conditions)
    {
        // suppression des conditions invalides
        $numeric = array('<', '>');

        foreach ($conditions as $i => $l) {
            $searchCode = $l[0];
            $searchOp = $l[1];

            if (in_array($searchOp, $numeric) && \AppBundle\Controller\SearchController::$searchTypes[$searchCode] !== 'integer') {
                // operator is numeric but searched property is not
                unset($conditions[$i]);
            }
        }

        return array_values($conditions);
    }

    public function buildQueryFromConditions($conditions)
    {
        // reconstruction de la bonne chaine de recherche pour affichage
        return implode(" ", array_map(
                        function ($l) {
                            return ($l[0] ? $l[0] . $l[1] : "")
                            . implode("|", array_map(
                                            function ($s) {
                                                return preg_match("/^[\p{L}\p{N}\-\&]+$/u", $s) ? $s : "\"$s\"";
                                            },
                                array_slice($l, 2)
                    ));
                        },
            $conditions
        ));
    }

    public function get_reviews($card)
    {
        $reviews = $this->doctrine->getRepository('AppBundle:Review')->findBy(array('card' => $card), array('nbVotes' => 'DESC'));

        $response = $reviews;

        return $response;
    }

    public function getDistinctTraits()
    {
        /**
         * @var $em \Doctrine\ORM\EntityManager
         */
        $em = $this->doctrine->getManager();
        $qb = $em->createQueryBuilder();
        $qb->from('AppBundle:Card', 'c');
        $qb->select('c.traits');
        $qb->distinct();
        $result = $qb->getQuery()->getResult();

        $traits = [];
        foreach ($result as $card) {
            $subs = explode('.', $card["traits"]);
            foreach ($subs as $sub) {
                $traits[trim($sub)] = 1;
            }
        }
    }
}
