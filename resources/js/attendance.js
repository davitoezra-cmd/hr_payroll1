/*
|--------------------------------------------------------------------------
| HR PAYROLL - ATTENDANCE
|--------------------------------------------------------------------------
| attendance.js
|--------------------------------------------------------------------------
*/

const API_URL = window.location.origin + "/api";

// ===============================
// TOKEN LOGIN
// ===============================
const token = localStorage.getItem("token");

if (!token) {

    alert("Silakan login terlebih dahulu.");

    window.location.href = "/login";

}

// ===============================
// DEFAULT HEADER API
// ===============================
const headers = {

    "Accept": "application/json",

    "Authorization": "Bearer " + token

};


// ===============================
// JAM & TANGGAL
// ===============================
function updateClock() {

    const now = new Date();

    document.getElementById("jam").innerHTML =
        now.toLocaleTimeString("id-ID");

    document.getElementById("tanggal").innerHTML =
        now.toLocaleDateString("id-ID", {

            weekday: "long",

            year: "numeric",

            month: "long",

            day: "numeric"

        });

}

updateClock();

setInterval(updateClock, 1000);



// ===============================
// CAMERA
// ===============================
let cameraStream = null;

async function startCamera() {

    try {

        cameraStream = await navigator.mediaDevices.getUserMedia({

            video: {

                facingMode: "user"

            },

            audio: false

        });

        document.getElementById("video").srcObject =
            cameraStream;

    }

    catch (error) {

        console.log(error);

        alert("Kamera tidak dapat diakses.");

    }

}



// ===============================
// GPS
// ===============================
function getLocation() {

    if (!navigator.geolocation) {

        alert("Browser tidak mendukung GPS.");

        return;

    }

    navigator.geolocation.getCurrentPosition(

        function(position){

            document.getElementById("latitude").value =
                position.coords.latitude;

            document.getElementById("longitude").value =
                position.coords.longitude;

        },

        function(error){

            console.log(error);

            alert("Lokasi gagal diambil.");

        },

        {

            enableHighAccuracy: true,

            timeout: 10000,

            maximumAge: 0

        }

    );

}



// ===============================
// AMBIL GPS TERBARU
// dipakai saat check in/out
// ===============================
function getCurrentLocation() {

    return new Promise((resolve, reject)=>{

        if(!navigator.geolocation){

            reject("Browser tidak mendukung GPS");

            return;

        }

        navigator.geolocation.getCurrentPosition(

            function(position){

                resolve({

                    latitude: position.coords.latitude,

                    longitude: position.coords.longitude

                });

            },

            function(error){

                reject(error);

            },

            {

                enableHighAccuracy: true,

                timeout: 10000,

                maximumAge: 0

            }

        );

    });

}



// ===============================
// CAPTURE SELFIE
// ===============================
function capturePhoto() {

    return new Promise((resolve)=>{

        const video =
            document.getElementById("video");

        const canvas =
            document.getElementById("canvas");

        canvas.width =
            video.videoWidth;

        canvas.height =
            video.videoHeight;

        const ctx =
            canvas.getContext("2d");

        ctx.drawImage(

            video,

            0,

            0,

            canvas.width,

            canvas.height

        );

        canvas.toBlob(function(blob){

            resolve(blob);

        },

        "image/jpeg",

        0.9);

    });

}



// ===============================
// START CAMERA & GPS
// ===============================
startCamera();

getLocation();

// ===============================
// LOAD PROFILE
// ===============================
async function loadProfile() {

    try {

        const response = await fetch(API_URL + "/profile", {

            method: "GET",

            headers: headers

        });

        const result = await response.json();

        if (!response.ok) {

            alert(result.message ?? "Gagal mengambil profile.");

            return;

        }

        const employee = result.data;

        document.getElementById("employeeName").innerHTML =
            employee.name;

        document.getElementById("employeeEmail").innerHTML =
            employee.email;

        document.getElementById("employeeRole").innerHTML =
            employee.role;

        if (employee.photo) {

            document.getElementById("employeePhoto").src =
                "/storage/" + employee.photo;

        }

    }

    catch (error) {

        console.log(error);

    }

}



