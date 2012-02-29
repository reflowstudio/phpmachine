<?php

namespace Tests\PHPMachine;


class TestResource extends \PHPMachine\Resource {

	public static function toHTML(\PHPMachine\Request $request, array &$context){
		return 'Awesome!';
	}

}