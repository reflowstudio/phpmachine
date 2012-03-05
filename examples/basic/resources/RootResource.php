<?php

namespace MyExample;


class RootResource extends \PHPMachine\Resource {

	public static function toHTML(\PHPMachine\Request $request, array &$context){
		return 'Welcome to PHPMachine';
	}

}
