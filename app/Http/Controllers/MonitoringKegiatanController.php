<?php

namespace App\Http\Controllers;

use App\Models\MonitoringKegiatan;
use App\Models\SatuanKerja;
use App\Models\TimKerja;
use App\Models\DataKegiatan;
use App\Models\target_realisasi_satker;
use App\Models\update_target_realisasi;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MonitoringKegiatanController extends Controller
{
    public function index(Request $request)
    {
        // Cek apakah pengguna memiliki akses untuk melihat kolom action
        $canAccessVerifikasi = Gate::any([
            'isNeracaProv',
            'isSosialProv',
            'isProduksiProv',
            'isDistribusiProv',
            'isIPDSProv',
            'isAdmin'
        ]);

        // Ambil semua satuan kerja dan tim kerja yang memiliki kegiatan
        $satuankerja = SatuanKerja::all();
        $timkerja = TimKerja::whereHas('datakegiatan')->get(); // Hanya tim kerja yang memiliki kegiatan

        // Ambil semua data kegiatan terkait tim kerja yang ada
        $datakegiatan = DataKegiatan::all(); // Pastikan ini mengambil data kegiatan dari DB

        // Pagination setup
        $perPage = $request->input('per_page', 10);  // Default 10 jika tidak ada input
        $search = $request->input('search');  // Ambil kata kunci pencarian

        // Ambil role pengguna saat ini
        $role = Auth::user()->role;

        // Ambil data MonitoringKegiatan dengan relasi TimKerja dan DataKegiatan dan filter pencarian
        $monitoringKegiatan = MonitoringKegiatan::with(['timkerja', 'datakegiatan', 'targetRealisasiSatker'])
            ->when($search, function ($query) use ($search) {
                return $query->whereHas('timkerja', function ($query) use ($search) {
                    $query->where('nama_tim', 'like', '%' . $search . '%');
                });
            })
            ->when($role == 'Produksi Provinsi' || $role == 'Produksi Kabupaten/Kota', function ($query) {
                return $query->whereHas('timkerja', function ($query) {
                    $query->where('nama_tim', 'like', '%Produksi%'); // Filter untuk tim Produksi
                });
            })
            ->when($role == 'Distribusi Provinsi' || $role == 'Distribusi Kabupaten/Kota', function ($query) {
                return $query->whereHas('timkerja', function ($query) {
                    $query->where('nama_tim', 'like', '%Distribusi%'); // Filter untuk tim Distribusi
                });
            })
            ->when($role == 'Neraca Provinsi' || $role == 'Neraca Kabupaten/Kota', function ($query) {
                return $query->whereHas('timkerja', function ($query) {
                    $query->where('nama_tim', 'like', '%Neraca%'); // Filter untuk tim Neraca
                });
            })
            ->when($role == 'Sosial Provinsi' || $role == 'Sosial Kabupaten/Kota', function ($query) {
                return $query->whereHas('timkerja', function ($query) {
                    $query->where('nama_tim', 'like', '%Sosial%'); // Filter untuk tim Sosial
                });
            })
            ->when($role == 'IPDS Provinsi' || $role == 'IPDS Kabupaten/Kota', function ($query) {
                return $query->whereHas('timkerja', function ($query) {
                    $query->where('nama_tim', 'like', '%IPDS%'); // Filter untuk tim IPDS
                });
            })
            ->paginate($perPage);

        // Waktu saat ini
        $currentDate = Carbon::now();

        // Menambahkan informasi waktu kegiatan, status, target, realisasi, dan persentase
        foreach ($monitoringKegiatan as $item) {
            // Menghitung waktu mulai dan selesai
            $waktuMulai = Carbon::parse($item->waktu_mulai . ' 00:00:00');
            $waktuSelesai = Carbon::parse($item->waktu_selesai . ' 23:59:59');
            $item->waktu_kegiatan = $waktuMulai->format('d-m-Y') . ' - ' . $waktuSelesai->format('d-m-Y');

            // Menentukan status
            if ($currentDate->lessThan($waktuMulai)) {
                $item->status = 'BELUM DIMULAI';
            } elseif ($currentDate->greaterThan($waktuSelesai)) {
                $item->status = 'SELESAI';
            } else {
                $item->status = 'ON PROGRESS';
            }

            // Menghitung akumulasi target dari semua satuan kerja
            $targetRealisasiSatker = target_realisasi_satker::with('updateRealisasi')
                ->where('id_monitoring_kegiatan', $item->id)
                ->get();

            $item->target = $targetRealisasiSatker->sum('target_satker');

            // Menghitung akumulasi realisasi terbaru dari semua satuan kerja
            $item->realisasi = $targetRealisasiSatker->sum(function ($satker) {
                return $satker->updateRealisasi->realisasi_satker ?? 0;
            });

            // Menghitung persentase realisasi
            $item->persentase = $item->target > 0
                ? round(($item->realisasi / $item->target) * 100, 2) . '%'
                : '0%';

            // Menentukan periode kegiatan
            if ($item->tahun_kegiatan) {
                if ($item->bulan) {
                    $item->periode_kegiatan = $item->bulan . ' - ' . $item->tahun_kegiatan;
                } elseif ($item->triwulan) {
                    $item->periode_kegiatan = 'Triwulan ' . $item->triwulan . ' - ' . $item->tahun_kegiatan;
                } elseif ($item->semester) {
                    $item->periode_kegiatan = 'Semester ' . $item->semester . ' - ' . $item->tahun_kegiatan;
                } else {
                    $item->periode_kegiatan = $item->tahun_kegiatan;
                }
            }
        }

        // Mengirimkan data ke view
        return view('monitoring-kegiatan', compact('satuankerja', 'timkerja', 'datakegiatan', 'monitoringKegiatan', 'canAccessVerifikasi'));
    }

    public function getKegiatanByTim(Request $request)
    {
        $tim_id = $request->input('tim_id'); // Ambil ID tim yang dipilih

        // Ambil data kegiatan berdasarkan id_tim_kerja
        $kegiatan = DataKegiatan::where('id_tim_kerja', $tim_id)->get();

        // Debugging: Log ID tim yang dipilih dan data kegiatan yang diterima (ubah menjadi array)
        Log::info('Tim ID:', ['tim_id' => $tim_id]);
        Log::info('Kegiatan yang terkait dengan tim ini:', $kegiatan->toArray()); // Konversi ke array

        // Jika tidak ada kegiatan untuk tim ini
        if ($kegiatan->isEmpty()) {
            return response()->json(['message' => 'Tidak ada kegiatan untuk tim ini']);
        }

        // Mengembalikan data kegiatan dalam format JSON
        return response()->json($kegiatan);
    }

    public function store(Request $request)
    {
        try {
            // Validasi input dari form
            $validatedData = $request->validate([
                'kode_tim' => 'required',
                'kode_kegiatan' => 'required',
                'tahun_kegiatan' => 'required',
                'waktu_mulai' => 'required',
                'waktu_selesai' => 'required',
                'satuan_kerja' => 'required|array',
                'target_sampel' => 'required|array', // Pastikan target_sampel adalah array
                'id_data_kegiatan' => 'required',
                'bulan' => 'nullable',
                'triwulan' => 'nullable',
                'semester' => 'nullable',
            ]);

            // Simpan data Monitoring Kegiatan
            $monitoringKegiatan = MonitoringKegiatan::create([
                'kode_tim' => $validatedData['kode_tim'],
                'kode_kegiatan' => $validatedData['kode_kegiatan'],
                'id_data_kegiatan' => $validatedData['id_data_kegiatan'],
                'tahun_kegiatan' => $validatedData['tahun_kegiatan'],
                'bulan' => $validatedData['bulan'] ?? null,
                'triwulan' => $validatedData['triwulan'] ?? null,
                'semester' => $validatedData['semester'] ?? null,
                'waktu_mulai' => $validatedData['waktu_mulai'],
                'waktu_selesai' => $validatedData['waktu_selesai'],
                'realisasi_kegiatan' => 0,
            ]);

            // Simpan target untuk setiap satuan kerja yang dipilih
            foreach ($validatedData['satuan_kerja'] as $satuanKerjaId) {
                $targetRealisasiSatker = new target_realisasi_satker();
                $targetRealisasiSatker->id_monitoring_kegiatan = $monitoringKegiatan->id;
                $targetRealisasiSatker->kode_satuan_kerja = $satuanKerjaId;
                // Ambil nilai target sampel untuk satuan kerja yang dipilih
                $targetRealisasiSatker->target_satker = $validatedData['target_sampel'][$satuanKerjaId];
                $targetRealisasiSatker->save(); // Simpan target untuk satuan kerja ini

                $updateRealisasiSatker = new update_target_realisasi();
                $updateRealisasiSatker->id_target_realisasi = $targetRealisasiSatker->id;
                $updateRealisasiSatker->realisasi_satker = 0;
                $updateRealisasiSatker->bukti_dukung_realisasi = null;
                $updateRealisasiSatker->keterangan = null;
                $updateRealisasiSatker->save();
            }
        } catch (\Throwable $th) {
            Log::error($th);
        }

        return redirect()->route('monitoring-kegiatan');
    }

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'kode_tim' => 'required',
                'kode_kegiatan' => 'required',
                'tahun_kegiatan' => 'required',
                'waktu_mulai' => 'required|date',
                'waktu_selesai' => 'required|date|after_or_equal:waktu_mulai',
                'satuan_kerja' => 'required|array',
                'target_sampel' => 'required|array',
                'bulan' => 'nullable',
                'triwulan' => 'nullable',
                'semester' => 'nullable'
            ]);

            $monitoringKegiatan = MonitoringKegiatan::findOrFail($id);

            $monitoringKegiatan->update([
                'kode_tim' => $validatedData['kode_tim'],
                'kode_kegiatan' => $validatedData['kode_kegiatan'],
                'tahun_kegiatan' => $validatedData['tahun_kegiatan'],
                'bulan' => $validatedData['bulan'] ?? null,
                'triwulan' => $validatedData['triwulan'] ?? null,
                'semester' => $validatedData['semester'] ?? null,
                'waktu_mulai' => $validatedData['waktu_mulai'],
                'waktu_selesai' => $validatedData['waktu_selesai']
            ]);

            // Update atau tambahkan target untuk setiap satuan kerja yang dipilih
            foreach ($validatedData['satuan_kerja'] as $satuanKerjaId) {
                // Update data target_realisasi_satker yang sudah ada
                $targetRealisasiSatker = target_realisasi_satker::updateOrCreate(
                    [
                        'kode_satuan_kerja' => $satuanKerjaId,
                        'id_monitoring_kegiatan' => $monitoringKegiatan->id,
                    ],
                    [
                        'target_satker' => $validatedData['target_sampel'][$satuanKerjaId],
                    ]
                );

                update_target_realisasi::updateOrCreate(
                    [
                        'id_target_realisasi' => $targetRealisasiSatker->id,
                    ],
                    [
                        'realisasi_satker' => $targetRealisasiSatker->realisasi_satker ?? 0,
                        'bukti_dukung_realisasi' => null,
                        'keterangan' => null,
                    ]
                );
            }

            return redirect()->route('monitoring-kegiatan')->with('success', 'Data berhasil diperbarui');
        } catch (\Throwable $th) {
            Log::error($th);
            return redirect()->route('monitoring-kegiatan')->with('error', 'Terjadi kesalahan saat memperbarui data');
        }
    }

    public function destroy($id)
    {
        $monitoringkegiatan = MonitoringKegiatan::findOrFail($id);
        $monitoringkegiatan->delete();

        return redirect()->route('monitoring-kegiatan')->with('success', 'Kegiatan berhasil dihapus');
    }
}
