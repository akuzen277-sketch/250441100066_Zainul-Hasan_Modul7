@extends('layouts.main')

@section('title', 'Detail Peminjaman')

@section('content')
    <div class="content-card">
        <h1>Detail Peminjaman</h1>

        <dl>
            <dt>Peminjam</dt>
            <dd>{{ $peminjaman->anggota->nama ?? '-' }}</dd>

            <dt>Buku</dt>
            <dd>{{ $peminjaman->buku->judul ?? '-' }}</dd>

            <dt>Tanggal pinjam</dt>
            <dd>{{ $peminjaman->tanggal_pinjam }}</dd>

            <dt>Tanggal kembali</dt>
            <dd>{{ $peminjaman->tanggal_kembali ?? 'Belum ditentukan' }}</dd>

            <dt>Status</dt>
            <dd>{{ ucfirst($peminjaman->status) }}</dd>
        </dl>

        <a href="{{ route('peminjaman.index') }}">Kembali ke daftar peminjaman</a>
    </div>
@endsection
