<?php
// Internal linking script
$hardlink_files = array(
);

$symlink_files = array(
);

$symlink_folders = array(
	'../fof/fof' => 'component/fof',
	'../fof/strapper' => 'component/strapper',
);

$path = dirname(__FILE__);

if(!empty($hardlink_files)) foreach($hardlink_files as $from => $to) {
	if(is_file($path.'/'.$to)) {
		unlink($path.'/'.$to);
	}
	link($path.'/'.$from, $path.'/'.$to);
}

if(!empty($symlink_files)) foreach($symlink_files as $from => $to) {
	if(is_file($path.'/'.$to) || is_link($path.'/'.$to)) {
		unlink($path.'/'.$to);
	}
	symlink($path.'/'.$from, $path.'/'.$to);
}

if(!empty($symlink_folders)) foreach($symlink_folders as $from => $to) {
	if(is_dir($path.'/'.$to) || is_link($path.'/'.$to)) {
		unlink($path.'/'.$to);
	}
	symlink($path.'/'.$from, $path.'/'.$to);
}