<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Model\DecklistManager;
use AppBundle\Entity\Decklist;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('cache_expiration'));

        /**
         * @var $decklist_manager DecklistManager
         */
        $decklist_manager = $this->get('decklist_manager');
        $decklist_manager->setLimit(1);
        
        $typeNames = [];
        foreach ($this->getDoctrine()->getRepository('AppBundle:Type')->findAll() as $type) {
            $typeNames[$type->getCode()] = $type->getName();
        }
        
        $decklists_by_side = [];
        $sides = $this->getDoctrine()->getRepository('AppBundle:Side')->findBy(['code' => 'ASC']);

        foreach ($sides as $side) {
            $array = [];
            $array['side'] = $side;

            $decklist_manager->setSide($side);
            $paginator = $decklist_manager->findDecklistsByPopularity();
            /**
        	 * @var $decklist Decklist
        	 */
            $decklist = $paginator->getIterator()->current();
            
            if ($decklist) {
                $array['decklist'] = $decklist;

                $countByType = $decklist->getSlots()->getCountByType();
                $counts = [];
                foreach ($countByType as $code => $qty) {
                    $typeName = $typeNames[$code];
                    $counts[] = $qty . " " . $typeName . "s";
                }
                $array['count_by_type'] = join(' &bull; ', $counts);

                $sides = [ $side->getName() ];
                foreach ($decklist->getSlots()->getAgendas() as $agenda) {
                    $minor_side = $this->get('agenda_helper')->getMinorSide($agenda->getCard());
                    if ($minor_side) {
                        $sides[] = $minor_side->getName();
                    } elseif ($agenda->getCard()->getCode() != '06018') { // prevent Alliance agenda to show up
                        $sides[] = $agenda->getCard()->getName();
                    }
                }
                $array['sides'] = join(' / ', $sides);

                $decklists_by_side[] = $array;
            }
        }

        $game_name = $this->container->getParameter('game_name');
        $publisher_name = $this->container->getParameter('publisher_name');
        
        return $this->render('AppBundle:Default:index.html.twig', [
            'pagetitle' =>  "$game_name Deckbuilder",
            'pagedescription' => "Build your deck for $game_name by $publisher_name. Browse the cards and the thousand of decklists submitted by the community. Publish your own decks and get feedback.",
            'decklists_by_side' => $decklists_by_side
        ], $response);
    }

    public function aboutAction()
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('cache_expiration'));

        return $this->render('AppBundle:Default:about.html.twig', array(
                "pagetitle" => "About",
                "game_name" => $this->container->getParameter('game_name'),
        ), $response);
    }

    public function apiIntroAction()
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('cache_expiration'));

        return $this->render('AppBundle:Default:apiIntro.html.twig', array(
                "pagetitle" => "API",
                "game_name" => $this->container->getParameter('game_name'),
                "publisher_name" => $this->container->getParameter('publisher_name'),
        ), $response);
    }
}
