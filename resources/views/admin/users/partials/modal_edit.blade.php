<style>
    /* Backdrop modern: memberikan blur pada background tapi modal tetap terang */
    #adminEditModal {
        background: rgba(15, 23, 42, 0.4); /* Biru gelap transparan tipis */
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }

    .modal-content-popup {
        border-radius: 24px !important;
        border: none !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
        background: #ffffff !important;
    }

    .input-custom {
        border-radius: 12px !important;
        padding: 12px 16px !important;
        border: 1px solid #e2e8f0 !important;
        background-color: #f8fafc !important;
        transition: all 0.2s;
    }

    .input-custom:focus {
        background-color: #ffffff !important;
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1) !important;
    }

    .form-label-small {
        font-size: 0.75rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
        display: block;
    }

    /* Memastikan tidak ada backdrop double */
    .modal-backdrop {
        display: none !important;
    }
</style>

<div class="modal fade" id="adminEditModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
        <div class="modal-content modal-content-popup">

            <div class="modal-header border-0 pb-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <h4 class="fw-bold m-0" style="color: #1e293b;">Edit Profil</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('admin.user.update', Auth::user()->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-body px-4 py-4">
                    <div class="mb-3">
                        <label class="form-label-small">NIDN / NIP</label>
                        <input type="text" name="nidn" class="form-control input-custom" value="{{ $user->nidn }}" placeholder="Masukkan NIDN atau NIP">
                    </div>

                    <div class="mb-3">
                        <label class="form-label-small">Email Instansi</label>
                        <input type="email" name="email" class="form-control input-custom" value="{{ $user->email }}" required placeholder="contoh@yarsi.ac.id">
                    </div>

                    <div class="mb-4">
                        <label class="form-label-small">Fakultas / Unit Kerja</label>
                        <select name="fakultas" class="form-select input-custom" required>
                            <option value="" disabled {{ !$user->fakultas ? 'selected' : '' }}>Pilih Unit Kerja...</option>
                            <option value="Fakultas Kedokteran" {{ $user->fakultas == 'Fakultas Kedokteran' ? 'selected' : '' }}>Fakultas Kedokteran</option>
                            <option value="Fakultas Ekonomi dan Bisnis" {{ $user->fakultas == 'Fakultas Ekonomi dan Bisnis' ? 'selected' : '' }}>Fakultas Ekonomi dan Bisnis</option>
                            <option value="Fakultas Hukum" {{ $user->fakultas == 'Fakultas Hukum' ? 'selected' : '' }}>Fakultas Hukum</option>
                            <option value="Fakultas Teknologi Informasi" {{ $user->fakultas == 'Fakultas Teknologi Informasi' ? 'selected' : '' }}>Fakultas Teknologi Informasi</option>
                            <option value="Fakultas Psikologi" {{ $user->fakultas == 'Fakultas Psikologi' ? 'selected' : '' }}>Fakultas Psikologi</option>
                            <option value="Fakultas Kedokteran Gigi" {{ $user->fakultas == 'Fakultas Kedokteran Gigi' ? 'selected' : '' }}>Fakultas Kedokteran Gigi</option>
                            <option value="Pascasarjana" {{ $user->fakultas == 'Pascasarjana' ? 'selected' : '' }}>Pascasarjana</option>
                            <option value="Unit Kerja / Administrasi" {{ $user->fakultas == 'Unit Kerja / Administrasi' ? 'selected' : '' }}>Unit Kerja / Administrasi</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-dark w-100 py-3 fw-bold shadow-sm" style="border-radius: 14px; background: #1e293b; border: none;">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
