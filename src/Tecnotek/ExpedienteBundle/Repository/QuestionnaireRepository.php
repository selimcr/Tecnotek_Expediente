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

    /**
     * @return array The list of the Questionnaires with type 1: Psico and in a group
     */
    public function findPsicoQuestionnairesOfGroup($group, $onlyForTeachers = false,
                                                   \Tecnotek\ExpedienteBundle\Entity\Student $student){
        $teachers = "";
        if($onlyForTeachers){
            $teachers = " AND q.enableForTeacher = true";
        }

        // Get Institution of Student
        $studentYear = $this->getEntityManager()
            ->createQuery('SELECT stdy'
            . ' FROM TecnotekExpedienteBundle:StudentYear stdy'
            . ' JOIN stdy.period p '
            . " WHERE p.isActual = 1 AND stdy.student = " . $student->getId())
            ->getResult();

        $institutionId = 0;
        if( isset($studentYear[0]) ){
            $stdGroup = $studentYear[0]->getGroup();
            if( $stdGroup ){
                $institutionId = $stdGroup->getInstitution()->getId();
            }
        }

         return $this->getEntityManager()
             ->createQuery('SELECT q'
             . ' FROM TecnotekExpedienteBundle:Questionnaire q'
             . ' JOIN q.institutions ins'
             . " WHERE q.group = " . $group->getId() . $teachers
             . ' AND ins.id in (' . $institutionId . ')'
             . " ORDER BY q.sortOrder ASC")
             ->getResult();
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

    public function findPsicoQuestionnairesAnswersOfStudentByGroup($stdId, $group, $onlyForTeachers = false){
        $teachers = "";
        if($onlyForTeachers){
            $teachers = " AND qe.enableForTeacher = true";
        }

        $query = $this->getEntityManager()
            ->createQuery('SELECT q'
            . ' FROM TecnotekExpedienteBundle:QuestionnaireAnswer q'
            . ' JOIN q.question question'
            . ' JOIN question.questionnaire qe'
            . " WHERE q.student = $stdId" . $teachers
            . " AND qe.group = " . $group->getId());
        $answer = null;
        try {
            $answers = $query->getResult();
        } catch (NoResultException $e) {
        }
        return $answers;
    }
}
?>
