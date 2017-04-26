<?php

spl_autoload_register( function ( $class_name ) {
	$class_name = str_replace( '\\', '/', $class_name );
	$class_name = str_replace( 'PetrovEgor/', '', $class_name );
	$class_path = explode( '/', $class_name );
	$class_name = $class_path[ count( $class_path ) - 1 ];
	unset( $class_path[ count( $class_path ) - 1 ] );
	if ( count( $class_path ) > 0 ) {
		$namespace = $class_path[0] . '/';
	} else {
		$namespace = '';
	}
	$class_name = str_replace( '_', '-', strtolower( $class_name ) );
	$class_name = $namespace . 'class-' . $class_name;
	$file = __DIR__ . DIRECTORY_SEPARATOR . str_replace( '\\', '/', $class_name ) . '.php';
	if ( is_file( $file ) ) {
		require_once $file;
	}
});
