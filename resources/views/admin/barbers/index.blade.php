@extends('layouts.admin')

@section('title', 'Manage Barbers')
@section('page-title', 'Barbers')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Daftar Barber</h4>
    <a href="{{ route('admin.barbers.create') }}" class="btn btn-danger">
        <i class="fas fa-plus"></i> Tambah Barber
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Photo</th>
                    <th>Nama</th>
                    <th>Status</th>
                    <th>Cabang</th>
                    <th>Phone</th>
                    <th>Experience</th>
                    <th>Bio</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($barbers as $barber)
                <tr>
                    <td>{{ ($barbers->currentPage()-1)*$barbers->perPage() + $loop->iteration }}</td>
                    <td>
                        @if($barber->photo)
                            <img src="{{ asset('storage/' . $barber->photo) }}" alt="{{ $barber->user->name }}" width="60" height="60" class="rounded-circle object-fit-cover">
                        @else
                            <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center text-white" style="width:60px;height:60px;">
                                <i class="fas fa-user"></i>
                            </div>
                        @endif
                    </td>
                    <td class="fw-bold">{{ $barber->user->name }}</td>
                    <td>
                        @if($barber->is_active)
                            <span class="badge bg-success">Aktif</span>
                        @else
                            <span class="badge bg-secondary">Libur</span>
                        @endif
                    </td>
                    <td>{{ $barber->branch->name ?? '-' }}</td>
                    <td>{{ $barber->phone }}</td>
                    <td>{{ $barber->experience }} tahun</td>
                    <td>{{ Str::limit($barber->bio, 50) ?? '-' }}</td>
                    <td>
                        <a href="{{ route('admin.barbers.edit', $barber) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.barbers.toggleStatus', $barber) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-secondary" title="Toggle Status">
                                <i class="fas fa-power-off"></i>
                            </button>
                        </form>
                        <form action="{{ route('admin.barbers.destroy', $barber) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus barber ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">Belum ada barber.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($barbers->hasPages())
    <div class="card-footer bg-white border-0 d-flex justify-content-center">
        {{ $barbers->links() }}
    </div>
    @endif
</div>
@endsection
