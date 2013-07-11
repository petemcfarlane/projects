<?php

// cloud/index.php/apps/projects/id/12345
$this->create('project_overview', 'id/{project_id}')->get()->action(
    function($params){
        require __DIR__ . '/../index.php';
    }
);

$this->create('project_view', 'id/{project_id}/{view}')->get()->action(
    function($params){
        require __DIR__ . '/../index.php';
    }
);

$this->create('item_view', 'id/{project_id}/{view}/{item}')->get()->action(
	function($params) {
		require __DIR__ . '/../index.php';
	}
);

$this->create('new_project')->post()->action(
	function($params) {
		require __DIR__ . '/../create.php';
	}
); 

$this->create('new_item', 'id/{project_id}/{view}')->post()->action(
	function($params) {
		require __DIR__ . '/../create.php';
	}
);