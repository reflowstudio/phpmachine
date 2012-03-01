<?php

namespace MyExample;


class BasicResource extends \PHPMachine\Resource {

	public static function toHTML(\PHPMachine\Request $request, array &$context){
		return 'Hello World!';
	}

}