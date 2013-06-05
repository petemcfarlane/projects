<?php

// cloud/index.php/apps/projects/12345
$this->create('project_overview', 'id/{projectID}')->get()->action(
    function($params){
        require __DIR__ . '/../index.php';
    }
);

$this->create('project_view', 'id/{projectID}/{view}')->get()->action(
    function($params){
        require __DIR__ . '/../index.php';
    }
);
