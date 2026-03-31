@extends('layouts.admin')

@section('title', 'Edit Barber')
@section('page-title', 'Edit Barber')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="{{ route('admin.barbers.update', $barber) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <strong>Terjadi kesalahan:</strong>
                            <ul class="mb-0 mt-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="user_id" class="form-label fw-bold">Pilih User (role: barber)</label>
                        <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id" required>
                            <option value="">-- Pilih User --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id', $barber->user_id) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="branch_id" class="form-label fw-bold">Cabang</label>
                        <select class="form-select @error('branch_id') is-invalid @enderror" id="branch_id" name="branch_id">
                            <option value="">-- Pilih Cabang (opsional) --</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id', $barber->branch_id) == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('branch_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label fw-bold">Phone</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $barber->phone) }}" required>
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="experience" class="form-label fw-bold">Experience (tahun)</label>
                            <input type="number" class="form-control @error('experience') is-invalid @enderror" id="experience" name="experience" value="{{ old('experience', $barber->experience) }}" min="0" required>
                            @error('experience') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="bio" class="form-label fw-bold">Bio</label>
                        <textarea class="form-control @error('bio') is-invalid @enderror" id="bio" name="bio" rows="3">{{ old('bio', $barber->bio) }}</textarea>
                        @error('bio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="photo" class="form-label fw-bold">Photo</label>
                        @if($barber->photo)
                            <div class="mb-2" id="currentWrap">
                                <img src="{{ asset('storage/' . $barber->photo) }}" alt="{{ $barber->user->name }}"
                                     style="max-width: 100%; max-height: 250px; height: auto; object-fit: contain;" class="rounded">
                            </div>
                        @endif
                        <input type="file" class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo" accept="image/jpeg,image/png,image/jpg,image/gif" onchange="previewImage(event)">
                        <small class="text-muted">Kosongkan jika tidak ingin mengubah photo</small>
                        @error('photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div id="previewWrap" class="mt-2 d-none">
                            <img id="photoPreview" class="rounded" style="max-width: 100%; max-height: 250px; height: auto; object-fit: contain;">
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-save"></i> Update
                        </button>
                        <a href="{{ route('admin.barbers.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function previewImage(event) {
    var preview = document.getElementById('photoPreview');
    var wrap = document.getElementById('previewWrap');
    var current = document.getElementById('currentWrap');
    var file = event.target.files[0];
    if (file) {
        var reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            wrap.classList.remove('d-none');
            if (current) current.classList.add('d-none');
        };
        reader.readAsDataURL(file);
    } else {
        wrap.classList.add('d-none');
        if (current) current.classList.remove('d-none');
    }
}
</script>
@endsection