// ===============================
// LOAD ATTENDANCE HARI INI
// ===============================
async function loadAttendance() {

    try {

        const response = await fetch(API_URL + "/attendance", {

            method: "GET",

            headers: headers

        });

        const result = await response.json();

        if (!response.ok) {

            console.log(result);

            return;

        }

        const attendance =
            result.data.attendance;



        // ===============================
        // BELUM ABSEN
        // ===============================
        if (!attendance) {

            document.getElementById("attendanceStatus").innerHTML =
                "Belum Check In";

            document.getElementById("attendanceStatus")
                .className =
                "badge bg-secondary fs-6";

            document.getElementById("checkInTime").innerHTML =
                "-";

            document.getElementById("checkOutTime").innerHTML =
                "-";

            document.getElementById("btnCheckIn")
                .classList.remove("d-none");

            document.getElementById("btnCheckOut")
                .classList.add("d-none");

            return;

        }



        // ===============================
        // STATUS
        // ===============================
        document.getElementById("attendanceStatus").innerHTML =
            attendance.status.toUpperCase();



        if (attendance.status === "hadir") {

            document.getElementById("attendanceStatus")
                .className =
                "badge bg-success fs-6";

        }
        else {

            document.getElementById("attendanceStatus")
                .className =
                "badge bg-warning fs-6";

        }



        // ===============================
        // JAM
        // ===============================
        document.getElementById("checkInTime").innerHTML =
            attendance.check_in ?? "-";

        document.getElementById("checkOutTime").innerHTML =
            attendance.check_out ?? "-";



        // ===============================
        // CHECK IN SUDAH
        // ===============================
        if (attendance.check_in) {

            document.getElementById("btnCheckIn")
                .classList.add("d-none");

            if (!attendance.check_out) {

                document.getElementById("btnCheckOut")
                    .classList.remove("d-none");

            }

        }



        // ===============================
        // CHECK OUT SUDAH
        // ===============================
        if (attendance.check_out) {

            document.getElementById("btnCheckOut")
                .classList.add("d-none");

        }



        // ===============================
        // GPS CHECK IN
        // ===============================
        if (attendance.latitude) {

            document.getElementById("latitude").value =
                attendance.latitude;

        }

        if (attendance.longitude) {

            document.getElementById("longitude").value =
                attendance.longitude;

        }

    }

    catch (error) {

        console.log(error);

    }

}



// ===============================
// REFRESH DATA
// ===============================
async function refreshAttendance() {

    await loadProfile();

    await loadAttendance();

}


// ===============================
// CHECK IN
// ===============================
async function checkIn() {

    const btn = document.getElementById("btnCheckIn");

    btn.disabled = true;
    btn.innerHTML = "Memproses...";

    try {

        // Ambil GPS terbaru
        const location = await getCurrentLocation();

        // Capture selfie
        const photo = await capturePhoto();

        const formData = new FormData();

        formData.append(
            "image_selfie",
            photo,
            "checkin.jpg"
        );

        formData.append(
            "latitude",
            location.latitude
        );

        formData.append(
            "longitude",
            location.longitude
        );

        const response = await fetch(
            API_URL + "/attendance/check-in",
            {
                method: "POST",

                headers: {

                    "Authorization": "Bearer " + token,

                    "Accept": "application/json"

                },

                body: formData

            }
        );

        const result = await response.json();

        if (!response.ok) {

            alert(result.message ?? "Check In gagal.");

            return;

        }

        alert(result.message);

        getLocation();

        await refreshAttendance();

    }
    catch (error) {

        console.log(error);

        alert("Terjadi kesalahan saat Check In.");

    }
    finally {

        btn.disabled = false;

        btn.innerHTML = "Check In";

    }

}



// ===============================
// CHECK OUT
// ===============================
async function checkOut() {

    const btn = document.getElementById("btnCheckOut");

    btn.disabled = true;

    btn.innerHTML = "Memproses...";

    try {

        // Ambil GPS terbaru
        const location = await getCurrentLocation();

        // Capture selfie
        const photo = await capturePhoto();

        const formData = new FormData();

        formData.append(
            "image_selfie",
            photo,
            "checkout.jpg"
        );

        formData.append(
            "latitude",
            location.latitude
        );

        formData.append(
            "longitude",
            location.longitude
        );

        const response = await fetch(
            API_URL + "/attendance/check-out",
            {
                method: "POST",

                headers: {

                    "Authorization": "Bearer " + token,

                    "Accept": "application/json"

                },

                body: formData

            }
        );

        const result = await response.json();

        if (!response.ok) {

            alert(result.message ?? "Check Out gagal.");

            return;

        }

        alert(result.message);

        getLocation();

        await refreshAttendance();

    }
    catch (error) {

        console.log(error);

        alert("Terjadi kesalahan saat Check Out.");

    }
    finally {

        btn.disabled = false;

        btn.innerHTML = "Check Out";

    }

}



// ===============================
// EVENT BUTTON
// ===============================
document
    .getElementById("btnCheckIn")
    .addEventListener("click", checkIn);

document
    .getElementById("btnCheckOut")
    .addEventListener("click", checkOut);



// ===============================
// LOAD PAGE
// ===============================
document.addEventListener("DOMContentLoaded", async function () {

    updateClock();

    startCamera();

    getLocation();

    await loadProfile();

    await loadAttendance();

});



// ===============================
// AUTO REFRESH GPS
// ===============================
setInterval(function(){

    getLocation();

},30000);



// ===============================
// AUTO REFRESH DATA ABSENSI
// ===============================
setInterval(function(){

    loadAttendance();

},60000);



// ===============================
// STOP CAMERA SAAT PAGE DITUTUP
// ===============================
window.addEventListener("beforeunload", function () {

    if (cameraStream) {

        cameraStream.getTracks().forEach(function(track){

            track.stop();

        });

    }

});