$(document).ready(function () {
    function loadPendingNotifications() {
        $.ajax({
            url: window.routes.pendingVerifikasi, // pakai variable global yang dikirim dari blade
            method: "GET",
            success: function (response) {
                if (response.pending && response.count > 0) {
                    $("#notificationBadge").text(response.count).show();

                    // Header NOTIFIKASI dan jumlah notifikasi berdampingan di kiri
                    let notifContent = `
  <div class="d-flex align-items-center gap-2 fw-bold text-primary fs-6 mb-4">
    <span>NOTIFIKASI</span>
    <span class="badge bg-danger rounded-pill">${response.count}</span>
  </div>
  <ul class="list-group mb-0">
`;

                    response.notifications.forEach(function (notif) {
                        notifContent += `
      <a href="/monitoring-kegiatan/detail-monitoring-kegiatan/${notif.id_monitoring_kegiatan}#verifikasi"
         class="list-group-item list-group-item-action notif-link">
        <strong>Usulan Terbaru ${notif.nama_satuan_kerja}</strong><br />
        <span class="text-muted">${notif.nama_kegiatan} ${notif.periode_kegiatan}</span><br />
        <small class="text-muted">${notif.created_at}</small>
      </a>
    `;
                    });

                    notifContent += `</ul>`;

                    $("#notifUsulanContent").html(notifContent);
                } else {
                    $("#notificationBadge").hide();
                    $("#notifUsulanContent").html(
                        '<p class="text-center mb-0">Tidak ada usulan menunggu verifikasi.</p>'
                    );
                }
            },

            error: function () {
                $("#notificationBadge").hide();
                $("#notifUsulanContent").html(
                    '<p class="text-center mb-0 text-danger">Gagal memuat notifikasi.</p>'
                );
            },
        });
    }

    loadPendingNotifications();
    setInterval(loadPendingNotifications, 30000);

    $("#notifDropdownMenu").on("click", "a.notif-link", function (e) {
        e.preventDefault();
        const url = $(this).attr("href");

        const dropdownToggle = document.getElementById("notificationButton");
        const dropdown = bootstrap.Dropdown.getInstance(dropdownToggle);
        if (dropdown) {
            dropdown.hide();
        }

        setTimeout(() => {
            window.location.href = url;
        }, 150);
    });
});
