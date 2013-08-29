<?php

namespace OCA\Projects\DependencyInjection;

use \OCA\AppFramework\DependencyInjection\DIContainer as BaseContainer;
use \OCA\Projects\Controller\ProjectController;

class DIContainer extends BaseContainer {

    public function __construct(){
        parent::__construct('projects');
		
        // use this to specify the template directory
        $this['TwigTemplateDirectory'] = __DIR__ . '/../templates';

        $this['ProjectController'] = function($c){
            return new ProjectController($c['API'], $c['Request']);
        };
    }

}