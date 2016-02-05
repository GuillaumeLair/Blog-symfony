<?php

namespace BlogSymfony\ArticleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request; 
use Symfony\Component\HttpFoundation\Session\Session;
use BlogSymfony\ArticleBundle\Entity\ArticleRepository;
use BlogSymfony\ArticleBundle\Entity\Article;
use \DateTime;

class DefaultController extends Controller
{
	const NB_ARTICLES_PAR_PAGE = 5;
	
    public function accueilAction ($page){
		$articleRepo = $this->getDoctrine()->getRepository('BlogSymfonyArticleBundle:Article');
		
		$tabArticles = $articleRepo->getArticleForPage($page);
		
		$nbArticles = $articleRepo->getNbArticles();
		$nbPages = ceil($nbArticles / self::NB_ARTICLES_PAR_PAGE);
		
		$variablesTwig = array(
			'tabArticles' =>$tabArticles,
			'page' => $page,
			'nbPages' => $nbPages
		);
		if(count($tabArticles)>0) {
		return $this->render('BlogSymfonyArticleBundle:Default:accueil.html.twig', $variablesTwig);
		} else {
			throw $this->createNotFoundException('Aucun article sur cette page');
		}
	}
	
	public function articleAction($slug) {
		$articleRepo = $this->getDoctrine()->getRepository('BlogSymfonyArticleBundle:Article');
		
		$article = $articleRepo->findOneBySlug($slug);
		
		$variablesTwig = array(
			'article' =>$article
		);
		
		return $this->render('BlogSymfonyArticleBundle:Default:article.html.twig', $variablesTwig);
		
	}
	
	public function redactionAction(Request $request, $slug) {
		
		if($slug === ''){
			$article= new Article();
			$dateParams=array(
			'date_format' => 'ddMMMMyyyy',
			'data'=> new DateTime(),
			);
		} else {
			$articleRepo = $this->getDoctrine()->getRepository('BlogSymfonyArticleBundle:Article');
			$article = $articleRepo->findOneBySlug($slug);
			$dateParams=array(
			'date_format' => 'ddMMMMyyyy',
			);
		}
		
		$formBuilder = $this->createFormBuilder($article)
			->add('titre', 'text', array('label'=> 'Titre de l\'article'))
			//->add('slug', 'text')
			->add('date', 'datetime', $dateParams)
			->add('corps', 'textarea',array ('label'=> 'Texte de l\'article'))
			->add('Enregistrer', 'submit');
		$form = $formBuilder->getForm();
		
		$form -> handleRequest($request);
		
		if($form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$em->persist($article);
			$em->flush();
			
			return $this->redirectToRoute('blog_article',array('slug'=>$article->getSlug()));
		}
		
		
		
		$variableTwig = array(
			'monForm' =>$form->createView(),
		);
		
		return $this->render('BlogSymfonyArticleBundle:Default:redaction.html.twig', $variableTwig);
	}
	
	public function backofficeAction(Request $request){
		$tabUsers = array();
		//Recup des Users si ROLE_ADMIN
		if($this->get('security.context')->isGranted('ROLE_ADMIN')){
		$userManager = $this->get('fos_user.user_manager');
		$tabUsers = $userManager->findUsers();
		}
		
		//Recup tableau article
		$articleRepo = $this->getDoctrine()->getRepository('BlogSymfonyArticleBundle:Article');
		$tabArticles = $articleRepo->findAll();
		
		//Preparation de l'affichage
		$session = $request->getSession();
		$tabNotices = $session->getFlashBag()->get('notice',array());
		$msgNotice = implode('<br/>', $tabNotices);
		
		$variableTwig = array(
			'tabArticles' => $tabArticles,
			'msgNotice' => $msgNotice,
			'tabUsers' => $tabUsers,
		);
		
		return $this ->render ('BlogSymfonyArticleBundle:Default:backoffice.html.twig', $variableTwig);
		
	}
	
	public function suppressionAction($slug, Request $request) {
		$articleRepo = $this->getDoctrine()->getRepository('BlogSymfonyArticleBundle:Article');
		$article = $articleRepo->findOneBySlug($slug);
		
		if($article){
			$em = $this->getDoctrine()->getManager();
			$em->remove($article);
			$em->flush();
		
			$session = $request->getSession();
			
			$session->getFlashBag()->add('notice', 'Article supprimé avec succés');
		}
		
		return $this->redirectToRoute('backoffice');
		
	}
}