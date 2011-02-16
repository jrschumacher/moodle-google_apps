<?php

if(!array_key_exists('header', $this->data)) {
	$this->data['header'] = 'selectidp';
}
$this->data['header'] = $this->t($this->data['header']);

$this->data['autofocus'] = 'preferredidp';

$this->includeAtTemplateBase('includes/header.php');

foreach ($this->data['idplist'] AS $idpentry) {
	if (isset($idpentry['name']))
		$this->includeInlineTranslation('idpname_' . $idpentry['entityid'], $idpentry['name']);
	if (isset($idpentry['description']))
		$this->includeInlineTranslation('idpdesc_' . $idpentry['entityid'], $idpentry['description']);
}


?>
	<div id="content">

		<h2><?php echo $this->data['header']; ?></h2>

		<form method="get" action="<?php echo $this->data['urlpattern']; ?>">
		<input type="hidden" name="entityID" value="<?php echo htmlspecialchars($this->data['entityID']); ?>" />
		<input type="hidden" name="return" value="<?php echo htmlspecialchars($this->data['return']); ?>" />
		<input type="hidden" name="returnIDParam" value="<?php echo htmlspecialchars($this->data['returnIDParam']); ?>" />
		
		<p><?php
		echo $this->t('selectidp_full');
		if($this->data['rememberenabled']) {
			echo('<br /><input type="checkbox" name="remember" value="1" />' . $this->t('remember'));
		}
		?></p>

		<?php

		
		if (!empty($this->data['preferredidp']) && array_key_exists($this->data['preferredidp'], $this->data['idplist'])) {
			$idpentry = $this->data['idplist'][$this->data['preferredidp']];
			echo '<div class="preferredidp">';
			echo '	<img src="/' . $this->data['baseurlpath'] .'resources/icons/star.png" style="float: right" />';

			if(array_key_exists('icon', $idpentry) && $idpentry['icon'] !== NULL) {
				$iconUrl = SimpleSAML_Utilities::resolveURL($idpentry['icon']);
				echo '<img style="float: left; margin: 1em; padding: 3px; border: 1px solid #999" src="' . htmlspecialchars($iconUrl) . '" />';
			}
			echo '<h3 style="margin-top: 8px">' . htmlspecialchars($this->t('idpname_' . $idpentry['entityid'])) . '</h3>';

			if (!empty($idpentry['description'])) {
				echo '	<p>' . htmlspecialchars($this->t('idpdesc_' . $idpentry['entityid'])) . '<br />';
			}
			echo('<input id="preferredidp" type="submit" name="idp_' .
				htmlspecialchars($idpentry['entityid']) . '" value="' .
				$this->t('select') . '" /></p>');
			echo '</div>';
		}
		
		
		foreach ($this->data['idplist'] AS $idpentry) {
			if ($idpentry['entityid'] != $this->data['preferredidp']) {

				if(array_key_exists('icon', $idpentry) && $idpentry['icon'] !== NULL) {
					$iconUrl = SimpleSAML_Utilities::resolveURL($idpentry['icon']);
					echo '<img style="clear: both; float: left; margin: 1em; padding: 3px; border: 1px solid #999" src="' . htmlspecialchars($iconUrl) . '" />';
				}
				echo '	<h3 style="margin-top: 8px">' . htmlspecialchars($this->t('idpname_' . $idpentry['entityid'])) . '</h3>';

				if (!empty($idpentry['description'])) {

					echo '	<p>' . htmlspecialchars($this->t('idpdesc_' . $idpentry['entityid'])) . '<br />';
				}
				echo('<input id="preferredidp" type="submit" name="idp_' .
					htmlspecialchars($idpentry['entityid']) . '" value="' .
					$this->t('select') . '" /></p>');
			}
		}
		
		?>
		</form>
		
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>
