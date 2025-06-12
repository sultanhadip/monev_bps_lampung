function updatePageSize() {
    var recordsPerPage = document.getElementById("recordsPerPage").value;
    window.location.search = "?recordsPerPage=" + recordsPerPage; // Mengirimkan query string dengan parameter
}

$(document).ready(function () {
    // Menangani form submit untuk menambahkan data tim kerja
    $("#addModal form").on("submit", function (e) {
        e.preventDefault(); // Mencegah form submit biasa

        // Ambil data dari form
        var formData = $(this).serialize();

        // Mengirim data dengan AJAX
        $.ajax({
            url: "{{ route('timkerja.store') }}", // Menggunakan route yang sudah didefinisikan
            method: "POST",
            data: formData,
            success: function (response) {
                // Menutup modal setelah data berhasil ditambahkan
                $("#addModal").modal("hide");

                // Tampilkan pesan sukses
                alert(response.message);

                // Tambahkan data tim kerja ke tabel tanpa reload halaman
                var newRow =
                    "<tr><td>" +
                    response.data.kode_tim +
                    "</td><td>" +
                    response.data.nama_tim +
                    '</td><td><button class="btn btn-warning">Edit</button></td></tr>';
                $("table tbody").append(newRow);
            },
            error: function (response) {
                // Tampilkan pesan error jika ada masalah
                alert("Terjadi kesalahan. Silakan coba lagi.");
            },
        });
    });
});

$(document).ready(function () {
    // Ambil URL dari elemen HTML
    var url = $("#ajax-url").data("url");

    // Tangani peristiwa 'keyup' pada kolom pencarian
    $("#searchInput").on("keyup", function () {
        var search = $(this).val(); // Ambil nilai pencarian
        var recordsPerPage = $("#recordsPerPage").val(); // Ambil jumlah data per halaman

        // Kirim permintaan Ajax ke server
        $.ajax({
            url: url,
            method: "GET",
            data: {
                search: search,
                recordsPerPage: recordsPerPage,
            },
            success: function (response) {
                // Perbarui tabel dengan data yang diterima
                $("#dataTable tbody").html(response.table);

                // Update text showing berdasarkan jumlah data hasil pencarian
                $("#showingText").text(response.showingText);
            },
        });
    });
});

document
    .getElementById("recordsPerPage")
    .addEventListener("change", function () {
        var recordsPerPage = this.value;
        var url = new URL(window.location.href);
        url.searchParams.set("recordsPerPage", recordsPerPage);
        window.location.href = url.toString();
    });
