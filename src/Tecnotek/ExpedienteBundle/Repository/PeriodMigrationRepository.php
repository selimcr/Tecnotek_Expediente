<?php
namespace Tecnotek\ExpedienteBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

class PeriodMigrationRepository extends CustomRepository {

    public function findLastMigrationInProgress() {
        $query = $this->getEntityManager()
            ->createQuery('SELECT pm'
                . ' FROM TecnotekExpedienteBundle:PeriodMigration pm'
                . ' WHERE pm.status <> 6'
                . " ORDER BY pm.id DESC");
        $answers = $query->getResult();
        return (sizeof($answers) > 0)? $answers[0]:null;
    }

    public function executeStep($step, $sourcePeriod, $destinationPeriod, $data) {
        $em = $this->getEntityManager();
        switch($step) {
            case 1:
                $sql = 'select ((max(id) - min(id) + 1) - count(*)) + count(*) FROM tek_groups where period_id = '
                    . $sourcePeriod->getId() . ';';
                $stmt = $em->getConnection()->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetchAll();
                $stmt->closeCursor();
                $diff = $result[0][0];
                $sql = 'INSERT INTO tek_groups (id, user_id, period_id, grade_id, name, institution_id)'
                    . ' SELECT (id+' . $diff . '),user_id,' . $destinationPeriod->getId() . ',grade_id,name,institution_id'
                    . ' FROM tek_groups '
                    . ' WHERE period_id = ' . $sourcePeriod->getId();
                $stmt = $em->getConnection()->prepare($sql);
                $stmt->execute();
                $stmt->closeCursor();
                return $diff;
            case 2:
                $numberOfCreatedGroups = $data["G"];
                $sql = 'INSERT INTO tek_students_year(student_id, period_id, group_id, conducta, periodAverageScore, periodHonor)'
                    . ' SELECT student_id,' . $destinationPeriod->getId() . ', (group_id+' . $numberOfCreatedGroups
                    . '),0,0,0'
                    . ' FROM tek_students_year '
                    . ' WHERE period_id = ' . $sourcePeriod->getId();
                break;
            case 3:
                $sql = 'select ((max(ate.id) - min(ate.id) + 1) - count(*)) + count(*) FROM tek_assigned_teachers ate'
                    . ' JOIN tek_groups g ON ate.group_id = g.id where g.period_id = ' . $sourcePeriod->getId() . ';';
                $stmt = $em->getConnection()->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetchAll();
                $stmt->closeCursor();
                $diff = $result[0][0];
                $sql = 'INSERT INTO tek_course_class(id,user_id, period_id, grade_id, course_id)'
                    . ' SELECT (id+' . $diff . '),user_id,' . $destinationPeriod->getId() . ',grade_id,course_id'
                    . ' FROM tek_course_class '
                    . ' WHERE period_id = ' . $sourcePeriod->getId();
                $stmt = $em->getConnection()->prepare($sql);
                $stmt->execute();
                $stmt->closeCursor();
                return $diff;
            case 4:
                $numberOfCreatedGroups = $data["G"];
                $numberOfCourseClasses = $data["CC"];
                $sql = 'INSERT INTO tek_assigned_teachers(user_id, group_id, course_class_id)'
                    . ' SELECT ate.user_id,ate.group_id+' . $numberOfCreatedGroups . ', ate.course_class_id + ' . $numberOfCourseClasses
                    . ' FROM tek_assigned_teachers ate'
                    . ' JOIN tek_groups g ON ate.group_id = g.id'
                    . ' WHERE g.period_id = ' . $sourcePeriod->getId();
                break;
            case 5:
                $sql = 'INSERT INTO tek_assigned_teachers(user_id, group_id, course_class_id)'
                    . ' SELECT 2,g.id,c.id'
                    . ' FROM tek_course_class c, tek_groups g '
                    . ' WHERE c.period_id = ' . $destinationPeriod->getId()
                    . ' AND g.period_id = ' . $destinationPeriod->getId() . ' AND g.grade_id = c.grade_id';
                break;
            case 6:
                $numberOfCourseClasses = $data["CC"];
                $sql = 'SELECT ((max(ce.id) - min(ce.id) + 1) - count(*)) + count(*) FROM tek_course_entries ce'
                    . ' JOIN tek_course_class cc ON ce.course_class_id = cc.id WHERE cc.period_id = ' . $sourcePeriod->getId() . ';';
                $stmt = $em->getConnection()->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetchAll();
                $stmt->closeCursor();
                $diff = $result[0][0];
                $sql = 'INSERT INTO tek_course_entries(id, course_class_id, parent_id, name, `code`, max_value, percentage, sort_order)'
                    . ' SELECT (ce.id+' . $diff . '), ce.course_class_id + ' . $numberOfCourseClasses . ', ce.parent_id, ce.name, ce.`code`, ce.max_value,'
                    . ' ce.percentage, ce.sort_order '
                    . ' FROM tek_course_entries ce '
                    . ' JOIN tek_course_class cc ON ce.course_class_id = cc.id '
                    . ' WHERE cc.period_id = ' . $sourcePeriod->getId() . ';';
                $stmt = $em->getConnection()->prepare($sql);
                $stmt->execute();
                $stmt->closeCursor();
                return $diff;
            case 7:
                $numberOfCreatedGroups = $data["G"];;
                $numberOfCourseEntries = $data["CE"];
                $sql = 'INSERT INTO tek_sub_course_entries(parent_id, group_id, name, code, max_value, percentage, sort_order)'
                    . ' SELECT sce.parent_id+' . $numberOfCourseEntries . ', sce.group_id+' . $numberOfCreatedGroups . ', sce.name, sce.code, sce.max_value, sce.percentage, sce.sort_order'
                    . ' FROM tek_sub_course_entries sce JOIN tek_groups g ON g.id = sce.group_id'
                    . ' WHERE g.period_id = ' . $sourcePeriod->getId() . ';';
                break;
            default:
                return null;
        }
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();
        $rowCount = $stmt->rowCount();
        $stmt->closeCursor();
        return $rowCount;
    }
}
?>
