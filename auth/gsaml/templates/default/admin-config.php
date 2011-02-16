<?php

$this->data['header'] = 'Configuration check';

if(array_key_exists('file', $this->data)) {
	$this->data['header'] .= ' - ' . htmlspecialchars($this->data['file']);
}

$this->includeAtTemplateBase('includes/header.php');

?>

<div id="content">
<h2><?php echo $this->data['header']; ?></h2>

<?php
if(array_key_exists('files', $this->data)) {
	/* File list. */
	echo('<p>Select configuration file to check:</p>');

	echo('<ul>');
	foreach($this->data['files'] as $file) {
		$fileName = htmlspecialchars($file['name']);
		if($file['available']) {
			$fileUrl = htmlspecialchars($this->data['url'] . '?file=' . urlencode($file['name']));
			echo('<li><a href="' . $fileUrl . '">' . $fileName . '</a></li>');
		} else {
			$reason = htmlspecialchars($file['reason']);
			echo('<li>' . $fileName . ' - ' . $reason . '</li>');
		}
	}
	echo('</ul>');

} else {
	/* File details. */

	$notices = $this->data['notices'];
	$missing = $this->data['missing'];
	$superfluous = $this->data['superfluous'];

	if(count($notices) > 0) {
		echo('<h3>Notices</h3>');
		echo('<ul>');
		foreach($notices as $i) {
			$type = $i['type'];
			if($type === 'error') {
				$image = 'bomb.png';
			} elseif($type === 'warning') {
				$image = 'caution.png';
			}
			$imageUrl = '/' . $this->data['baseurlpath'] . 'resources/icons/' . $image;

			echo('<p>');
			echo('<img style="display: inline; float: left; width: 1.7em; height: 1.7em;" src="' . htmlspecialchars($imageUrl) . '" alt="' . htmlspecialchars($type) . '" />');
			echo(htmlspecialchars($i['message']));
			echo('</p>');
		}
		echo('</ul>');
	}

	if(count($missing) > 0) {
		echo('<h3>Options missing from config file</h3>');
		echo('<ul>');
		foreach($missing as $i) {
			echo('<li>' . htmlspecialchars($i) . '</li>');
		}
		echo('</ul>');
	}

	if(count($superfluous) > 0) {
		echo('<h3>Superfluous options in config file</h3>');
		echo('<ul>');
		foreach($superfluous as $i) {
			echo('<li>' . htmlspecialchars($i) . '</li>');
		}
		echo('</ul>');
	}

	if(count($notices) === 0 && count($missing) === 0 && count($superfluous) === 0) {
		echo('<p>No errors found.</p>');
	}

	echo('<p><a href="' . htmlspecialchars($this->data['url']) . '">Go back to the file list</a></p>');
}

$this->includeAtTemplateBase('includes/footer.php');
?>
