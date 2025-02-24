$(document).ready(function () {
    // Menangani form submit untuk menambahkan data kegiatan
    $("#addModal form").on("submit", function (e) {
        e.preventDefault();

        $.ajax({
            url: "{{ route('datakegiatan.store') }}",
            method: "POST",
            data: $(this).serialize(),
            success: function (response) {
                $("#addModal").modal("hide");
                alert(response.message);
                location.reload(); // Refresh halaman setelah sukses
            },
            error: function (response) {
                alert("Terjadi kesalahan. Silakan coba lagi.");
            },
        });
    });
});
