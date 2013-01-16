<?php

abstract class CubePoints_Module {

	abstract public function main();
	
	public function __construct( $cubepoints ) {
		$this->cubepoints = $cubepoints;
	}

} // end CubePointsModule class