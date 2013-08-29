<?php
/*
namespace OCA\SalesQuestionnaire\Lib;

class SearchProvider extends \OC_Search_Provider{

	function search($query) {
		$query = \OCP\DB::prepare('SELECT `id`, `customer`, `project_name` FROM `*PREFIX*salesquestionnaire` WHERE '
			. '`customer` LIKE "%'.$query.'%" '
			. 'OR `project_name` LIKE "%'.$query.'%" '
			. 'OR `uid` LIKE "%'.$query.'%" '
			. 'OR `platform` LIKE "%'.$query.'%" '
			. 'OR `territories` LIKE "%'.$query.'%" '
			. 'OR `oem` LIKE "%'.$query.'%"');
		$result = $query->execute();
		$questionnaires = $result->fetchAll();

		$searchresults = array();
		foreach ($questionnaires as $questionnaire) {
			$searchresults[] = new \OC_Search_Result($questionnaire['customer'], $questionnaire['project_name'], \OCP\Util::linkToRoute('salesquestionnaire.questionnaire.show', array('Id'=>$questionnaire['id']) ), "Sales Qs" ); //$name,$text,$link,$type
		}

		return $searchresults;
	}

}
*/
