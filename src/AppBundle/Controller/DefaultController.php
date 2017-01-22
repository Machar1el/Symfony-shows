<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Template()
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @Route("/shows/{id}", name="shows", defaults={"id": "1"})
     * @Template()
     */
    public function showsAction($id)
    {
        $title = "Liste des séries";
		$nbPerPage = 8;                                 // Nombre de séries par page
        $offset = $id * $nbPerPage - $nbPerPage;        // Offset pour la recherche de X éléments dans la table des series
        $em = $this->get('doctrine')->getManager();
        $repository = $em->getRepository('AppBundle:TVShow');
        $shows = $repository->findBy(
            array(),
            array('name' => 'asc'),
            $nbPerPage,
            $offset
        );
		$em = $this->getDoctrine()->getManager();
        $query = $em->createQueryBuilder();
        $query->select('count(s.id) as somme')
            ->from('AppBundle:TVShow', 's');
        $nbResult = $query->getQuery()->getSingleScalarResult();

        $pageFin = ceil($nbResult / $nbPerPage);        // Nombre de pages
		
		return $this->render('AppBundle:Default:shows.html.twig', array(
            'shows' => $shows,
            'page' => $id,
            'pageFin' => $pageFin,
            'title' => $title
        ));
    }

    /**
     * @Route("/show/{id}", name="show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->get('doctrine')->getManager();
        $repository = $em->getRepository('AppBundle:TVShow');

        return [
            'show' => $repository->find($id)
        ];        
    }
	
	/**
     * @Route("/search", name="search")
     * @Template()
     */
    public function searchAction(Request $request)
    {
        $title = "Résultat de la recherche";

        if ($request->getMethod() == "POST") {
            $search = $request->request->get('search');
            $em = $this->getDoctrine()->getManager();
            $query = $em->createQueryBuilder();
            $query
                ->select('s')
                ->from('AppBundle:TVShow', 's')
                ->where('s.name LIKE :data OR s.synopsis LIKE :data')
                ->setParameter('data', '%'.$search.'%');
            $shows = $query->getQuery()->getResult();
        }
        return $this->render('AppBundle:Default:shows.html.twig', array(
            'shows' => $shows,
            'title' => $title
        ));
    }

    /**
     * @Route("/calendar", name="calendar")
     * @Template()
     */
    public function calendarAction()
    {
        $date = new \DateTime();
        $em = $this->getDoctrine()->getManager();
        $query_episodes = $em->createQueryBuilder();
        $query_episodes->select('e')
            ->from('AppBundle:Episode', 'e')
            ->where('e.date >= :date')
            ->orderBy('e.date', 'ASC')
            ->setParameter('date', $date);
        $episodes = $query_episodes->getQuery()->getResult();

        return $this->render('AppBundle:Default:calendar.html.twig', array(
            'episodes' => $episodes
        ));
    }

    /**
     * @Route("/login", name="login")
     * @Template()
     */
    public function loginAction()
    {
        return [];
    }
}
