document.addEventListener("DOMContentLoaded", () => {
    $.fn.modal.Constructor.prototype._enforceFocus = function() {};

    showDate();
    showTime();

    $('#input-type').hide();

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('.select2').select2({
        theme: "bootstrap-5",
        width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
        placeholder: $( this ).data( 'placeholder' ),
    });
});

// General

// show date
function showDate() {
    if (document.getElementById("tanggal").value == "" || document.getElementById("tanggal").value == null) {
        let date = new Date();

        let dateFormat = setDateFormat(date);

        if (document.getElementById("tanggal")) {
            document.getElementById("tanggal").value = dateFormat;
        }
    }
}

// show time
function showTime() {
    let date = new Date();
    let h = date.getHours(); // 0 - 23
    let m = date.getMinutes(); // 0 - 59
    let s = date.getSeconds(); // 0 - 59
    let session = " AM";

    if(h == 0){
        h = 12;
    }

    if(h == 12){
        session = " PM";
    }

    if(h > 12){
        h = h - 12;
        session = " PM";
    }

    h = (h < 10) ? "0" + h : h;
    m = (m < 10) ? "0" + m : m;
    s = (s < 10) ? "0" + s : s;

    let time = h + ":" + m + session;

    if (document.getElementById("jam")) {
        document.getElementById("jam").value = time;
    }

    setTimeout(showTime, 1000);
}

// yy-mm-dd format
function setDateFormat(date) {
    var d = new Date(date),
        month = "" + (d.getMonth() + 1),
        day = "" + d.getDate(),
        year = d.getFullYear();

    if (month.length < 2)
        month = "0" + month;
    if (day.length < 2)
        day = "0" + day;

    return [year, month, day].join("-");
}

// Format date to YYYY-MM-DD
function formatDate(date) {
    var dateObj = new Date(date);

    return [
        dateObj.getFullYear(),
        pad(dateObj.getMonth() + 1),
        pad(dateObj.getDate()),
    ].join('-');
}

function formatDateLocal(date) {
    let months = [{ 'angka': 1, 'nama': 'Januari' }, { 'angka': 2, 'nama': 'Februari' }, { 'angka': 3, 'nama': 'Maret' }, { 'angka': 4, 'nama': 'April' }, { 'angka': 5, 'nama': 'Mei' }, { 'angka': 6, 'nama': 'Juni' }, { 'angka': 7, 'nama': 'Juli' }, { 'angka': 8, 'nama': 'Agustus' }, { 'angka': 9, 'nama': 'September' }, { 'angka': 10, 'nama': 'Oktober' }, { 'angka': 11, 'nama': 'November' }, { 'angka': 12, 'nama': 'Desember' }];

    var dateObj = new Date(date);

    return [
        pad(dateObj.getDate()),
        months[dateObj.getMonth()]['nama'],
        dateObj.getFullYear(),
    ].join(' ');
}

function formatDateTime(date) {
    var dateObj = new Date(date);

    var date = "0" + dateObj.getDate();
    var month = "0" + (dateObj.getMonth() + 1);
    var year = dateObj.getFullYear();

    var dateMonthYear = year + "-" + month.substr(-2) + "-" + date.substr(-2);

    // Hours part from the timestamp
    var hours = "0" + dateObj.getHours();

    // Minutes part from the timestamp
    var minutes = "0" + dateObj.getMinutes();

    // Seconds part from the timestamp
    var seconds = "0" + dateObj.getSeconds();

    // Will display time in 10:30:23 format
    var time = hours.substr(-2) + ':' + minutes.substr(-2) + ':' + seconds.substr(-2);

    return dateMonthYear + " " + time;
}

