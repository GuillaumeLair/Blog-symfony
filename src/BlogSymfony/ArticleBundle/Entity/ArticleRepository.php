<?php

namespace BlogSymfony\ArticleBundle\Entity;

use BlogSymfony\ArticleBundle\Controller\DefaultController;
use \DateTime;

/**
 * ArticleRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ArticleRepository extends \Doctrine\ORM\EntityRepository
{
	public function getArticleForPage($page) {
		$offset = ($page-1) * DefaultController::NB_ARTICLES_PAR_PAGE;
		
		$query = $this->createQueryBuilder('a')
						->where ('a.date < :now' )
						->setParameter ('now', new DateTime())
						->orderBy('a.date','DESC')
						->setFirstResult($offset)
						->setMaxResults(DefaultController::NB_ARTICLES_PAR_PAGE)
						->getQuery();
						
		return $query->getResult();
	}
	
	public function getNbArticles() {
		$query = $this->createQueryBuilder('a')
						->select('count(a)')
						->where ('a.date < :now' )
						->setParameter ('now', new DateTime());
		
		return $query->getQuery()->getSingleScalarResult();
	}
}
