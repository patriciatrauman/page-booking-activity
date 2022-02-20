document.addEventListener('DOMContentLoaded', (event) => {
  // const form = document.querySelector('.pra_submit');
  const moveEventToEditForm = document.querySelectorAll('.editPra');
  const flush = document.querySelectorAll('.trashPra');
  if (flush !== undefined && flush !== null) {
    flush.forEach(elem => {
      elem.addEventListener('click', function () {
        document.getElementById('activityId').value = this.dataset.activityId;
        document.getElementById('action').value = 'delete';
        document.getElementById('saveActivity').submit();
      })
    })
  }
  if (moveEventToEditForm !== undefined && moveEventToEditForm !== null) {
    moveEventToEditForm.forEach(editPage => {
      editPage.addEventListener('click', function () {
        let details = document.getElementById('pra_detailForm');
        details.children[0].textContent = 'Formulaire de modification';
        details.open = true;
        document.getElementById('activityName').value = this.dataset.name;
        document.getElementById('activityId').value = this.dataset.activityId;
        document.getElementById('activityDesc').value = this.dataset.activityDesc;
        document.getElementById('action').value = 'update';
        let admins = JSON.parse(this.dataset.admins);
        Object.keys(document.getElementById('activityAdmin').getElementsByTagName('option')).forEach(optionKey => {
          let option = document.getElementById('activityAdmin').getElementsByTagName('option')[optionKey];
          if (admins.includes(option.innerHTML)) {
            option.selected = 'selected';
          }
        })
        console.log("details", details)
        console.log('this.dataset', this.dataset)
      })
    })
  }
})