// Authentication
function login(e, evt) {
    evt.preventDefault();

    $.ajax({
        url: e.getAttribute('action'),
        type: e.getAttribute('method'),
        data: new FormData(e),
        processData: false,
        contentType: false,
        success: function(res) {
            if (res.status == 200) {
                console.log(res.message);
                location.href = res.redirect;
            } else {
                console.error(res.message);
                for(let i = 0;i < res.additional.length;i++) {
                    document.getElementById(res.additional[i]).classList.add('is-invalid');
                }
                iziToast.error({
                    title: 'Error',
                    message: res.message,
                    position: 'topCenter'
                });
            }
        }, error: function (jqXHR) {
            let res = jqXHR.responseJSON;
            let message = '';
            console.log(res.message);
            for (let key in res.errors) {
                message += res.errors[key]+' ';
                document.getElementById(key).classList.add('is-invalid');
            };
            iziToast.error({
                title: 'Error',
                message: message,
                position: 'topCenter'
            });
        }
    });
}

function logout(url) {
    Swal.fire({
        title: 'Logout?',
        showConfirmButton: true,
        showDenyButton: true,
        confirmButtonText: 'Logout',
        confirmButtonColor: '#6531a0',
        denyButtonText: 'Cancel',
      }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: url,
                type: 'post',
                data: {confirmed : result.isConfirmed},
                success: function(res) {
                    if (res.status == 200) {
                        console.log(res.message);
                        location.href = res.redirect;
                    }
                }
            });
        }
    });
}

// filter modal
function showFilterModal() {
    $("#filter-modal").modal("show");
}

function hideFilterModal() {
    $("#filter-modal").modal("hide");
}

// reject modal
function showRejectModal() {
    $("#reject-modal").modal("show");
}

function hideRejectModal() {
    $("#reject-modal").modal("hide");
}

// rework
function reworkConfirmation() {
    Swal.fire({
        icon: 'info',
        title: 'REWORK this defect?',
        html: `<table class="table text-start w-auto mx-auto">
                    <tr>
                        <td>ID<td>
                        <td>:<td>
                        <td>?<td>
                    <tr>
                    <tr>
                        <td>Size<td>
                        <td>:<td>
                        <td>?<td>
                    <tr>
                    <tr>
                        <td>Defect Type<td>
                        <td>:<td>
                        <td>?<td>
                    <tr>
                    <tr>
                        <td>Defect Area<td>
                        <td>:<td>
                        <td>?<td>
                    <tr>
                </table>`,
        showConfirmButton: true,
        showDenyButton: true,
        confirmButtonText: 'Rework',
        confirmButtonColor: '#447efa',
        denyButtonText: 'Batal',
    }).then((result) => {
        if (result.isConfirmed) {
            location.reload;
        } else if (result.isDenied) {
            Swal.fire({
                icon: 'info',
                title: 'REWORK Canceled',
                confirmButtonText: 'Ok',
                confirmButtonColor: '#447efa',
            })
        }
    });
}

// qty input
function increment(id) {
    let element = document.getElementById(id);
    element.value = parseInt(element.value) + 1;
}

function decrement(id) {
    let element = document.getElementById(id);
    element.value = parseInt(element.value) - 1;
}

// popup notification
function showNotification(type, message) {
    switch (type) {
        case 'info' :
            iziToast.info({
                title: 'Information',
                message: message,
                position: 'topCenter'
            });
            break;
        case 'success' :
            iziToast.success({
                title: 'Success',
                message: message,
                position: 'topCenter'
            });
            break;
        case 'warning' :
            iziToast.warning({
                title: 'Warning',
                message: message,
                position: 'topCenter'
            });
            break;
        case 'error' :
            iziToast.error({
                title: 'Error',
                message: message,
                position: 'topCenter'
            });
            break;
    }
}

// enable form
function enableForm(element, elementOppositionId, formId) {
    // hide this element
    element.classList.remove("d-block");
    element.classList.add("d-none");

    // show opposition element
    document.getElementById(elementOppositionId).classList.remove("d-none");
    document.getElementById(elementOppositionId).classList.add("d-block");

    // form
    let form = document.getElementById(formId);
    let formElements = form.elements;

    for (let i = 0; i < formElements.length; i++) {
        if (formElements[i].type != 'submit' && formElements[i].type != 'button') {
            formElements[i].disabled = false;
        } else {
            formElements[i].classList.remove('d-none');
            formElements[i].classList.add('d-block');
        }
    }
}

