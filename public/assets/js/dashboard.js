$(document).ready(function () {
    // Fungsi untuk memperbarui dropdown Objek berdasarkan pilihan Tim
    function updateObjek() {
        const tim = $("#tim").val();
        if (!tim) {
            $("#objek").html('<option value="">Pilih objek</option>');
            $("#periode").html('<option value="">Pilih periode</option>');
            $("#nama_kegiatan").html(
                '<option value="">Pilih kegiatan</option>'
            );
            $("#periode-waktu-container").hide(); // Sembunyikan periode waktu
            return;
        }

        $.ajax({
            url: '{{ route("get.objek") }}',
            type: "GET",
            data: {
                tim_id: tim,
            },
            success: function (response) {
                const options = response
                    .map(
                        (objek) => `<option value="${objek}">${objek}</option>`
                    )
                    .join("");
                $("#objek").html(
                    '<option value="">Pilih objek</option>' + options
                );
                $("#periode").html('<option value="">Pilih periode</option>');
                $("#nama_kegiatan").html(
                    '<option value="">Pilih kegiatan</option>'
                );
                $("#periode-waktu-container").hide(); // Sembunyikan periode waktu
            },
            error: function (xhr) {
                console.error("Error fetching objek:", xhr.responseText);
                alert("Gagal memuat daftar objek.");
            },
        });
    }

    // Fungsi untuk memperbarui dropdown Periode berdasarkan pilihan Objek
    function updatePeriode() {
        const tim = $("#tim").val();
        const objek = $("#objek").val();

        if (!tim || !objek) {
            $("#periode").html('<option value="">Pilih periode</option>');
            $("#nama_kegiatan").html(
                '<option value="">Pilih kegiatan</option>'
            );
            $("#periode-waktu-container").hide(); // Sembunyikan periode waktu
            return;
        }

        $.ajax({
            url: '{{ route("get.periode") }}',
            type: "GET",
            data: {
                tim_id: tim,
                objek: objek,
            },
            success: function (response) {
                const options = response
                    .map(
                        (periode) =>
                            `<option value="${periode}">${periode}</option>`
                    )
                    .join("");
                $("#periode").html(
                    '<option value="">Pilih periode</option>' + options
                );
                $("#nama_kegiatan").html(
                    '<option value="">Pilih kegiatan</option>'
                );
                $("#periode-waktu-container").hide(); // Sembunyikan periode waktu
            },
            error: function (xhr) {
                console.error("Error fetching periode:", xhr.responseText);
                alert("Gagal memuat daftar periode.");
            },
        });
    }

    // Fungsi untuk memperbarui dropdown Nama Kegiatan berdasarkan pilihan Periode
    function updateNamaKegiatan() {
        const tim = $("#tim").val();
        const objek = $("#objek").val();
        const periode = $("#periode").val();

        if (!tim || !objek || !periode) {
            $("#nama_kegiatan").html(
                '<option value="">Pilih kegiatan</option>'
            );
            $("#periode-waktu-container").hide(); // Sembunyikan periode waktu
            return;
        }

        $.ajax({
            url: '{{ route("get.namaKegiatan") }}',
            type: "GET",
            data: {
                tim_id: tim,
                objek: objek,
                periode: periode,
            },
            success: function (response) {
                if (Object.keys(response).length === 0) {
                    $("#nama_kegiatan").html(
                        '<option value="">Tidak ada kegiatan yang tersedia</option>'
                    );
                } else {
                    const options = Object.entries(response)
                        .map(
                            ([kode, nama]) =>
                                `<option value="${kode}">${nama}</option>`
                        )
                        .join("");
                    $("#nama_kegiatan").html(
                        '<option value="">Pilih kegiatan</option>' + options
                    );
                }
                $("#periode-waktu-container").hide(); // Sembunyikan periode waktu
            },
            error: function (xhr) {
                console.error(
                    "Error fetching nama kegiatan:",
                    xhr.responseText
                );
                alert("Gagal memuat daftar kegiatan.");
            },
        });
    }

    // Fungsi untuk memperbarui dropdown Waktu Kegiatan berdasarkan Nama Kegiatan
    function updatePeriodeWaktu() {
        const kodeKegiatan = $("#nama_kegiatan").val();

        if (!kodeKegiatan) {
            $("#waktu-kegiatan-container").hide(); // Sembunyikan dropdown waktu kegiatan jika tidak ada nama kegiatan
            return;
        }

        $.ajax({
            url: '{{ route("get.periodeKegiatan") }}',
            type: "GET",
            data: {
                kode_kegiatan: kodeKegiatan,
            },
            success: function (response) {
                let options = '<option value="">Pilih Waktu Kegiatan</option>';

                // Tambahkan opsi berdasarkan periode yang sesuai
                if (
                    response.waktu_kegiatan &&
                    response.waktu_kegiatan.length > 0
                ) {
                    response.waktu_kegiatan.forEach((waktu) => {
                        options += `<option value="${waktu}">${waktu}</option>`;
                    });
                }

                // Update dropdown waktu kegiatan
                $("#waktu_kegiatan").html(options);

                // Tampilkan dropdown jika ada data, sembunyikan jika tidak ada
                if (
                    options === '<option value="">Pilih Waktu Kegiatan</option>'
                ) {
                    $("#waktu-kegiatan-container").hide();
                } else {
                    $("#waktu-kegiatan-container").show(); // Tampilkan dropdown
                }
            },
            error: function (xhr) {
                console.error(
                    "Error fetching periode waktu:",
                    xhr.responseText
                );
                alert("Gagal memuat periode waktu. Silakan coba lagi.");
            },
        });
    }

    // Event listener untuk perubahan pada dropdown Tim, Objek, Periode, dan Nama Kegiatan
    $("#tim").change(updateObjek);
    $("#objek").change(updatePeriode);
    $("#periode").change(updateNamaKegiatan);
    $("#nama_kegiatan").change(updatePeriodeWaktu);

    $("#btn-tampilkan").click(function () {
        const kodeKegiatan = $("#nama_kegiatan").val();
        if (!kodeKegiatan) {
            alert("Silakan pilih kegiatan yang valid.");
            return;
        }
        const waktuKegiatan = $("#waktu_kegiatan").val();

        $.ajax({
            url: '{{ route("get.filteredData") }}',
            type: "GET",
            data: {
                kode_kegiatan: kodeKegiatan,
                waktu_kegiatan: waktuKegiatan,
            },
            success: function (response) {
                // Update data di UI
                $("#target").text(response.target || "-");
                $("#realisasi").text(response.realisasi || "-");
                $("#tertinggi").text(response.tertinggi.persentase || "-");
                $("#tertinggi-nama").text(response.tertinggi.nama || "-");
                $("#terendah").text(response.terendah.persentase || "-");
                $("#terendah-nama").text(response.terendah.nama || "-");

                // Update chart dengan data yang baru
                updateChart(
                    response.chartTargetData,
                    response.chartRealisasiData,
                    response.chartCategories
                );
            },
            error: function (xhr) {
                alert(
                    "Terjadi kesalahan saat mengambil data: " + xhr.responseText
                );
            },
        });
    });

    // Fungsi untuk menghancurkan chart yang ada
    function destroyChart() {
        const chart = ApexCharts.getChartByID("columnChart");
        if (chart) {
            chart.destroy(); // Menghancurkan grafik yang ada
            console.log("Chart destroyed");
        }
    }

    // Fungsi untuk memperbarui grafik dengan data baru
    function updateChart(targetData, realisasiData, categories) {
        // Hancurkan grafik lama sebelum render yang baru
        destroyChart();

        // Pastikan kontainer grafik sudah siap untuk menerima grafik baru
        const chartContainer = document.querySelector("#columnChart");
        chartContainer.innerHTML = ""; // Clear any existing chart before rendering a new one

        // Render ulang chart dengan data baru
        const chart = new ApexCharts(chartContainer, {
            series: [
                {
                    name: "Target",
                    data: targetData, // Gunakan data target yang diterima dari server
                },
                {
                    name: "Realisasi",
                    data: realisasiData, // Gunakan data realisasi yang diterima dari server
                },
            ],
            chart: {
                type: "bar",
                height: 350,
            },
            xaxis: {
                categories: categories, // Gunakan kategori yang diterima dari server
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: "55%",
                },
            },
        });

        chart.render(); // Render ulang grafik dengan data yang baru
        console.log("New chart rendered");
    }
});
