document.addEventListener("DOMContentLoaded", function () {
    // Menyimpan data kegiatan yang diterima
    let kegiatanData = [];

    const timKerjaSelect = document.getElementById("kode_tim");
    const kegiatanSelect = document.getElementById("kode_kegiatan");

    timKerjaSelect.addEventListener("change", function () {
        const timId = this.value; // Ambil ID tim yang dipilih
        kegiatanSelect.innerHTML =
            '<option value="" disabled selected>Pilih Kegiatan</option>'; // Reset dropdown

        // Jika tidak ada tim yang dipilih, hentikan eksekusi
        if (!timId) return;

        // Kirim request AJAX ke server untuk mengambil kegiatan berdasarkan tim yang dipilih
        fetch(`/get-kegiatan-by-tim?tim_id=${timId}`)
            .then((response) => response.json())
            .then((kegiatan) => {
                // Debugging: Periksa data kegiatan yang diterima
                console.log("Data kegiatan yang diterima:", kegiatan);

                // Simpan data kegiatan yang diterima
                kegiatanData = kegiatan;

                // Jika tidak ada kegiatan untuk tim ini
                if (kegiatan.message) {
                    const noKegiatanOption = document.createElement("option");
                    noKegiatanOption.value = "";
                    noKegiatanOption.textContent = kegiatan.message;
                    kegiatanSelect.appendChild(noKegiatanOption);
                    return;
                }

                // Tambahkan kegiatan ke dalam dropdown
                kegiatan.forEach((item) => {
                    const option = document.createElement("option");
                    option.value = item.id; // ID kegiatan
                    option.textContent = item.nama_kegiatan; // Nama kegiatan
                    kegiatanSelect.appendChild(option);
                });
            })
            .catch((error) => {
                console.error("Error fetching kegiatan:", error);
            });
    });

    // Event listener untuk ketika kegiatan dipilih
    kegiatanSelect.addEventListener("change", function () {
        const kegiatanId = this.value;
        const selectedKegiatan = kegiatanData.find((k) => k.id == kegiatanId);

        if (selectedKegiatan) {
            const periodeKegiatan = selectedKegiatan.periode_kegiatan;

            // Menyimpan id_data_kegiatan ke dalam input tersembunyi
            document.getElementById("id_data_kegiatan").value =
                selectedKegiatan.id;

            // Menyesuaikan tampilan form berdasarkan periode kegiatan
            if (periodeKegiatan === "Bulanan") {
                // Tampilkan input bulan
                document.getElementById("bulan-container").style.display =
                    "block";
                document.getElementById("triwulan-container").style.display =
                    "none";
                document.getElementById("semester-container").style.display =
                    "none";
            } else if (periodeKegiatan === "Triwulan") {
                // Tampilkan input triwulan
                document.getElementById("bulan-container").style.display =
                    "none";
                document.getElementById("triwulan-container").style.display =
                    "block";
                document.getElementById("semester-container").style.display =
                    "none";
            } else if (periodeKegiatan === "Semesteran") {
                // Tampilkan input semester
                document.getElementById("bulan-container").style.display =
                    "none";
                document.getElementById("triwulan-container").style.display =
                    "none";
                document.getElementById("semester-container").style.display =
                    "block";
            } else {
                // Tidak ada periode yang relevan, sembunyikan semua
                document.getElementById("bulan-container").style.display =
                    "none";
                document.getElementById("triwulan-container").style.display =
                    "none";
                document.getElementById("semester-container").style.display =
                    "none";
            }
        }
    });

    // Checkbox "Pilih Semua"
    const selectAllCheckbox = document.getElementById("select_all");
    const checkboxes = document.querySelectorAll(".satuan-checkbox");
    const targetContainer = document.getElementById("target-sampel-container");

    // Fungsi untuk memperbarui input target sampel
    function updateTargetSampel() {
        targetContainer.innerHTML = ""; // Bersihkan input sebelumnya

        // Loop melalui semua checkbox satuan kerja yang dicentang
        checkboxes.forEach((checkbox) => {
            if (checkbox.checked) {
                const satuanKerjaId = checkbox.value;
                const satuanKerjaName = document.querySelector(
                    `label[for="satuan_kerja_${satuanKerjaId}"]`
                ).innerText;

                // Tambahkan input field untuk setiap satuan kerja yang dicentang
                const inputContainer = document.createElement("div");
                inputContainer.classList.add("mb-2");
                inputContainer.innerHTML = `
                    <label for="target_sampel_${satuanKerjaId}" class="form-label">${satuanKerjaName}</label>
                    <input type="number" id="target_sampel_${satuanKerjaId}" name="target_sampel[${satuanKerjaId}]" class="form-control" required>
                `;
                targetContainer.appendChild(inputContainer);
            }
        });
    }

    // Event untuk checkbox "Pilih Semua"
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener("change", function (e) {
            const isChecked = e.target.checked;

            // Centang atau hapus centang semua checkbox satuan kerja
            checkboxes.forEach(function (checkbox) {
                checkbox.checked = isChecked;
            });

            // Perbarui input target sampel setelah perubahan checkbox
            updateTargetSampel();
        });
    }

    // Event untuk setiap checkbox satuan kerja
    checkboxes.forEach(function (checkbox) {
        checkbox.addEventListener("change", function () {
            // Perbarui input target sampel setiap kali checkbox berubah
            updateTargetSampel();
        });
    });
});
