
document.addEventListener('DOMContentLoaded', (event) => {
  const savePres = document.querySelector('.savePres');
  if (savePres !== null && savePres !== undefined) {
    savePres.addEventListener('click', function () {
      let element = document.getElementById('textPresDiv');
      let form = document.getElementById('textPresForm');
      let textPresDesc = document.getElementById('textPresDesc');
      textPresDesc.value = element.innerHTML;
      form.submit();
    })
  }

  const deleteCalElement = document.querySelectorAll('.deleteCal');
  if (deleteCalElement !== null) {
    deleteCalElement.forEach(deleteElement => {
      deleteElement.addEventListener('click', function () {
        let form = document.getElementById('deleteCal');
        document.getElementById('calIdToDelete').value = this.dataset.idCal;
        form.submit();
      })
    })
  }

  const updateCalElement = document.querySelectorAll('.updateCal');
  if (updateCalElement !== null) {
    updateCalElement.forEach(updateElement => {
      updateElement.addEventListener('click', function () {
        document.getElementById('formAddDate_dateCal').value = this.dataset.jour;
        document.getElementById('formAddDate_timeCal').value = this.dataset.heure;
        document.getElementById('formAddDate_idCal').value = this.dataset.idCal;
        document.getElementById('formAddDate_nbCal').value = this.dataset.nbpersonne;
        document.getElementById('formAddDate_action').value = 'update';
        let formElement = document.getElementById('addDates');
        formElement.style.display = 'block';
        closeAddDateElement.style.display = 'inline';
        openAddDateElement.style.display = `none`;
      })
    })
  }
  const openDetailBookingElement = document.querySelectorAll('.openDetailBooking');
  if (openDetailBookingElement !== null) {
    openDetailBookingElement.forEach(openDetailBooking => {
      openDetailBooking.addEventListener('click', function () {
        alert('Les personnes enregistrées sont : ' + this.dataset.name);
      })
    })
  }

  const saveAddDateElement = document.querySelector('.saveAddDate');
  if (saveAddDateElement !== null) {
    saveAddDateElement.addEventListener('click', function () {
      let fieldSetElement = document.querySelector('.formAddDate');
      let dateToCheck = fieldSetElement.children[1].value;
      let timeToCheck = fieldSetElement.children[3].value;
      let nbPersonne = fieldSetElement.children[5].value;
      if (checkDate(dateToCheck) && checkTime(timeToCheck) && checkNbPersonne(nbPersonne)) {
        document.getElementById('addDateForm').submit();
      }
    })
  }

  const labelAddDateElement = document.querySelector('.labelAddDate');
  if (labelAddDateElement !== null) {
    labelAddDateElement.addEventListener('click', function () {
      let formElement = document.getElementById('addDates');
      if (formElement.style.display == 'none') {
        formElement.style.display = 'block';
        closeAddDateElement.style.display = 'inline';
        openAddDateElement.style.display = `none`;
        document.getElementById('formAddDate_action').value = 'insert';
      } else {
        let today = new Date();
        let month = '' + (today.getMonth() + 1);
        let day = '' + today.getDate();
        let initDate = [today.getFullYear(), (month.length < 2 ? '0' + month : month), day.length < 2 ? '0' + day : day].join('-');

        document.getElementById('formAddDate_dateCal').value = initDate;
        document.getElementById('formAddDate_timeCal').value = '00:00';
        document.getElementById('formAddDate_idCal').value = 'null';
        document.getElementById('formAddDate_nbCal').value = 0;
        document.getElementById('formAddDate_action').value = 'insert';
        formElement.style.display = 'none';
        openAddDateElement.style.display = 'inline';
        closeAddDateElement.style.display = `none`;
      }
    })
  }

  const saveBookingElement = document.querySelector('.saveBooking');
  if (saveBookingElement !== null) {
    saveBookingElement.addEventListener('click', function () {
      document.getElementById('bre_cal_book').submit();
    })
  }

})



function checkDate(dateToCheck) {
  let date = new Date(dateToCheck)
  if (date == 'Invalid Date') {
    alert('Date Invalide, merci de modifier pour pouvoir enregistrer')
    return false
  } else {
    return true
  }
}

function checkTime(timeToCheck) {
  if (timeToCheck.trim() == '') {
    alert('Heure est un champs obligatoire')
    return false
  } else {
    if (timeToCheck.includes(':')) {
      let parts = timeToCheck.split(':');
      if (isNaN(parts[0]) || isNaN(parts[1])) {
        alert('Heure Invalide, merci de modifier pour pouvoir enregistrer')
        return false
      } else {
        if (parts[0] > 23 || parts[1] > 59) {
          alert('Heure Invalide, merci de modifier pour pouvoir enregistrer')
          return false
        }
      }
    }
  }
  return true;
}

function checkNbPersonne(nbPersonne) {
  if (nbPersonne == undefined || nbPersonne.trim() == '') {
    alert('Le nombre de personne est un champs obligatoire')
    return false
  } else {
    if (isNaN(nbPersonne)) {
      alert('Nombre de personne Invalide, merci de modifier pour pouvoir enregistrer')
      return false
    }
  }
  return true;
}
