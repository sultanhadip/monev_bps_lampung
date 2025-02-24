$(document).ready(function () {
    // Menangani form submit untuk menambahkan data tim kerja
    $("#addModal form").on("submit", function (e) {
        e.preventDefault(); // Mencegah form submit biasa

        // Ambil data dari form
        var formData = $(this).serialize();

        // Mengirim data dengan AJAX
        $.ajax({
            url: "{{ route('satuankerja.store') }}", // Menggunakan route yang sudah didefinisikan
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
                    response.data.kode_satuan_kerja +
                    "</td><td>" +
                    response.data.nama_satuan_kerja +
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
