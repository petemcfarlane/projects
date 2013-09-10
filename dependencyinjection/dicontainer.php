<?php

namespace OCA\Projects\DependencyInjection;


use \OCA\Projects\Core\API;
use \OCA\AppFramework\DependencyInjection\DIContainer as BaseContainer;
use \OCA\Projects\Controller\ProjectController;
use \OCA\Projects\Controller\DetailController;

class DIContainer extends BaseContainer {

    public function __construct(){
        parent::__construct('projects');
		
		$this['API'] = $this->share(function($c){
			return new API($c['AppName']);
		});

        // use this to specify the template directory
        $this['TwigTemplateDirectory'] = __DIR__ . '/../templates';

        $this['ProjectController'] = function($c){
            return new ProjectController($c['API'], $c['Request']);
        };

        $this['DetailController'] = function($c){
            return new DetailController($c['API'], $c['Request']);
        };
    }

}