<?php

namespace OCA\Projects\Core;

class API extends \OCA\AppFramework\Core\API {

    public function __construct($appName){
        parent::__construct($appName);
    }


    // public function methodName($someParam){
       // \OCP\Util::methodName($this->appName, $someParam);
    // }

    public function getItemsSharedWith($itemType, $format=-1, $parameters=null, $limit=-1, $includeCollections=false) {
		return \OCP\Share::getItemsSharedWith($itemType, $format, $parameters, $limit, $includeCollections);
	}
	
	public function getItemSharedWith($itemType, $itemTarget, $format=-1, $parameters=null, $includeCollections=false) {
		return \OCP\Share::getItemSharedWith($itemType, $itemTarget, $format, $parameters, $includeCollections);
	}
}