function updatePageSize() {
    var recordsPerPage = document.getElementById("recordsPerPage").value;
    window.location.search = "?recordsPerPage=" + recordsPerPage; // Mengirimkan query string dengan parameter
}

$(document).ready(function () {
    // Menangani submit form
    $("#addUserForm").on("submit", function (e) {
        e.preventDefault(); // Mencegah form disubmit secara biasa

        // Mengambil data form
        var formData = $(this).serialize();

        // Mengambil token CSRF dari meta tag
        var csrfToken = $("meta[name='csrf-token']").attr("content");

        // Mengirim data ke server
        $.ajax({
            url: "{{ route('kelolauser.store') }}", // Rute untuk menyimpan data
            method: "POST",
            data: formData,
            headers: {
                "X-CSRF-TOKEN": csrfToken, // Menambahkan token CSRF pada header
            },
            success: function (response) {
                console.log(response); // Menampilkan response dari server untuk debugging

                if (response.success) {
                    Swal.fire({
                        icon: "success",
                        text: response.message,
                        showConfirmButton: true,
                        timer: 3000,
                    });

                    // Menambahkan baris baru ke tabel tanpa reload halaman
                    var newRow =
                        "<tr><td>" +
                        response.data.id +
                        "</td><td>" +
                        response.data.nama +
                        "</td><td>" +
                        response.data.username +
                        "</td><td>" +
                        response.data.role +
                        "</td><td>" +
                        response.data.satuanKerja.nama_satuan_kerja +
                        "</td><td>" +
                        response.data.timKerja.nama_tim +
                        "</td><td>" +
                        '<button class="btn btn-sm btn-icon btn-white text-primary border-0" data-bs-toggle="modal" data-bs-target="#editModal' +
                        response.data.id +
                        '"><i class="bi bi-pencil-square fs-6"></i></button>' +
                        '<form action="/kelolauser/' +
                        response.data.id +
                        '" method="POST" style="display:inline;">' +
                        '@csrf @method("DELETE")' +
                        '<button type="submit" class="btn btn-sm btn-icon btn-white text-danger border-0"><i class="bi bi-trash fs-6"></i></button>' +
                        "</form>" +
                        "</td></tr>";

                    // Menambahkan data ke tabel tanpa reload halaman
                    $("table tbody").append(newRow);

                    // Tutup modal
                    $("#addModal").modal("hide");
                } else {
                    Swal.fire({
                        icon: "error",
                        text: "Terjadi kesalahan, silakan coba lagi.",
                    });
                }
            },
            error: function (xhr, status, error) {
                console.error(xhr.responseText); // Debugging error
                Swal.fire({
                    icon: "error",
                    text: "Terjadi kesalahan. Coba lagi.",
                });
            },
        });
    });
});
