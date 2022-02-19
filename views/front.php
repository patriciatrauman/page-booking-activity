<?php
/*Bloc présentation de l'évenement*/

?>
<p>
<div contenteditable="<?= $isUserAdmin ? 'true' : 'false' ?>" data-id-event=<?= $event->id ?> data-action='textPres' id='textPresDiv'><?= stripslashes($event->description) ?></div>
<form id="textPresForm" action="<?= home_url($rq) ?>" method="post" style="display: none;">
  <textarea name="description" id="textPresDesc"></textarea>
  <input type="hidden" name="eventId" value="<?= $event->id ?> ">
</form>


<?php
if ($isUserAdmin) {
?>
  <!--span class="material-icons" data-id-event=<?= $event->id ?> data-action='editPres'>edit</span-->
  <span class="material-icons cursorPointer savePres" data-id-event=<?= $event->id ?> data-action='savePres'>save</span>
<?php
}
?>
</p>

<?php
/*Liste des dates bookable
 * - Possibilité de booker pour tout utilisateur s'il reste de la place
 * - Pour les admins
 *  - Possibilité de voir qui est inscrit
 *  - Possibilité d'ajouter/modifier/supprimer des dates
 */
?>
<?php
if ($isUserAdmin) {
?>
  <span class="labelAddDate">
    <span class="material-icons cursorPointer openAddDate" data-id-event=<?= $event->id ?> data-action='openAddDate'>add_circle</span>
    <span class="material-icons cursorPointer closeAddDate" data-id-event=<?= $event->id ?> data-action='closeAddDate' style="display:none">cancel</span>
    Ajouter des dates
  </span>
  <div id="addDates" style="display:none">

    <form id="addDateForm" action="<?= home_url($rq) ?>" method="post">
      <!--fieldset class="addAddDate"><span class="material-icons cursorPointer addAnotherDate"  data-action='addAnotherDate'>add_circle</span></fieldset-->
      <fieldset class="formAddDate">
        <label for="formAddDate_dateCal">Date</label>
        <input type="date" id="formAddDate_dateCal" name="dateCal" min="<?= date('Y-m-d') ?>">
        <label for="formAddDate_timeCal">Heure</label>
        <input type="time" id="formAddDate_timeCal" name="timeCal">
        <label for="formAddDate_nbCal">Ouverture à nb personne</label>
        <input type="number" id="formAddDate_nbCal" name="nbCal" min="1" max="15">
        <input type="hidden" id="formAddDate_postId" name="postId" value="<?= $postId ?>">
        <input type="hidden" id="formAddDate_eventId" name="eventId" value="<?= $event->id ?>">
        <input type="hidden" id="formAddDate_action" name="action" value="add">
        <input type="hidden" id="formAddDate_idCal" name="idCal" value="">
      </fieldset>
      <fieldset>
        <span class="material-icons cursorPointer saveAddDate" data-id-event=<?= $event->id ?> data-action='saveAddDate'>save</span>
      </fieldset>
    </form>
  </div>
<?php
}
?>

<form action="<?= home_url($rq) ?>" id='deleteCal' method='post' style="display:none">
  <input type="hidden" name="action" value="delete">
  <input type="hidden" name="eventId" value="<?= $event->id ?>">
  <input type="hidden" name="calId" id="calIdToDelete" value="">
</form>
<form action="<?= home_url($rq) ?>" id='bre_cal_book' method='post'>
  <input type="hidden" name="idEvent" value<?= $event->id ?>>
  <fieldset>
    <legend>Réservez votre participation
      <span class="material-icons cursorPointer saveBooking" data-id-event=<?= $event->id ?> data-action='saveBooking'>save</span>
    </legend>
  </fieldset>
  <fieldset>
    <?php
    if (count($calendar) > 0) {
      foreach ($calendar as $key => $cal) {
        $nbPersonneBooked = is_null($cal->nbBooked) ? 0 : $cal->nbBooked;
        $isBooked = is_null($cal->booked) ? false : true;
        $isMayNeedToBeOpen = $isUserAdmin && $nbPersonneBooked > 0;
        $mayBeUpdatedOrDeleted = $isUserAdmin;
    ?>
        <label for="timeCal">Le <?= $cal->jour ?> à <?= $cal->heure ?> <?= $nbPersonneBooked ?>/<?= $cal->nbPersonne ?>
          <?php if ($isMayNeedToBeOpen) {
          ?>
            <span class="material-icons cursorPointer openDetailBooking" data-id-cal=<?= $cal->id ?> data-action='openDetailBooking' data-name='<?= implode(',', $cal->users) ?>'>info</span>
          <?php
          } ?>
          <?php if ($mayBeUpdatedOrDeleted) {
          ?>
            <span class="material-icons cursorPointer updateCal" data-id-cal=<?= $cal->id ?> data-jour="<?= $cal->jourTech ?>" data-nbPersonne="<?= $cal->nbPersonne ?>" data-heure="<?= $cal->heure ?>" data-action='updateCal'>edit</span>
            <span class="material-icons cursorPointer deleteCal" data-id-cal=<?= $cal->id ?> data-action='deleteCal'>delete</span>
          <?php
          } ?>
        </label>
        <?php
        if (!($nbPersonneBooked == $cal->nbPersonne && $isBooked == false)) {
        ?>
          <input type="checkbox" name="booking[<?= $cal->id ?>]" data-id-day='<?= $cal->id ?>' <?= $isBooked ? 'checked' : '' ?>>
          <!-- <input type="hidden" name="calId[]" data-id-day='<?= $cal->id ?>' value<?= $cal->id ?>> -->
        <?php
        } else {
        ?>
          <span>Complet</span>
        <?php
        }
        ?>
        <br />
      <?php
      }
    } else {
      ?>
      Pas de dates disponible pour l'instant
    <?php
    }
    ?>
    <input type='hidden' name='idEvent' value='<?= $event->id ?>' />
  </fieldset>
</form>