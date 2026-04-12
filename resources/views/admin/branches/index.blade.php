@extends('layouts.admin')

@section('title', 'Manage Branches')
@section('page-title', 'Branches')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Daftar Cabang</h4>
    <a href="{{ route('admin.branches.create') }}" class="btn btn-danger">
        <i class="fas fa-plus"></i> Tambah Cabang
    </a>
</div>


@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Nama Cabang</th>
                    <th>Alamat</th>
                    <th>Phone</th>
                    <th>Barbers</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($branches as $branch)
                <tr>
                    <td>{{ ($branches->currentPage()-1)*$branches->perPage() + $loop->iteration }}</td>
                    <td class="fw-bold">{{ $branch->name }}</td>
                    <td>{{ $branch->address }}</td>
                    <td>{{ $branch->phone }}</td>
                    <td><span class="badge bg-secondary">{{ $branch->barbers_count }} barber</span></td>
                    <td>
                        <a href="{{ route('admin.branches.edit', $branch) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.branches.destroy', $branch) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus cabang {{ $branch->name }}?')">
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
                    <td colspan="6" class="text-center text-muted py-4">Belum ada cabang.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($branches->hasPages())
    <div class="card-footer bg-white border-0 d-flex justify-content-center py-3">
        {{ $branches->onEachSide(1)->links() }}
    </div>
    @endif
</div>
@endsection
