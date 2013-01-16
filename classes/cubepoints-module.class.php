<?php

abstract class CubePointsModule {

	abstract public function main();
	
	public function __construct( $cubepoints ) {
		$this->cubepoints = $cubepoints;
	}

} // end CubePointsModule class