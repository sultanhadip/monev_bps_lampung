document.addEventListener("DOMContentLoaded", function () {
    // Tangkap semua tombol dengan kelas 'approve-btn'
    document.querySelectorAll(".approve-btn").forEach((button) => {
        button.addEventListener("click", function () {
            // Ambil data dari atribut data-*
            const id = this.getAttribute("data-id");
            const realisasi = this.getAttribute("data-realisasi");
            const buktiDukung = this.getAttribute("data-bukti");
            const idTargetRealisasi = this.getAttribute(
                "data-id-target-realisasi"
            );

            console.log(id, realisasi, buktiDukung, idTargetRealisasi);

            // Set nilai ke dalam modal form
            document.getElementById("id_target_realisasi_approve").value =
                idTargetRealisasi;
            document.getElementById("id_update_realisasi").value = id;
            document.getElementById("usulan_realisasi").value = realisasi;

            // Tampilkan atau sembunyikan bukti dukung
            if (buktiDukung && buktiDukung !== "null") {
                document.getElementById("bukti_dukung_link").href = buktiDukung;
                document.getElementById("bukti_dukung_link").textContent =
                    "Lihat Bukti Dukung";
            } else {
                document.getElementById("bukti_dukung_text").innerHTML =
                    "<span class='text-muted'>Tidak ada bukti dukung</span>";
            }
        });
    });

    document.querySelectorAll(".btn-edit-realisasi").forEach((button) => {
        button.addEventListener("click", function () {
            // Ambil data dari atribut data-*
            const id = this.getAttribute("data-id"); // Ambil ID target_realisasi
            const realisasi = this.getAttribute("data-realisasi"); // Ambil data realisasi

            // Set nilai ke dalam modal form
            document.getElementById("id_target_realisasi").value = id; // Set ID target_realisasi ke dalam hidden input
            document.getElementById("realisasi").value = realisasi; // Set nilai realisasi ke dalam input form 'realisasi'
            console.log(id, realisasi, buktiDukung);
        });
    });
});
