@extends('layouts.admin')

@section('title', 'Manage Feedback')
@section('page-title', 'Manage Feedback')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Daftar Kritik & Saran</h4>
    <span class="badge bg-dark fs-6">{{ $feedbacks->total() }} feedback</span>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Nama</th>
                    <th>Pesan</th>
                    <th>Rating</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($feedbacks as $feedback)
                <tr>
                    <td>{{ ($feedbacks->currentPage()-1)*$feedbacks->perPage() + $loop->iteration }}</td>
                    <td class="fw-bold">{{ $feedback->name ?? ($feedback->user->name ?? 'Guest') }}</td>
                    <td>{{ Str::limit($feedback->message, 80) }}</td>
                    <td>
                        @if($feedback->rating)
                            @for($i = 1; $i <= 5; $i++)
                                <i class="{{ $i <= $feedback->rating ? 'fas' : 'far' }} fa-star text-warning"></i>
                            @endfor
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>{{ $feedback->created_at?->format('d M Y H:i') }}</td>
                    <td>
                        @if($feedback->is_read)
                            <span class="badge bg-success">Sudah Dibaca</span>
                        @else
                            <span class="badge bg-warning text-dark">Belum Dibaca</span>
                        @endif
                    </td>
                    <td>
                        @if(!$feedback->is_read)
                        <form action="{{ route('admin.feedback.read', $feedback) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-primary" title="Tandai dibaca">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>
                        @endif

                        <form action="{{ route('admin.feedback.destroy', $feedback) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus feedback ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">Belum ada feedback.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($feedbacks->hasPages())
    <div class="card-footer bg-white border-0 d-flex justify-content-center py-3">
        {{ $feedbacks->onEachSide(1)->links() }}
    </div>
    @endif
</div>
@endsection
