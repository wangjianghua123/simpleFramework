<?php

/*
[UCenter] (C)2001-2099 Comsenz Inc.
This is NOT a freeware, use is subject to license terms

$Id: base.php 1059 2011-03-01 07:25:09Z monkey $
*/

!defined('IN_UC') && exit('Access Denied');

class basem {
	function load($model, $base = NULL, $release = '') {
		$base = $base ? $base : $this;
		if(empty($_ENV[$model])) {
			$release = !$release ? RELEASE_ROOT : $release;
			if(file_exists(UC_ROOT.$release."model/$model.php")) {
				require_once UC_ROOT.$release."model/$model.php";
			} else {
				require_once UC_ROOT."model/$model.php";
			}
                        $c=$model.'model';
			$_ENV[$model] = new $c($base);                      
		}
		return $_ENV[$model];
	}
}

?>