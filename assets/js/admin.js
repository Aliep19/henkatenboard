let currentShift = 'shift1';
const shiftData = {
    shift1: {
        workHours: '08:00 - 16:00',
        outputTarget: '1600',
        lineGuide1: 'MARIONO - 0254',
        lineGuide2: 'VACANT',
        tableData: [
            { no: 1, process: 'BALL RACE', mpAwal: 'JAMALUDIN - K1100', absensi: 'HADIR', mpPengganti: '', sts: 'gray' },
            { no: 2, process: 'DAMPING FORCE', mpAwal: 'SUTARNO - K1101', absensi: 'SAKIT', mpPengganti: 'AJI MATS MAIL - K0002', sts: 'red' },
            { no: 3, process: 'TORQUE', mpAwal: 'ABDUL FIKRI - K1102', absensi: 'HADIR', mpPengganti: '', sts: 'gray' },
            { no: 4, process: 'BOLT TIGH & CH....', mpAwal: 'PUTRA ADITYA - K1103', absensi: 'HADIR', mpPengganti: '', sts: 'gray' },
            { no: 5, process: 'INSERT DAMPER', mpAwal: 'ZAKI MUHAMMAD - K2103', absensi: 'HADIR', mpPengganti: '', sts: 'gray' }
        ]
    },
    shift2: {
        workHours: '16:00 - 00:00',
        outputTarget: '1500',
        lineGuide1: 'SURYO - 0356',
        lineGuide2: 'VACANT',
        tableData: [
            { no: 1, process: 'PRESS PROTEC....', mpAwal: 'MUHAMMAD ADITA - K1104', absensi: 'TRAIN', mpPengganti: 'SAIFUL FARISIN - K0003', sts: 'green' },
            { no: 2, process: 'C-PIN', mpAwal: 'HERMAN - K1105', absensi: 'HADIR', mpPengganti: '', sts: 'gray' },
            { no: 3, process: 'OIL FILLING', mpAwal: 'BAMBANG - K1106', absensi: 'HADIR', mpPengganti: '', sts: 'gray' }
        ]
    },
    shift3: {
        workHours: '00:00 - 08:00',
        outputTarget: '1400',
        lineGuide1: 'BUDI - 0458',
        lineGuide2: 'VACANT',
        tableData: [
            { no: 1, process: 'LEAKAGE TESTER', mpAwal: 'KUSMAYADI - K1107', absensi: 'SAKIT', mpPengganti: 'TRI JOKO - K0004', sts: 'red' },
            { no: 2, process: 'FINAL VISUAL INSPECTION', mpAwal: 'HENDRI - K1108', absensi: 'HADIR', mpPengganti: '', sts: 'gray' }
        ]
    }
};

function confirmLogout() {
    Swal.fire({
        title: 'Konfirmasi Logout',
        text: 'Apakah Anda yakin ingin logout?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, logout',
        cancelButtonText: 'Batal',
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'login.html'; // Redirect to login page
        }
    });
}

function confirmSubmit() {
    Swal.fire({
        title: 'Konfirmasi Submit',
        text: 'Apakah Anda yakin ingin submit data ini?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, submit',
        cancelButtonText: 'Batal',
    }).then((result) => {
        if (result.isConfirmed) {
            // Add your submit logic here
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Data telah berhasil di-submit.',
            });
        }
    });
}

function confirmReset() {
    Swal.fire({
        title: 'Konfirmasi Reset',
        text: 'Apakah Anda yakin ingin mereset data ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, reset',
        cancelButtonText: 'Batal',
    }).then((result) => {
        if (result.isConfirmed) {
            // Add your reset logic here
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Data telah berhasil di-reset.',
            });
        }
    });
}

