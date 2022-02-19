<div id="<?= PRA_PREFIX_PLUGIN ?>-plugin-container">
	<div class="<?= PRA_PREFIX_PLUGIN ?>-masthead">
		<div class="<?= PRA_PREFIX_PLUGIN ?>-masthead__inside-container">
			<div class="<?= PRA_PREFIX_PLUGIN ?>-masthead__logo-container">
				<h1>Création de page d'activité</h1>
			</div>
		</div>
	</div>
	<div class="<?= PRA_PREFIX_PLUGIN ?>-lower">
		<details id="<?= PRA_PREFIX_PLUGIN ?>_detailForm" <?= isset($errors) && count($errors) > 0 ? 'open' : '' ?>>
			<summary>Formulaire de création</summary>
			<?php
			if (isset($errors)) {
			?>
				<div class='error'><?= implode('<br>', $errors); ?></div>
			<?php
			}
			?>
			<form action="<?php echo esc_url(Pra_Admin::get_page_url('saveActivity')); ?>" id="saveActivity" method="post">
				<fieldset>
					<p>
						<label for="activityName">Titre</label>
						<input type="text" id="activityName" name="activityName" value='<?= isset($activity) && isset($activity['activityName']) ? $activity['activityName'] : '' ?>'>
						<input type="hidden" id="activityId" name="activityId">
						<input type="hidden" id="action" name="action" value="create">
					</p>
					<p>
						<label for="activityDesc">Description</label>
						<textarea type="text" id="activityDesc" name="activityDesc"> <?= isset($activity) && isset($activity['activityDesc']) ? $activity['activityDesc'] : '' ?></textarea>
					</p>
					<p>
						<label for="activityAdmin">Roles administrateur sur cetle activité</label>
						<select multiple name="activityAdmin[]" id="activityAdmin">
							<?php
							foreach ($roles as $role) {
							?>
								<option value="<?= $role['name']; ?>"><?= $role['name']; ?></option>
							<?php
							}
							?>
						</select>
					</p>
					<button type="submit" name="Send" class="<?= PRA_PREFIX_PLUGIN ?>_submit">Enregistrer</button>
			</form>
		</details>
		<?php Pra::view('activitiesList', array('activities' => $activities)); ?>
	</div>
</div>