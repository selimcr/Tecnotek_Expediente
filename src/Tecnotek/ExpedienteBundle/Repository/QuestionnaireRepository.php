<?php
namespace Tecnotek\ExpedienteBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

class QuestionnaireRepository extends EntityRepository
{
    /**
     * Get the list of Questionnaires for a Type
     *
     * @param $type The type agroup the Questionnaires, i.e: 1=Psico Questionnaires
     *
     * @return array The list of Questionnaires
     */
    public function findQuestionnairesByType($type){
        return $this->getEntityManager()
            ->createQuery('SELECT q'
            . ' FROM TecnotekExpedienteBundle:Questionnaire q'
            . " WHERE q.type = $type"
            . " ORDER BY q.sortOrder ASC")
            ->getResult();
    }

    /**
     * @return array The list of the Questionnaires with type 1: Psico
     */
    public function findPsicoQuestionnaires(){
        return $this->findQuestionnairesByType(1);
    }

    public function findStudentQuestion($stdId, $questionId){
        $query = $this->getEntityManager()
            ->createQuery('SELECT q'
            . ' FROM TecnotekExpedienteBundle:QuestionnaireAnswer q'
            . " WHERE q.student = $stdId AND q.question = $questionId");
        $answer = new \Tecnotek\ExpedienteBundle\Entity\QuestionnaireAnswer();
        try {
            // The Query::getSingleResult() method throws an exception
            // if there is no record matching the criteria.
            $answer = $query->getSingleResult();
        } catch (NoResultException $e) {
            $question = $this->getEntityManager()
                ->createQuery('SELECT q'
                . ' FROM TecnotekExpedienteBundle:QuestionnaireQuestion q'
                . " WHERE q.id = $questionId")->getSingleResult();
            $answer->setQuestion($question);
        }
        return $answer;
    }

    public function findPsicoQuestionnairesAnswersOfStudent($stdId){
        $query = $this->getEntityManager()
            ->createQuery('SELECT q'
            . ' FROM TecnotekExpedienteBundle:QuestionnaireAnswer q'
            . " WHERE q.student = $stdId");
        $answer = null;
        try {
            // The Query::getSingleResult() method throws an exception
            // if there is no record matching the criteria.
            $answers = $query->getResult();
        } catch (NoResultException $e) {
        }
        return $answers;
    }
}
?>
