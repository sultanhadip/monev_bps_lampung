$(document).ready(function () {
    $("#generateButton").click(function () {
        console.log("Generate button clicked");
        if (confirm("Apakah Anda yakin ingin mengenerate nilai kinerja?")) {
            $.ajax({
                url: '{{ route("penilaian.generate") }}', // Blade route() disuntikkan dengan benar di sini
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}", // CSRF token disuntikkan dengan benar
                },
                success: function (response) {
                    console.log("Response:", response);
                    alert(response.message);
                    location.reload();
                },
                error: function (xhr) {
                    let errorMessage = xhr.responseJSON
                        ? xhr.responseJSON.message
                        : "Terjadi kesalahan saat generate.";
                    console.error("Error:", errorMessage);
                    alert(errorMessage);
                },
            });
        }
    });
});