// disable form
function disableForm(element, elementOppositionId, formId) {
    // hide this element
    element.classList.remove("d-block");
    element.classList.add("d-none");

    // show opposition element
    document.getElementById(elementOppositionId).classList.remove("d-none");
    document.getElementById(elementOppositionId).classList.add("d-block");

    // form
    let form = document.getElementById(formId);
    let formElements = form.elements;

    for (let i = 0; i < formElements.length; i++) {
        if (formElements[i].type != 'submit' && formElements[i].type != 'button') {
            formElements[i].disabled = true;
        } else {
            formElements[i].classList.remove('d-block');
            formElements[i].classList.add('d-none');
        }
    }
}

// Update Profile
function submitForm(e, evt) {
    evt.preventDefault();

    $.ajax({
        url: e.getAttribute('action'),
        type: e.getAttribute('method'),
        data: new FormData(e),
        processData: false,
        contentType: false,
        success: function(res) {
            if (res.status == 200) {
                console.log(res.message);
                location.href = res.redirect;
                iziToast.success({
                    title: 'Success',
                    message: res.message,
                    position: 'topCenter'
                });
            } else {
                console.error(res.message);
                for(let i = 0;i < res.additional.length;i++) {
                    document.getElementById(res.additional[i]).classList.add('is-invalid');
                }
                iziToast.error({
                    title: 'Error',
                    message: res.message,
                    position: 'topCenter'
                });
            }
        }, error: function (jqXHR) {
            let res = jqXHR.responseJSON;
            let message = '';
            console.log(res.message);
            for (let key in res.errors) {
                message += res.errors[key]+' ';
                document.getElementById(key).classList.add('is-invalid');
            };
            iziToast.error({
                title: 'Error',
                message: message,
                position: 'topCenter'
            });
        }
    });
}

// Select Reject Area
function showSelectRejectArea(rejectAreaImage, x, y, index) {
    document.body.style.maxHeight = '100%';
    document.body.style.overflow = 'hidden';

    let rejectAreaImageElement = document.getElementById('reject-area-img');
    rejectAreaImageElement.src = 'http://10.10.5.62:8080/erp/pages/prod_new/upload_files/'+rejectAreaImage;

    let selectRejectArea = document.getElementById('select-reject-area');
    selectRejectArea.style.display = 'flex';
    selectRejectArea.style.flexDirection = 'column';
    selectRejectArea.style.alignItems = 'center';

    let rejectAreaIndex = document.getElementById('reject-area-index');
    rejectAreaIndex.value = index;

    let rejectAreaPositionX = document.getElementById('reject-area-position-x');
    let rejectAreaPositionY = document.getElementById('reject-area-position-y');
    rejectAreaPositionX.value = x;
    rejectAreaPositionY.value = y;

    if (rejectAreaImageElement) {
        let rect = rejectAreaImageElement.getBoundingClientRect();
        let rejectAreaImagePoint = document.getElementById('reject-area-img-point');
        if (rejectAreaPositionX.value != 0 || rejectAreaPositionY.value != 0) {
            rejectAreaImagePoint.style.width = 0.03 * rect.width+'px';
            rejectAreaImagePoint.style.height = rejectAreaImagePoint.style.width;
            rejectAreaImagePoint.style.left =  'calc('+x+'% - '+0.015 * rect.width+'px)';
            rejectAreaImagePoint.style.top =  'calc('+y+'% - '+0.015 * rect.width+'px)';
            rejectAreaImagePoint.style.display = 'block';
        } else {
            rejectAreaImagePoint.style.display = 'none';
        }
    }
}

function hideSelectRejectArea() {
    document.body.style.maxHeight = null;
    document.body.style.overflow = null;

    let rejectAreaImageElement = document.getElementById('reject-area-img');
    rejectAreaImageElement.src = '';

    let selectRejectArea = document.getElementById('select-reject-area');
    selectRejectArea.style.display = 'none';
    selectRejectArea.style.flexDirection = null;
    selectRejectArea.style.justifyContent = null;
    selectRejectArea.style.alignItems = null;
}

