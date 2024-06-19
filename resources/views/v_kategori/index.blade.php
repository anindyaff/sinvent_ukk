@extends('layouts.main')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <!-- tambah search -->
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <a href="{{ route('kategori.create') }}" class="btn btn-md btn-success">TAMBAH KATEGORI</a>
                        </div>
                        <div class="col-md-4">
                            <form action="{{ route('kategori.index') }}" method="GET">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Cari kategori..." value="{{ request()->input('search') }}">
                                    <div class="input-group-append">
                                        <button class="btn btn-secondary" type="submit"><i class="fa fa-search"></i></button>
                                    </div>
                                    @if(request()->filled('search'))
                                        <div class="input-group-append">
                                            <a href="{{ route('kategori.index') }}" class="btn btn-secondary"><i class="fa fa-times"></i></a>
                                        </div>
                                    @endif
                                </div>
                            </form>
                        </div>
                        @if(session('success'))
                        <div class="alert alert-success mt-3">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('Gagal'))
                        <div class="alert alert-danger mt-3">
                            {{ session('Gagal') }}
                        </div>
                    @endif
                    </div>
                </div>

                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>DESKRIPSI</th>
                            <th>KATEGORI</th>
                            <th>KETERANGAN</th>
                            <th style="width: 15%">AKSI</th>

                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rsetkategori as $rk)
                            <tr>
                                <td>{{ ($rsetkategori->currentPage() -1) * $rsetkategori->perPage() + $loop->index + 1 }}</td>
                                <td>{{ $rk->deskripsi  }}</td>
                                <td>{{ $rk->kategori  }}</td>
                                <td>{{ $rk->ketKategori  }}</td>
                                <td class="text-center"> 
                                    <form onsubmit="return confirm('Apakah Anda Yakin ?');" action="{{ route('kategori.destroy', $rk->id) }}" method="POST">
                                        <a href="{{ route('kategori.show', $rk->id) }}" class="btn btn-sm btn-dark"><i class="fa fa-eye"></i></a>
                                        <a href="{{ route('kategori.edit', $rk->id) }}" class="btn btn-sm btn-primary"><i class="fa fa-pencil-alt"></i></a>
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <div class="alert">
                                Data barang belum tersedia
                            </div>
                        @endforelse
                    </tbody>
                    
                </table>
                {{-- {{ $barang->links() }} --}}

            </div>
        </div>
    </div>
@endsection