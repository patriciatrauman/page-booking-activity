<h2>Liste d'activités</h2>
<table>
  <tr>
    <th>Nom</th>
    <th>Description</th>
    <th>Roles administrateur</th>
    <th>&nbsp;</th>
  </tr>
  <?php
  foreach ($activities as $activity) {
  ?>
    <tr>
      <td style="vertical-align: top;"><?= $activity->name ?></td>
      <td style="vertical-align: top;"><?= stripslashes($activity->description) ?></td>
      <td style="vertical-align: top;"><?= implode(', ', $activity->admins) ?></td>
      <td style="vertical-align: top;">
        <span class="material-icons cursorPointer editPra" style="cursor:pointer" data-activity-id="<?= $activity->id ?>" data-activity-desc="<?= $activity->description ?>" data-admins='<?= json_encode($activity->admins) ?>' data-name="<?= $activity->name ?>">edit</span>
        <span class="material-icons cursorPointer trashPra" style="cursor:pointer" data-activity-id="<?= $activity->id ?>">delete</span>
      </td>
    </tr>
  <?php
  }
  ?>
</table>