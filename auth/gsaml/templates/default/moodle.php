<?php 
	if (!array_key_exists('icon', $this->data)) $this->data['icon'] = 'lock.png';
	$this->includeAtTemplateBase('includes/header.php'); 
?>

	
	<div id="content">
	This is a test.
		<?php if (isset($this->data['error'])) { ?>
		<div style="border-left: 1px solid #e8e8e8; border-bottom: 1px solid #e8e8e8; background: #f5f5f5"
		<img src="/<?php echo $this->data['baseurlpath']; ?>resources/icons/bomb.png" style="float: left; margin: 15px " />
		<h2><?php echo $this->t('error_header'); ?></h2>
		
		<p><?php echo htmlspecialchars($this->data['error']); ?> </p>
		</div>
		<?php } ?>
	
		<h2 style="break: both"><?php echo $this->t('user_pass_header'); ?></h2>
		
		<p>
			<?php echo $this->t('user_pass_text'); ?>
		</p>
		
		<form action="?" method="post" name="f">

		<table>
			<tr>
				<td rowspan="3"><img src="/<?php echo $this->data['baseurlpath']; ?>resources/icons/pencil.png" /></td>
				<td style="padding: .3em;"><?php echo $this->t('username'); ?></td>
				<td><input type="text" tabindex="1" name="username" 
					<?php if (isset($this->data['username'])) {
						echo 'value="' . htmlspecialchars($this->data['username']) . '"';
					} ?> /></td>

					
				<td style="padding: .4em;" rowspan="3">
					<input type="submit" tabindex="3" value="Login" />
					<input type="hidden" name="RelayState" value="<?php echo htmlspecialchars($this->data['relaystate']); ?>" />
				</td>
			</tr>
			
			<tr>
				<td style="padding: .3em;"><?php echo $this->t('organization'); ?></td>
				<td><select name="org" tabindex="2">
					<?php
					
					foreach ($this->data['ldapconfig'] AS $key => $entry) {
						echo '<option ' .
							($key == $this->data['org'] ? 'selected="selected" ' : '')
							. 'value="' . htmlspecialchars($key) . '">' . htmlspecialchars($entry['description']) . '</option>';
					}
					
					?>
				</select></td>
			</tr>
			
			<tr>
				<td style="padding: .3em;"><?php echo $this->t('password'); ?></td>
				<td><input type="password" tabindex="2" name="password" /></td>
			</tr>
		</table>
		
		
		</form>
		
		
		<h2><?php echo $this->t('help_header'); ?>.</h2>
		
		
		<p><?php echo $this->t('help_text'); ?>!</p>
		
		
		
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>
