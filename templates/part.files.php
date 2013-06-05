<?php
// check for project root folder
if ($project['root_dir_id'] == 0) {
	// if no folder create one, owned by user
}

// Setup File System to 'Project_x'
OC_Util::setupFS('project_'.$project['id']);

$dir = isset($_GET['dir']) ? stripslashes($_GET['dir']) : '';

$files = array();
if (\OC\Files\Cache\Upgrade::needUpgrade(OC_User::getUser())) { //dont load anything if we need to upgrade the cache
	$content = array();
	$needUpgrade = true;
	$freeSpace = 0;
} else {
	$content = \OC\Files\Filesystem::getDirectoryContent($dir);
	$freeSpace = \OC\Files\Filesystem::free_space($dir);
	$needUpgrade = false;
}
foreach ($content as $i) {
	if (isset($i['mtime'])) $i['date'] = OCP\Util::formatDate($i['mtime']);

	if ($i['type'] == 'file') {
		$fileinfo = pathinfo($i['name']);
		$i['basename'] = $fileinfo['filename'];
		if (!empty($fileinfo['extension'])) {
			$i['extension'] = '.' . $fileinfo['extension'];
		} else {
			$i['extension'] = '';
		}
	}
	$i['directory'] = $dir;
	$files[] = $i;
}
print_r($files);
$maxUploadFilesize=OCP\Util::maxUploadFilesize($dir);
?>

<form id="upload-form" action="<?php print_unescaped(OCP\Util::linkTo('files', 'ajax/upload.php')); ?>" method="post" enctype="multipart/form-data">
	<input type="hidden" name="MAX_FILE_SIZE" id="max_upload" value="<?php p($maxUploadFilesize) ?>">
	<!-- Send the requesttoken, this is needed for older IE versions
	because they don't send the CSRF token via HTTP header in this case -->
	<input type="hidden" name="requesttoken" value="<?php p($_['requesttoken']) ?>" id="requesttoken">
	<input type="hidden" class="max_human_file_size" value="(max <?php p(OCP\Util::humanFileSize($maxUploadFilesize)); ?>)">
	<input type="hidden" name="dir" value="<?php p($dir) ?>" id="dir">
	<input type="file" id="file_upload_start" name='files[]'/>
</form>