function switchShift() {
    if (currentShift === 'shift1') {
        currentShift = 'shift2';
    } else if (currentShift === 'shift2') {
        currentShift = 'shift3';
    } else {
        currentShift = 'shift1';
    }

    // Update dropdown shift
    document.getElementById('shift').value = currentShift;

    // Lanjutkan dengan kode yang ada untuk mengupdate informasi tabel dan status
    const shiftInfo = shiftData[currentShift];
    document.getElementById('work-hours').value = shiftInfo.workHours;
    document.getElementById('output-target').value = shiftInfo.outputTarget;
    document.getElementById('line-guide1').value = shiftInfo.lineGuide1;
    document.getElementById('line-guide2').value = shiftInfo.lineGuide2;

    const tableBody = document.getElementById('table-body');
    tableBody.innerHTML = '';
    shiftInfo.tableData.forEach((row, index) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${row.no}</td>
            <td>${row.process}</td>
            <td>${row.mpAwal}</td>
            <td>
                <select class="absensi-dropdown">
                    <option value="HADIR" ${row.absensi === 'HADIR' ? 'selected' : ''}>HADIR</option>
                    <option value="SAKIT" ${row.absensi === 'SAKIT' ? 'selected' : ''}>SAKIT</option>
                    <option value="IZIN" ${row.absensi === 'IZIN' ? 'selected' : ''}>IZIN</option>
                    <option value="CUTI" ${row.absensi === 'CUTI' ? 'selected' : ''}>CUTI</option>
                </select>
            </td>
            <td>${row.mpPengganti}</td>
            <td class="sts-cell" data-status="${row.sts}">${row.absensi}</td>
            <td>
                <button class="edit-button" data-index="${index}">Edit</button>
                <button class="delete-button" data-index="${index}">Hapus</button>
            </td>
        `;
        tableBody.appendChild(tr);
    });

    updateSTSCSS();
    addEventListeners();
}


function updateSTSCSS() {
    const statusCells = document.querySelectorAll('.sts-cell');
    statusCells.forEach(cell => {
        const status = cell.textContent.trim().toLowerCase();
        cell.className = 'sts-cell'; // Reset class
        switch (status) {
            case 'hadir':
                cell.classList.add('hadir');
                break;
            case 'sakit':
                cell.classList.add('sakit');
                break;
            case 'izin':
                cell.classList.add('izin');
                break;
            case 'cuti':
                cell.classList.add('cuti');
                break;
            default:
                cell.style.backgroundColor = '#f0f0f0'; // Default color if no match
                cell.style.color = 'black'; // Default text color
                break;
        }
        cell.style.color = 'white';
        cell.style.borderRadius = '15px';
        cell.style.padding = '5px 10px';
        cell.style.display = 'inline-block';
    });
}

function addEventListeners() {
    const dropdowns = document.querySelectorAll('.absensi-dropdown');
    dropdowns.forEach(dropdown => {
        dropdown.addEventListener('change', function () {
            const statusCell = this.parentElement.nextElementSibling.nextElementSibling;
            statusCell.textContent = this.value;
            switch (this.value) {
                case 'HADIR':
                    statusCell.style.backgroundColor = 'green';
                    break;
                case 'SAKIT':
                    statusCell.style.backgroundColor = 'red';
                    break;
                case 'IZIN':
                    statusCell.style.backgroundColor = 'yellow';
                    break;
                case 'CUTI':
                    statusCell.style.backgroundColor = 'gray';
                    break;
            }
        });
    });

    const editButtons = document.querySelectorAll('.edit-button');
    editButtons.forEach(button => {
        button.addEventListener('click', function () {
            const index = this.getAttribute('data-index');
            editRow(index);
        });
    });

    const deleteButtons = document.querySelectorAll('.delete-button');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function () {
            const index = this.getAttribute('data-index');
            deleteRow(index);
        });
    });
}
function editRow(index) {
    const rowData = shiftData[currentShift].tableData[index];
    Swal.fire({
        title: 'Edit Data',
        html: `
            <label for="swal-input-no">No:</label>
            <input id="swal-input-no" class="swal2-input" value="${rowData.no}" disabled>
            <label for="swal-input-process">Process:</label>
            <input id="swal-input-process" class="swal2-input" value="${rowData.process}">
            <label for="swal-input-mpAwal">MP Awal:</label>
            <input id="swal-input-mpAwal" class="swal2-input" value="${rowData.mpAwal}">
            <label for="swal-input-absensi">Absensi:</label>
            <select id="swal-input-absensi" class="swal2-input">
                <option value="HADIR" ${rowData.absensi === 'HADIR' ? 'selected' : ''}>HADIR</option>
                <option value="SAKIT" ${rowData.absensi === 'SAKIT' ? 'selected' : ''}>SAKIT</option>
                <option value="IZIN" ${rowData.absensi === 'IZIN' ? 'selected' : ''}>IZIN</option>
                <option value="CUTI" ${rowData.absensi === 'CUTI' ? 'selected' : ''}>CUTI</option>
            </select>
            <label for="swal-input-mpPengganti">MP Pengganti:</label>
            <input id="swal-input-mpPengganti" class="swal2-input" value="${rowData.mpPengganti}">
        `,
        focusConfirm: false,
        preConfirm: () => {
            return {
                no: rowData.no,
                process: document.getElementById('swal-input-process').value,
                mpAwal: document.getElementById('swal-input-mpAwal').value,
                absensi: document.getElementById('swal-input-absensi').value,
                mpPengganti: document.getElementById('swal-input-mpPengganti').value,
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            shiftData[currentShift].tableData[index] = result.value;
            switchShift();
        }
    });
}



function deleteRow(index) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: 'Apakah Anda yakin ingin menghapus data ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal',
    }).then((result) => {
        if (result.isConfirmed) {
            shiftData[currentShift].tableData.splice(index, 1);
            switchShift();
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Data telah berhasil dihapus.',
            });
        }
    });
}

// Show the modal when "MP Repair" button is clicked
document.getElementById('mpRepairBtn').addEventListener('click', function () {
    $('#mpRepairModal').modal('show');
});

// Initialize the table with the default shift data
switchShift();

// Clock update
function updateClock() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    document.getElementById('clock').textContent = `${hours}:${minutes}`;
}
setInterval(updateClock, 1000);
updateClock();
