<?php

namespace Damis\ExperimentBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
*
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ExperimentRepository extends EntityRepository
{
    /**
     * Finds specified limit of closable experiments (which have all successfully finished tasks) - for successfully finishing
     *
     * @param $limit
     * @return array
     */
    public function getClosableExperiments($limit = 100)
    {
        $query = $this->createQueryBuilder('e')
            ->select('e')
            ->leftJoin('e.workflowtasks', 'w', 'with', 'w.experiment = e and (w.workflowtaskisrunning = 0 or w.workflowtaskisrunning = 1 or w.workflowtaskisrunning = 3)')
            ->andWhere('e.status = 2')
            ->andWhere('w.workflowtaskid is null')
            ->andWhere('e.start < CURRENT_TIMESTAMP()')
            ->setMaxResults($limit);

        return $query->getQuery()->getResult();
    }

    /**
     * Finds specified limit of closable experiments (which have at least one task with error status) - for error status
     *
     * @param int $limit
     * @return array
     */
    public function getClosableErrExperiments($limit = 100)
    {
        $query = $this->createQueryBuilder('e')
            ->select('e')
            ->leftJoin('e.workflowtasks', 'w', 'with', 'w.experiment = e and w.workflowtaskisrunning = 3')
            ->andWhere('e.status = 2')
            ->andWhere('w.workflowtaskid is not null')
            ->setMaxResults($limit);

        return $query->getQuery()->getResult();
    }

    /**
     * Returns all user experiments
     *
     * @param int $user
     * @return \Doctrine\ORM\Query
     */
    public function getUserExperiments($user)
    {
        $query = $this->createQueryBuilder('e')
            ->select('e')
            ->andWhere('e.user = :user')
            ->andWhere('e.status <> 6')
            ->setParameter('user', $user)
            ->addOrderBy('e.id', 'DESC');

        return $query->getQuery();
    }

    /**
     * Returns all user experiments
     *
     * @param int $user
     * @return \Doctrine\ORM\Query
     */
    public function getExperimentsExamples()
    {
        $query = $this->createQueryBuilder('e')
            ->select('e')
            ->andWhere('e.status = 6')
            ->addOrderBy('e.id', 'DESC');

        return $query->getQuery();
    }
    
    /**
     * Getting last experiment name
     *
     * @param int user id
     * @return string
     */
    public function getNextExperimentNameNumber($userId)
    {
        $em = $this->getEntityManager();
        $dql = $em->createQuery("
            SELECT SUBSTRING(tbl.name, 1, 3) as name, SUBSTRING(tbl.name FROM 4) as nr
            FROM DamisExperimentBundle:Experiment tbl
            WHERE SUBSTRING(tbl.name, 1, 3) = 'exp' AND tbl.user = $userId
            ORDER BY tbl.id DESC
        ")->setMaxResults(1);
        
        $result = $dql->getResult();

        if (isset($result[0]['nr'])) {
            $nr = $result[0]['nr'] + 1;
            if ($nr < 10) {
                return '00'.$nr;
            } elseif ($nr < 100)
                return '0'.$nr;
            else {
                return $nr;
            }
        }

        return '000';

    }
}
