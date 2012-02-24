<?php

namespace phpmachine_resource;

function log_d($id) {
	echo $id . "\n";
}

function do_fun($function, $state) {
	return array('pong', $state);
}