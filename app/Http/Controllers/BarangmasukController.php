<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Barangmasuk;
use App\Models\Barang;
use App\Models\Barangkeluar;
use Illuminate\Support\Facades\Storage;

class BarangmasukController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $tgl_masuk = $request->input('tgl_masuk');
    
        $barangmasuk = BarangMasuk::with('barang')
            ->when($search, function ($query, $search) {
                return $query->whereHas('barang', function($q) use ($search) {
                    $q->where('merk', 'like', '%' . $search . '%')
                      ->orWhere('seri', 'like', '%' . $search . '%');
                });
            })
            ->when($tgl_masuk, function ($query, $tgl_masuk) {
                return $query->whereDate('tgl_masuk', $tgl_masuk);
            })
            ->latest()
            ->paginate(10);
    
        $barangmasuk->appends(['search' => $search, 'tgl_masuk' => $tgl_masuk]);
    
        return view('v_barangmasuk.index', compact('barangmasuk'))
            ->with('i', (request()->input('page', 1) - 1) * 10);
    }

    public function create()
    {
        $merkBarang = Barang::pluck('merk', 'id');
        // Menampilkan form untuk membuat data barang masuk
        return view('v_barangmasuk.create', compact('merkBarang'));
    }

    public function store(Request $request)
    {
        // Validasi data dari request jika diperlukan
        $validatedData = $request->validate([
            'tgl_masuk' => 'required',
            'qty_masuk' => 'required|numeric|min:0',
            'barang_id' => 'required',
            // Tambahkan validasi lainnya sesuai kebutuhan
        ]);

        // Simpan data barang masuk baru ke database
        $barangMasuk = new Barangmasuk();
        $barangMasuk->tgl_masuk = $request->tgl_masuk;
        $barangMasuk->qty_masuk = $request->qty_masuk;
        $barangMasuk->barang_id = $request->barang_id;
        // Tambahkan kolom lainnya yang perlu diisi
        $barangMasuk->save();

        return redirect()->route('barangmasuk.index')->with('success', 'Barang masuk berhasil ditambahkan');
    }

    public function show($id)
    {
        // Mengambil data barang masuk berdasarkan ID
        $barangmasuk = Barangmasuk::findOrFail($id);
    
        return view('v_barangmasuk.show', compact('barangmasuk'));
    }

    public function edit($id)
    {
        // Mengambil data barang masuk untuk diedit berdasarkan ID
        $barangmasuk = Barangmasuk::findOrFail($id);
        $merkBarang = Barang::pluck('merk', 'id');
    
        return view('v_barangmasuk.edit', compact('barangmasuk', 'merkBarang'));
    }
    
    public function update(Request $request, $id)
{
    // Validasi data dari request jika diperlukan
    $validatedData = $request->validate([
        'tgl_masuk' => 'required',
        'qty_masuk' => 'required|numeric|min:1',
        'barang_id' => 'required|exists:barang,id',
    ]);
    
    // Temukan data barang masuk yang akan diupdate
    $barangMasuk = Barangmasuk::findOrFail($id);
    $barang = Barang::findOrFail($request->barang_id);
    
    // Hitung perbedaan antara stok yang baru dan yang sebelumnya
    $difference = $request->qty_masuk - $barangMasuk->qty_masuk;
    
    // Cek apakah stok cukup jika perbedaan negatif
    if ($difference < 0 && $barang->stok < abs($difference)) {
        return redirect()->back()->withErrors(['qty_masuk' => 'Perubahan kuantitas melebihi stok yang tersedia'])->withInput();
    }
    
    // Simpan perubahan data barang masuk ke database
    $barangMasuk->tgl_masuk = $request->tgl_masuk;
    $barangMasuk->qty_masuk = $request->qty_masuk;
    $barangMasuk->save();
    
    // Update stok barang
    $barang->stok += $difference;
    $barang->save();
    
    return redirect()->route('barangmasuk.index')->with('success', 'Barang masuk berhasil diperbarui');
}
    
    
    public function destroy($id)
    {
        // Menghapus data barang masuk berdasarkan ID
        $barangmasuk = Barangmasuk::findOrFail($id);
        $barangKeluarCount = BarangKeluar::where('barang_id', $barangmasuk->barang_id)
        ->where('tgl_keluar', '>=', $barangmasuk->tgl_masuk)
        ->count();

        // Jika ada catatan barangkeluar terkait, kembalikan pesan kesalahan
        if ($barangKeluarCount > 0) {
        return redirect()->route('barangmasuk.index')->withErrors(['error' => 'Data Barang Masuk tidak dapat dihapus karena ada Barang Keluar yang terkait.']);
        }
        $barangmasuk->delete();
    
        return redirect()->route('barangmasuk.index')->with('success', 'Barang masuk berhasil dihapus');
    }
    

}