// Show Reject Area Image
function showRejectAreaImage(defectAreaImage) {
    document.body.style.maxHeight = '100%';
    document.body.style.overflow = 'hidden';

    let defectAreaImageElement = document.getElementById('reject-area-img-show');
    defectAreaImageElement.src = 'http://10.10.5.62:8080/erp/pages/prod_new/upload_files/'+defectAreaImage;

    let showDefectArea = document.getElementById('show-reject-area');
    showDefectArea.style.display = 'flex';
    showDefectArea.style.flexDirection = 'column';
    showDefectArea.style.alignItems = 'center';
}

function hideRejectAreaImage() {
    document.body.style.maxHeight = null;
    document.body.style.overflow = null;

    let defectAreaImageElement = document.getElementById('reject-area-img-show');
    defectAreaImageElement.src = '';

    let showDefectArea = document.getElementById('show-reject-area');
    showDefectArea.style.display = 'none';
    showDefectArea.style.flexDirection = null;
    showDefectArea.style.justifyContent = null;
    showDefectArea.style.alignItems = null;

    let rejectAreaImgPointClones = document.getElementsByClassName("reject-area-img-point-clone");
    if (rejectAreaImgPointClones) {
        for (let i = rejectAreaImgPointClones.length - 1; i >= 0; i--) {
            rejectAreaImgPointClones[i].parentNode.removeChild(rejectAreaImgPointClones[i]);
        }
    }

    let rejectAreaImgTypes = document.getElementById("reject-area-img-types")
    rejectAreaImgTypes.innerHTML = '';
}

// Reminder
function showReminder(hoursminutes) {
    Swal.fire({
        icon: 'info',
        title: 'Reminder',
        html: 'Waktu saat ini : <b>'+hoursminutes+'</b><br class="mb-3">Harap sempatkan untuk menginput data di setiap jam jika memungkinkan<br class="mb-3"><small>Jika ada kendala dalam penggunaan aplikasi tolong di infokan</small>',
        showConfirmButton: true,
        showDenyButton: false,
        confirmButtonText: 'Oke',
        confirmButtonColor: '#6531a0',
    });
}

if (document.getElementById("alert-sound")) {
    var sound = document.getElementById("alert-sound");
    var played = false;


    window.addEventListener('click', function(event) {
        sound.pause();
        sound.currentTime = 0;
    });

    setInterval(function() {
        // let now = new Date();
        // let hours = String(now.getHours()).padStart(2, '0');
        // let minutes = String(now.getMinutes()).padStart(2, '0');
        // let seconds = now.getSeconds();
        // let hoursminutes = hours+':'+minutes;

        // if (!played) {
        //     switch (hoursminutes) {
        //         case "07:53" :
        //             played = true;
        //             sound.play();
        //             showReminder(hoursminutes);
        //             break;
        //         case "08:53" :
        //             played = true;
        //             sound.play();
        //             showReminder(hoursminutes);
        //             break;
        //         case "09:53" :
        //             played = true;
        //             sound.play();
        //             showReminder(hoursminutes);
        //             break;
        //         case "10:53" :
        //             played = true;
        //             sound.play();
        //             showReminder(hoursminutes);
        //             break;
        //         case "11:53" :
        //             played = true;
        //             sound.play();
        //             showReminder(hoursminutes);
        //             break;
        //         case "13:53" :
        //             played = true;
        //             sound.play();
        //             showReminder(hoursminutes);
        //             break;
        //         case "14:53" :
        //             played = true;
        //             sound.play();
        //             showReminder(hoursminutes);
        //             break;
        //         case "15:51" :
        //             played = true;
        //             sound.play();
        //             showReminder(hoursminutes);
        //             break;
        //         case "16:53" :
        //             played = true;
        //             sound.play();
        //             showReminder(hoursminutes);
        //             break;
        //     }
        // }

        // if (seconds == "0") {
        //     played = false;
        // }
    }, 1000);
}
