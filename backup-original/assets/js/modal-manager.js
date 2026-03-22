/**
 * Advanced Modal Implementation for KSP Lam Gabe Jaya
 * Comprehensive Modal Management with Bootstrap & jQuery
 */

class ModalManager {
    constructor() {
        this.modals = new Map();
        this.init();
    }

    init() {
        this.setupModalTemplates();
        this.setupModalTriggers();
        this.setupModalEvents();
        this.setupModalValidation();
        this.setupModalAJAX();
    }

    // Modal Templates
    setupModalTemplates() {
        // Add Member Modal
        this.addModalTemplate('addMember', `
            <div class="modal fade" id="addMemberModal" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-user-plus me-2"></i>Tambah Anggota Baru
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="addMemberForm" class="needs-validation" novalidate>
                                <!-- Personal Information -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="mb-3 border-bottom pb-2">
                                            <i class="fas fa-user me-2"></i>Informasi Personal
                                        </h6>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Nama Lengkap *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-user"></i>
                                            </span>
                                            <input type="text" class="form-control" name="fullName" required>
                                            <div class="invalid-feedback">Nama lengkap harus diisi</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Username *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-at"></i>
                                            </span>
                                            <input type="text" class="form-control" name="username" required>
                                            <div class="invalid-feedback">Username harus diisi</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Email *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-envelope"></i>
                                            </span>
                                            <input type="email" class="form-control" name="email" required>
                                            <div class="invalid-feedback">Email tidak valid</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Telepon *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-phone"></i>
                                            </span>
                                            <input type="tel" class="form-control" name="phone" required>
                                            <div class="invalid-feedback">Nomor telepon harus diisi</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Tanggal Lahir</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-calendar"></i>
                                            </span>
                                            <input type="date" class="form-control" name="birthDate">
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Jenis Kelamin</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-venus-mars"></i>
                                            </span>
                                            <select class="form-select" name="gender">
                                                <option value="">Pilih</option>
                                                <option value="L">Laki-laki</option>
                                                <option value="P">Perempuan</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Identity Information -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="mb-3 border-bottom pb-2">
                                            <i class="fas fa-id-card me-2"></i>Informasi Identitas
                                        </h6>
                                    </div>
                                    <div class="col-12">
                                        <div id="identityFields">
                                            <!-- First Identity Field -->
                                            <div class="identity-field mb-3">
                                                <div class="row align-items-end">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Jenis Identitas *</label>
                                                        <select class="form-select identity-type" name="identityType[]" required>
                                                            <option value="">Pilih Jenis</option>
                                                            <option value="KTP">KTP</option>
                                                            <option value="SIM">SIM</option>
                                                            <option value="PASSPORT">Passport</option>
                                                            <option value="NPWP">NPWP</option>
                                                            <option value="BPJS">BPJS</option>
                                                            <option value="KK">Kartu Keluarga</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">Nomor Identitas *</label>
                                                        <input type="text" class="form-control identity-number" name="identityNumber[]" required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">Upload Scan</label>
                                                        <input type="file" class="form-control identity-file" name="identityFile[]" accept="image/*">
                                                    </div>
                                                    <div class="col-md-1">
                                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="this.removeIdentityField(this)" style="display: none;">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="this.addIdentityField()">
                                            <i class="fas fa-plus me-1"></i>Tambah Identitas
                                        </button>
                                    </div>
                                </div>

                                <!-- Address Information -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="mb-3 border-bottom pb-2">
                                            <i class="fas fa-map-marker-alt me-2"></i>Informasi Alamat
                                        </h6>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Provinsi *</label>
                                        <select class="form-select" name="province" id="provinceSelect" required>
                                            <option value="">Pilih Provinsi</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Kabupaten/Kota *</label>
                                        <select class="form-select" name="regency" id="regencySelect" required disabled>
                                            <option value="">Pilih Kabupaten/Kota</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Kecamatan *</label>
                                        <select class="form-select" name="district" id="districtSelect" required disabled>
                                            <option value="">Pilih Kecamatan</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Desa/Kelurahan *</label>
                                        <select class="form-select" name="village" id="villageSelect" required disabled>
                                            <option value="">Pilih Desa/Kelurahan</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">RT</label>
                                        <input type="text" class="form-control" name="rt" placeholder="Nomor RT">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">RW</label>
                                        <input type="text" class="form-control" name="rw" placeholder="Nomor RW">
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Alamat Lengkap</label>
                                        <textarea class="form-control" name="fullAddress" rows="2" placeholder="Detail alamat (jalan, blok, nomor rumah, dll)"></textarea>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Kode Pos</label>
                                        <input type="text" class="form-control" name="postalCode" placeholder="Kode Pos">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Koordinat (Opsional)</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="latitude" placeholder="Latitude">
                                            <input type="text" class="form-control" name="longitude" placeholder="Longitude">
                                            <button type="button" class="btn btn-outline-secondary" onclick="this.getCurrentLocation()">
                                                <i class="fas fa-map-pin"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Account Information -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="mb-3 border-bottom pb-2">
                                            <i class="fas fa-user-cog me-2"></i>Informasi Akun
                                        </h6>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Password *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                            <input type="password" class="form-control" name="password" required>
                                            <button type="button" class="btn btn-outline-secondary" onclick="this.togglePasswordVisibility(this)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <div class="invalid-feedback">Password harus diisi</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Konfirmasi Password *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                            <input type="password" class="form-control" name="confirmPassword" required>
                                            <button type="button" class="btn btn-outline-secondary" onclick="this.togglePasswordVisibility(this)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <div class="invalid-feedback">Konfirmasi password harus sama</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Role *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-user-tag"></i>
                                            </span>
                                            <select class="form-select" name="role" required>
                                                <option value="">Pilih Role</option>
                                                <option value="member">Anggota</option>
                                                <option value="staff">Staff</option>
                                                <option value="admin">Admin</option>
                                            </select>
                                            <div class="invalid-feedback">Role harus dipilih</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Additional Information -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="mb-3 border-bottom pb-2">
                                            <i class="fas fa-info-circle me-2"></i>Informasi Tambahan
                                        </h6>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Pekerjaan</label>
                                        <input type="text" class="form-control" name="occupation" placeholder="Pekerjaan">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Penghasilan/Bulan</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" class="form-control" name="income" min="0" step="100000">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Status Pernikahan</label>
                                        <select class="form-select" name="maritalStatus">
                                            <option value="">Pilih Status</option>
                                            <option value="single">Belum Menikah</option>
                                            <option value="married">Menikah</option>
                                            <option value="divorced">Cerai</option>
                                            <option value="widowed">Duda/Janda</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Pendidikan Terakhir</label>
                                        <select class="form-select" name="education">
                                            <option value="">Pilih Pendidikan</option>
                                            <option value="sd">SD</option>
                                            <option value="smp">SMP</option>
                                            <option value="sma">SMA</option>
                                            <option value="d3">D3</option>
                                            <option value="s1">S1</option>
                                            <option value="s2">S2</option>
                                            <option value="s3">S3</option>
                                        </select>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Foto Profil</label>
                                        <div class="file-upload-area" data-target="memberPhoto">
                                            <div class="text-center p-4">
                                                <i class="fas fa-camera fa-3x text-muted mb-3"></i>
                                                <p class="mb-2">Klik atau drag foto ke sini</p>
                                                <button type="button" class="btn btn-outline-primary btn-sm">Pilih Foto</button>
                                            </div>
                                            <div class="file-preview d-none">
                                                <img src="" alt="Preview" class="img-fluid rounded">
                                                <button type="button" class="btn btn-sm btn-danger mt-2 remove-file">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </div>
                                        </div>
                                        <input type="file" name="photo" accept="image/*" class="d-none">
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>Batal
                            </button>
                            <button type="button" class="btn btn-outline-primary" onclick="this.resetAddMemberForm()">
                                <i class="fas fa-redo me-1"></i>Reset
                            </button>
                            <button type="button" class="btn btn-primary" onclick="this.saveMember()">
                                <i class="fas fa-save me-1"></i>Simpan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `);

        // Edit Member Modal
        this.addModalTemplate('editMember', `
            <div class="modal fade" id="editMemberModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-warning text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-user-edit me-2"></i>Edit Anggota
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="editMemberForm" class="needs-validation" novalidate>
                                <input type="hidden" name="memberId">
                                <!-- Form fields similar to add member -->
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-warning" onclick="this.updateMember()">
                                <i class="fas fa-save me-1"></i>Update
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `);

        // Delete Confirmation Modal
        this.addModalTemplate('deleteConfirm', `
            <div class="modal fade" id="deleteConfirmModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Hapus
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="text-center">
                                <i class="fas fa-trash-alt fa-4x text-danger mb-3"></i>
                                <h6>Apakah Anda yakin ingin menghapus data ini?</h6>
                                <p class="text-muted">Tindakan ini tidak dapat dibatalkan.</p>
                                <div id="deleteItemInfo"></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>Batal
                            </button>
                            <button type="button" class="btn btn-danger" onclick="this.confirmDelete()">
                                <i class="fas fa-trash me-1"></i>Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `);

        // View Details Modal
        this.addModalTemplate('viewDetails', `
            <div class="modal fade" id="viewDetailsModal" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-eye me-2"></i>Detail Informasi
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div id="detailsContent"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="button" class="btn btn-primary" onclick="this.editFromDetails()">
                                <i class="fas fa-edit me-1"></i>Edit
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `);

        // Loan Application Modal
        this.addModalTemplate('loanApplication', `
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-hand-holding-usd me-2"></i>Ajukan Pinjaman Baru
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="loanApplicationForm" class="needs-validation" novalidate>
                                <!-- Personal Information -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="mb-3 border-bottom pb-2">
                                            <i class="fas fa-user me-2"></i>Informasi Personal Peminjam
                                        </h6>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Nama Lengkap *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-user"></i>
                                            </span>
                                            <input type="text" class="form-control" name="borrowerName" required>
                                            <div class="invalid-feedback">Nama lengkap harus diisi</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Email *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-envelope"></i>
                                            </span>
                                            <input type="email" class="form-control" name="borrowerEmail" required>
                                            <div class="invalid-feedback">Email tidak valid</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Telepon *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-phone"></i>
                                            </span>
                                            <input type="tel" class="form-control" name="borrowerPhone" required>
                                            <div class="invalid-feedback">Nomor telepon harus diisi</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Tanggal Lahir</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-calendar"></i>
                                            </span>
                                            <input type="date" class="form-control" name="borrowerBirthDate">
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Jenis Kelamin</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-venus-mars"></i>
                                            </span>
                                            <select class="form-select" name="borrowerGender">
                                                <option value="">Pilih</option>
                                                <option value="L">Laki-laki</option>
                                                <option value="P">Perempuan</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Pekerjaan *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-briefcase"></i>
                                            </span>
                                            <input type="text" class="form-control" name="borrowerOccupation" required>
                                            <div class="invalid-feedback">Pekerjaan harus diisi</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Identity Information -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="mb-3 border-bottom pb-2">
                                            <i class="fas fa-id-card me-2"></i>Informasi Identitas Peminjam
                                        </h6>
                                    </div>
                                    <div class="col-12">
                                        <div id="borrowerIdentityFields">
                                            <!-- First Identity Field -->
                                            <div class="identity-field mb-3">
                                                <div class="row align-items-end">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Jenis Identitas *</label>
                                                        <select class="form-select identity-type" name="borrowerIdentityType[]" required>
                                                            <option value="">Pilih Jenis</option>
                                                            <option value="KTP">KTP</option>
                                                            <option value="SIM">SIM</option>
                                                            <option value="PASSPORT">Passport</option>
                                                            <option value="NPWP">NPWP</option>
                                                            <option value="BPJS">BPJS</option>
                                                            <option value="KK">Kartu Keluarga</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">Nomor Identitas *</label>
                                                        <input type="text" class="form-control identity-number" name="borrowerIdentityNumber[]" required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">Upload Scan</label>
                                                        <input type="file" class="form-control identity-file" name="borrowerIdentityFile[]" accept="image/*">
                                                    </div>
                                                    <div class="col-md-1">
                                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="window.modalManager.removeBorrowerIdentityField(this)" style="display: none;">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.modalManager.addBorrowerIdentityField()">
                                            <i class="fas fa-plus me-1"></i>Tambah Identitas
                                        </button>
                                    </div>
                                </div>

                                <!-- Address Information -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="mb-3 border-bottom pb-2">
                                            <i class="fas fa-map-marker-alt me-2"></i>Informasi Alamat Peminjam
                                        </h6>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Provinsi *</label>
                                        <select class="form-select" name="borrowerProvince" id="borrowerProvinceSelect" required>
                                            <option value="">Pilih Provinsi</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Kabupaten/Kota *</label>
                                        <select class="form-select" name="borrowerRegency" id="borrowerRegencySelect" required disabled>
                                            <option value="">Pilih Kabupaten/Kota</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Kecamatan *</label>
                                        <select class="form-select" name="borrowerDistrict" id="borrowerDistrictSelect" required disabled>
                                            <option value="">Pilih Kecamatan</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Desa/Kelurahan *</label>
                                        <select class="form-select" name="borrowerVillage" id="borrowerVillageSelect" required disabled>
                                            <option value="">Pilih Desa/Kelurahan</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">RT</label>
                                        <input type="text" class="form-control" name="borrowerRT" placeholder="Nomor RT">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">RW</label>
                                        <input type="text" class="form-control" name="borrowerRW" placeholder="Nomor RW">
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Alamat Lengkap</label>
                                        <textarea class="form-control" name="borrowerFullAddress" rows="2" placeholder="Detail alamat (jalan, blok, nomor rumah, dll)"></textarea>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Kode Pos</label>
                                        <input type="text" class="form-control" name="borrowerPostalCode" placeholder="Kode Pos">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Penghasilan/Bulan</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" class="form-control" name="borrowerIncome" min="0" step="100000">
                                        </div>
                                    </div>
                                </div>

                                <!-- Loan Information -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="mb-3 border-bottom pb-2">
                                            <i class="fas fa-hand-holding-usd me-2"></i>Informasi Pinjaman
                                        </h6>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Jenis Pinjaman *</label>
                                        <select class="form-select" name="loanType" required>
                                            <option value="">Pilih Jenis Pinjaman</option>
                                            <option value="produktif">Pinjaman Produktif</option>
                                            <option value="konsumtif">Pinjaman Konsumtif</option>
                                            <option value="investasi">Pinjaman Investasi</option>
                                            <option value="modal-kerja">Modal Kerja</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Jumlah Pinjaman *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" class="form-control" name="amount" min="1000000" step="100000" required>
                                            <div class="invalid-feedback">Minimal Rp 1.000.000</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Jangka Waktu *</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="tenure" min="1" max="60" required>
                                            <span class="input-group-text">Bulan</span>
                                            <div class="invalid-feedback">1-60 bulan</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Suku Bunga (%)</label>
                                        <input type="number" class="form-control" name="interestRate" step="0.1" readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Metode Pembayaran</label>
                                        <select class="form-select" name="paymentMethod">
                                            <option value="angsuran-bulanan">Angsuran Bulanan</option>
                                            <option value="angsuran-mingguan">Angsuran Mingguan</option>
                                            <option value="jatuh-tempo">Jatuh Tempo</option>
                                        </select>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Tujuan Pinjaman *</label>
                                        <textarea class="form-control" name="purpose" rows="3" required placeholder="Jelaskan tujuan penggunaan dana pinjaman"></textarea>
                                        <div class="invalid-feedback">Tujuan pinjaman harus diisi</div>
                                    </div>
                                </div>

                                <!-- Simulation -->
                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <h6 class="mb-3 border-bottom pb-2">
                                            <i class="fas fa-calculator me-2"></i>Simulasi Pinjaman
                                        </h6>
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <small class="text-muted">Plafond Pinjaman</small>
                                                    <h5 class="text-primary" id="simAmount">Rp 0</h5>
                                                </div>
                                                <div class="mb-3">
                                                    <small class="text-muted">Bunga per Bulan</small>
                                                    <h5 id="simInterest">Rp 0</h5>
                                                </div>
                                                <div class="mb-3">
                                                    <small class="text-muted">Total Pembayaran</small>
                                                    <h5 id="simTotal">Rp 0</h5>
                                                </div>
                                                <div class="mb-3">
                                                    <small class="text-muted">Cicilan per Bulan</small>
                                                    <h5 class="text-success" id="simInstallment">Rp 0</h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <h6 class="mb-3 border-bottom pb-2">
                                            <i class="fas fa-shield-alt me-2"></i>Informasi Jaminan
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Jenis Jaminan</label>
                                                <select class="form-select" name="collateralType" id="collateralType">
                                                    <option value="">Pilih Jaminan</option>
                                                    <option value="tanah">Tanah</option>
                                                    <option value="bangunan">Bangunan</option>
                                                    <option value="kendaraan">Kendaraan</option>
                                                    <option value="deposito">Deposito</option>
                                                    <option value="tanpa-jaminan">Tanpa Jaminan</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Nilai Jaminan</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">Rp</span>
                                                    <input type="number" class="form-control" name="collateralValue" step="100000">
                                                </div>
                                            </div>
                                        </div>
                                        <div id="collateralDetails" style="display: none;">
                                            <div class="row">
                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label">Deskripsi Jaminan</label>
                                                    <textarea class="form-control" name="collateralDescription" rows="3" placeholder="Deskripsi detail jaminan (alamat, kondisi, dll)"></textarea>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Upload Dokumen Jaminan</label>
                                                    <input type="file" class="form-control" name="collateralDocument" accept="image/*,.pdf">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Nomor Sertifikat</label>
                                                    <input type="text" class="form-control" name="collateralCertificate" placeholder="Nomor sertifikat jika ada">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Additional Information -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="mb-3 border-bottom pb-2">
                                            <i class="fas fa-info-circle me-2"></i>Informasi Tambahan
                                        </h6>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Status Pernikahan</label>
                                        <select class="form-select" name="maritalStatus">
                                            <option value="">Pilih Status</option>
                                            <option value="single">Belum Menikah</option>
                                            <option value="married">Menikah</option>
                                            <option value="divorced">Cerai</option>
                                            <option value="widowed">Duda/Janda</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Jumlah Tanggungan</label>
                                        <input type="number" class="form-control" name="dependents" min="0" placeholder="Jumlah tanggungan">
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Referensi (Opsional)</label>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <input type="text" class="form-control" name="referenceName" placeholder="Nama referensi">
                                            </div>
                                            <div class="col-md-6">
                                                <input type="tel" class="form-control" name="referencePhone" placeholder="Telepon referensi">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>Batal
                            </button>
                            <button type="button" class="btn btn-outline-primary" onclick="window.modalManager.resetLoanApplicationForm()">
                                <i class="fas fa-redo me-1"></i>Reset
                            </button>
                            <button type="button" class="btn btn-success" onclick="window.modalManager.submitLoanApplication()">
                                <i class="fas fa-paper-plane me-1"></i>Ajukan Pinjaman
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `);

        // Report Generator Modal
        this.addModalTemplate('reportGenerator', `
            <div class="modal fade" id="reportGeneratorModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-secondary text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-file-alt me-2"></i>Generate Laporan
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="reportForm">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Jenis Laporan *</label>
                                        <select class="form-select" name="reportType" required>
                                            <option value="">Pilih Jenis Laporan</option>
                                            <option value="monthly">Laporan Bulanan</option>
                                            <option value="quarterly">Laporan Kuartalan</option>
                                            <option value="annual">Laporan Tahunan</option>
                                            <option value="member">Laporan Anggota</option>
                                            <option value="loan">Laporan Pinjaman</option>
                                            <option value="savings">Laporan Simpanan</option>
                                            <option value="npl">Laporan NPL</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Format Laporan *</label>
                                        <select class="form-select" name="format" required>
                                            <option value="pdf">PDF</option>
                                            <option value="excel">Excel</option>
                                            <option value="csv">CSV</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Periode Awal *</label>
                                        <input type="date" class="form-control" name="startDate" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Periode Akhir *</label>
                                        <input type="date" class="form-control" name="endDate" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Filter Tambahan</label>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="includeCharts" id="includeCharts">
                                                <label class="form-check-label" for="includeCharts">
                                                    Sertakan Grafik
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="includeDetails" id="includeDetails" checked>
                                                <label class="form-check-label" for="includeDetails">
                                                    Detail Lengkap
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="includeSummary" id="includeSummary" checked>
                                                <label class="form-check-label" for="includeSummary">
                                                    Ringkasan
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-secondary" onclick="this.previewReport()">
                                <i class="fas fa-eye me-1"></i>Preview
                            </button>
                            <button type="button" class="btn btn-primary" onclick="this.generateReport()">
                                <i class="fas fa-download me-1"></i>Generate
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `);
    }

    // Add modal template to DOM
    addModalTemplate(name, template) {
        if (!$('#' + name + 'Modal').length) {
            $('body').append(template);
        }
        this.modals.set(name, name + 'Modal');
    }

    // Setup modal triggers
    setupModalTriggers() {
        // Auto-setup data-modal attributes
        $('[data-modal]').each(function() {
            const $trigger = $(this);
            const modalName = $trigger.data('modal');
            const modalId = this.modals.get(modalName);
            
            if (modalId) {
                $trigger.on('click', function(e) {
                    e.preventDefault();
                    this.openModal(modalName, $trigger.data());
                }.bind(this));
            }
        }.bind(this));

        // Setup specific modal triggers
        this.setupMemberModals();
        this.setupLoanModals();
        this.setupReportModals();
    }

    setupMemberModals() {
        // Add member button
        $(document).on('click', '[data-action="add-member"]', function() {
            this.openModal('addMember');
        }.bind(this));

        // Edit member button
        $(document).on('click', '[data-action="edit-member"]', function() {
            const memberId = $(this).data('member-id');
            this.openModal('editMember', { memberId: memberId });
        }.bind(this));

        // View member button
        $(document).on('click', '[data-action="view-member"]', function() {
            const memberId = $(this).data('member-id');
            this.openModal('viewDetails', { memberId: memberId });
        }.bind(this));

        // Delete member button
        $(document).on('click', '[data-action="delete-member"]', function() {
            const memberId = $(this).data('member-id');
            const memberName = $(this).data('member-name');
            this.openModal('deleteConfirm', { 
                memberId: memberId, 
                memberName: memberName,
                type: 'member'
            });
        }.bind(this));
    }

    setupLoanModals() {
        // Apply loan button
        $(document).on('click', '[data-action="apply-loan"]', function() {
            const memberId = $(this).data('member-id');
            this.openModal('loanApplication', { memberId: memberId });
        }.bind(this));

        // Setup loan calculation
        $(document).on('input', '#loanApplicationForm input[name="amount"], #loanApplicationForm input[name="tenure"]', function() {
            this.calculateLoanSimulation();
        }.bind(this));
    }

    setupReportModals() {
        // Generate report button
        $(document).on('click', '[data-action="generate-report"]', function() {
            this.openModal('reportGenerator');
        }.bind(this));
    }

    // Modal Events
    setupModalEvents() {
        // Modal show event
        $('.modal').on('show.bs.modal', function() {
            const $modal = $(this);
            
            // Add loading state
            $modal.find('.modal-content').addClass('modal-loading');
            
            // Setup form validation
            const $form = $modal.find('form');
            if ($form.length) {
                $form.removeClass('was-validated');
                $form[0].reset();
            }
        });

        // Modal shown event
        $('.modal').on('shown.bs.modal', function() {
            const $modal = $(this);
            
            // Remove loading state
            $modal.find('.modal-content').removeClass('modal-loading');
            
            // Focus first input
            const $firstInput = $modal.find('input:visible:first');
            if ($firstInput.length) {
                $firstInput.focus();
            }
            
            // Initialize address database for add member modal
            if ($modal.attr('id') === 'addMemberModal') {
                window.modalManager.loadProvinces();
                window.modalManager.updateRemoveButtons();
                window.modalManager.setupFileUploadAreas();
            }
            
            // Initialize borrower address database for loan application modal
            if ($modal.attr('id') === 'loanApplicationModal') {
                window.modalManager.loadBorrowerProvinces();
                window.modalManager.updateBorrowerRemoveButtons();
                window.modalManager.setupFileUploadAreas();
                
                // Setup collateral type change handler
                $('#collateralType').on('change', function() {
                    const collateralType = $(this).val();
                    if (collateralType && collateralType !== 'tanpa-jaminan') {
                        $('#collateralDetails').show();
                    } else {
                        $('#collateralDetails').hide();
                    }
                });
            }
        });

        // Modal hide event
        $('.modal').on('hide.bs.modal', function() {
            const $modal = $(this);
            
            // Clean up
            $modal.find('.modal-backdrop').remove();
            $modal.removeClass('show');
        });

        // Prevent modal from closing when form is dirty
        $('.modal form').on('submit', function(e) {
            e.preventDefault();
            // Handle form submission
        });
    }

    // Modal Validation
    setupModalValidation() {
        // Bootstrap validation
        $('.modal .needs-validation').each(function() {
            const $form = $(this);
            
            $form.on('submit', function(e) {
                if (!this.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                
                $form.addClass('was-validated');
            });
        });

        // Custom validation
        $('.modal input, .modal select, .modal textarea').each(function() {
            const $input = $(this);
            
            $input.on('blur', function() {
                this.validateField($input);
            }.bind(this));
            
            $input.on('input', function() {
                if ($input.hasClass('is-invalid') || $input.hasClass('is-valid')) {
                    this.validateField($input);
                }
            }.bind(this));
        }.bind(this));
    }

    validateField($input) {
        const value = $input.val().trim();
        const type = $input.attr('type');
        const required = $input.prop('required');
        
        // Remove previous validation states
        $input.removeClass('is-valid is-invalid');
        $input.next('.invalid-feedback, .valid-feedback').remove();
        
        // Check if field is valid
        let isValid = true;
        let message = '';
        
        if (required && !value) {
            isValid = false;
            message = 'Field ini harus diisi.';
        } else if (type === 'email' && value && !this.isValidEmail(value)) {
            isValid = false;
            message = 'Email tidak valid.';
        } else if (type === 'tel' && value && !this.isValidPhone(value)) {
            isValid = false;
            message = 'Nomor telepon tidak valid.';
        }
        
        // Add validation feedback
        const feedbackClass = isValid ? 'valid-feedback' : 'invalid-feedback';
        const feedbackHtml = `<div class="${feedbackClass}">${message}</div>`;
        
        $input.addClass(isValid ? 'is-valid' : 'is-invalid').after(feedbackHtml);
        
        return isValid;
    }

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    isValidPhone(phone) {
        const phoneRegex = /^[\d\s\-\+\(\)]+$/;
        return phoneRegex.test(phone) && phone.replace(/\D/g, '').length >= 10;
    }

    // Modal AJAX
    setupModalAJAX() {
        // Setup AJAX loading states
        $('.modal [data-loading]').each(function() {
            const $element = $(this);
            
            $element.on('click', function() {
                const $modal = $element.closest('.modal');
                const $content = $modal.find('.modal-content');
                
                // Add loading overlay
                const loadingHtml = `
                    <div class="modal-loading-overlay position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-white bg-opacity-90">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Processing...</p>
                        </div>
                    </div>
                `;
                
                $content.css('position', 'relative').append(loadingHtml);
                
                // Simulate loading
                setTimeout(() => {
                    $content.find('.modal-loading-overlay').fadeOut(300, function() {
                        $(this).remove();
                    });
                }, 2000);
            });
        });
    }

    // Modal Management Methods
    openModal(modalName, data = {}) {
        const modalId = this.modals.get(modalName);
        if (!modalId) {
            console.error(`Modal ${modalName} not found`);
            return;
        }

        const $modal = $('#' + modalId);
        
        // Set modal data
        if (Object.keys(data).length > 0) {
            this.setModalData($modal, data);
        }
        
        // Show modal
        const modal = new bootstrap.Modal($modal[0]);
        modal.show();
        
        return modal;
    }

    closeModal(modalName) {
        const modalId = this.modals.get(modalName);
        if (!modalId) return;

        const $modal = $('#' + modalId);
        const modal = bootstrap.Modal.getInstance($modal[0]);
        if (modal) {
            modal.hide();
        }
    }

    setModalData($modal, data) {
        // Set form data
        Object.keys(data).forEach(key => {
            const $input = $modal.find(`[name="${key}"]`);
            if ($input.length) {
                $input.val(data[key]);
            }
        });
        
        // Set content data
        if (data.memberName) {
            $modal.find('#deleteItemInfo').html(`
                <div class="alert alert-warning">
                    <strong>${data.memberName}</strong> akan dihapus secara permanen.
                </div>
            `);
        }
    }

    // Modal Actions
    async saveMember() {
        const $form = $('#addMemberForm');
        
        if (!$form[0].checkValidity()) {
            $form.addClass('was-validated');
            return;
        }
        
        // Show loading
        this.showModalLoading('addMember');
        
        try {
            // Collect form data including address IDs
            const formData = new FormData($form[0]);
            
            // Add address data with IDs from alamat_db
            const addressData = {
                province_id: document.getElementById('provinceSelect').value,
                regency_id: document.getElementById('regencySelect').value,
                district_id: document.getElementById('districtSelect').value,
                village_id: document.getElementById('villageSelect').value,
                // Also store the names for display purposes
                province_name: document.getElementById('provinceSelect').selectedOptions[0]?.getAttribute('data-name') || '',
                regency_name: document.getElementById('regencySelect').selectedOptions[0]?.getAttribute('data-name') || '',
                district_name: document.getElementById('districtSelect').selectedOptions[0]?.getAttribute('data-name') || '',
                village_name: document.getElementById('villageSelect').selectedOptions[0]?.getAttribute('data-name') || '',
                postal_code: formData.get('postalCode') || '',
                address_line: formData.get('address') || '',
                address_type: 'home',
                is_primary: 1
            };
            
            // Collect identity data
            const identityData = [];
            const identityTypes = formData.getAll('identityType[]');
            const identityNumbers = formData.getAll('identityNumber[]');
            const identityFiles = formData.getAll('identityFile[]');
            
            for (let i = 0; i < identityTypes.length; i++) {
                if (identityTypes[i] && identityNumbers[i]) {
                    identityData.push({
                        type: identityTypes[i],
                        number: identityNumbers[i],
                        file: identityFiles[i] || null
                    });
                }
            }
            
            // Prepare member data
            const memberData = {
                member_type_id: formData.get('memberType'),
                title: formData.get('title'),
                full_name: formData.get('fullName'),
                place_of_birth: formData.get('placeOfBirth'),
                date_of_birth: formData.get('dateOfBirth'),
                gender: formData.get('gender'),
                phone_number: formData.get('phoneNumber'),
                mobile_number: formData.get('mobileNumber'),
                email: formData.get('email'),
                occupation: formData.get('occupation'),
                company_name: formData.get('companyName'),
                monthly_income: formData.get('monthlyIncome'),
                marital_status: formData.get('maritalStatus'),
                addresses: [addressData], // Store address with IDs
                identities: identityData
            };
            
            // Send to API
            const response = await fetch('/api/multiple-identities.php?action=create_member&token=' + this.getAuthToken(), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(memberData)
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.hideModalLoading('addMember');
                this.closeModal('addMember');
                this.showNotification('Anggota berhasil ditambahkan! No. Anggota: ' + result.data.member_number, 'success');
                this.resetAddMemberForm();
                
                // Refresh table if exists
                if (window.enhancedUI) {
                    window.enhancedUI.refreshTable('recentMembersTable');
                }
            } else {
                this.hideModalLoading('addMember');
                this.showNotification('Gagal menambah anggota: ' + result.error, 'error');
            }
            
        } catch (error) {
            console.error('Error saving member:', error);
            this.hideModalLoading('addMember');
            this.showNotification('Terjadi kesalahan saat menambah anggota', 'error');
        }
    }

    updateMember() {
        const $form = $('#editMemberForm');
        
        if (!$form[0].checkValidity()) {
            $form.addClass('was-validated');
            return;
        }
        
        this.showModalLoading('editMember');
        
        setTimeout(() => {
            this.hideModalLoading('editMember');
            this.closeModal('editMember');
            this.showNotification('Data anggota berhasil diupdate!', 'success');
        }, 2000);
    }

    confirmDelete() {
        this.showModalLoading('deleteConfirm');
        
        setTimeout(() => {
            this.hideModalLoading('deleteConfirm');
            this.closeModal('deleteConfirm');
            this.showNotification('Data berhasil dihapus!', 'success');
        }, 1500);
    }

    async submitLoanApplication() {
        const $form = $('#loanApplicationForm');
        
        if (!$form[0].checkValidity()) {
            $form.addClass('was-validated');
            return;
        }
        
        this.showModalLoading('loanApplication');
        
        try {
            // Collect form data including address IDs
            const formData = new FormData($form[0]);
            
            // Add address data with IDs from alamat_db
            const addressData = {
                province_id: document.getElementById('borrowerProvinceSelect').value,
                regency_id: document.getElementById('borrowerRegencySelect').value,
                district_id: document.getElementById('borrowerDistrictSelect').value,
                village_id: document.getElementById('borrowerVillageSelect').value,
                // Also store the names for display purposes
                province_name: document.getElementById('borrowerProvinceSelect').selectedOptions[0]?.getAttribute('data-name') || '',
                regency_name: document.getElementById('borrowerRegencySelect').selectedOptions[0]?.getAttribute('data-name') || '',
                district_name: document.getElementById('borrowerDistrictSelect').selectedOptions[0]?.getAttribute('data-name') || '',
                village_name: document.getElementById('borrowerVillageSelect').selectedOptions[0]?.getAttribute('data-name') || '',
                postal_code: formData.get('borrowerPostalCode') || '',
                address_line: formData.get('borrowerAddress') || ''
            };
            
            // Collect identity data
            const identityData = [];
            const identityTypes = formData.getAll('borrowerIdentityType[]');
            const identityNumbers = formData.getAll('borrowerIdentityNumber[]');
            const identityFiles = formData.getAll('borrowerIdentityFile[]');
            
            for (let i = 0; i < identityTypes.length; i++) {
                if (identityTypes[i] && identityNumbers[i]) {
                    identityData.push({
                        type: identityTypes[i],
                        number: identityNumbers[i],
                        file: identityFiles[i] || null
                    });
                }
            }
            
            // Prepare loan application data
            const loanData = {
                member_id: formData.get('memberId'),
                amount: formData.get('amount'),
                tenure: formData.get('tenure'),
                purpose: formData.get('purpose'),
                collateral_type: formData.get('collateralType'),
                collateral_value: formData.get('collateralValue'),
                borrower_name: formData.get('borrowerName'),
                borrower_phone: formData.get('borrowerPhone'),
                borrower_email: formData.get('borrowerEmail'),
                addresses: [addressData], // Store address with IDs
                identities: identityData
            };
            
            // Send to API
            const response = await fetch('/api/multiple-identities.php?action=create_loan_applicant&token=' + this.getAuthToken(), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(loanData)
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.hideModalLoading('loanApplication');
                this.closeModal('loanApplication');
                this.showNotification('Pengajuan pinjaman berhasil dikirim! No. Aplikasi: ' + result.data.application_number, 'success');
                this.resetLoanApplicationForm();
            } else {
                this.hideModalLoading('loanApplication');
                this.showNotification('Gagal mengirim pengajuan: ' + result.error, 'error');
            }
            
        } catch (error) {
            console.error('Error submitting loan application:', error);
            this.hideModalLoading('loanApplication');
            this.showNotification('Terjadi kesalahan saat mengirim pengajuan', 'error');
        }
    }

    generateReport() {
        const $form = $('#reportForm');
        
        if (!$form[0].checkValidity()) {
            $form.addClass('was-validated');
            return;
        }
        
        this.showModalLoading('reportGenerator');
        
        setTimeout(() => {
            this.hideModalLoading('reportGenerator');
            this.closeModal('reportGenerator');
            this.showNotification('Laporan sedang dibuat, akan diunduh otomatis...', 'info');
        }, 2000);
    }

    previewReport() {
        // Show preview in new modal or tab
        this.showNotification('Preview laporan akan segera tersedia...', 'info');
    }

    // Utility Methods
    calculateLoanSimulation() {
        const amount = parseFloat($('#loanApplicationForm input[name="amount"]').val()) || 0;
        const tenure = parseInt($('#loanApplicationForm input[name="tenure"]').val()) || 0;
        const interestRate = 12; // Default 12% per year
        
        if (amount > 0 && tenure > 0) {
            const monthlyRate = interestRate / 100 / 12;
            const monthlyInterest = amount * monthlyRate;
            const totalInterest = monthlyInterest * tenure;
            const totalPayment = amount + totalInterest;
            const installment = totalPayment / tenure;
            
            $('#simAmount').text(this.formatCurrency(amount));
            $('#simInterest').text(this.formatCurrency(monthlyInterest));
            $('#simTotal').text(this.formatCurrency(totalPayment));
            $('#simInstallment').text(this.formatCurrency(installment));
        }
    }

    formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount);
    }

    showModalLoading(modalName) {
        const modalId = this.modals.get(modalName);
        const $modal = $('#' + modalId);
        const $content = $modal.find('.modal-content');
        
        const loadingHtml = `
            <div class="modal-loading-overlay position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-white bg-opacity-90">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Processing...</p>
                </div>
            </div>
        `;
        
        $content.css('position', 'relative').append(loadingHtml);
    }

    hideModalLoading(modalName) {
        const modalId = this.modals.get(modalName);
        const $modal = $('#' + modalId);
        $modal.find('.modal-loading-overlay').fadeOut(300, function() {
            $(this).remove();
        });
    }

    showNotification(message, type = 'info') {
        if (window.enhancedUI) {
            window.enhancedUI.showNotification(message, type);
        } else {
            alert(message);
        }
    }

    // File upload handling
    setupFileUpload() {
        $('.file-upload-area').each(function() {
            const $uploadArea = $(this);
            const target = $uploadArea.data('target');
            const $input = $(`input[name="${target}"]`);
            
            // Click to browse
            $uploadArea.on('click', function() {
                $input.click();
            });
            
            // Drag and drop
            $uploadArea.on('dragover', function(e) {
                e.preventDefault();
                $(this).addClass('dragover');
            });
            
            $uploadArea.on('dragleave', function(e) {
                e.preventDefault();
                $(this).removeClass('dragover');
            });
            
            $uploadArea.on('drop', function(e) {
                e.preventDefault();
                $(this).removeClass('dragover');
                
                const files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    $input[0].files = files;
                    this.showFilePreview($uploadArea, files[0]);
                }
            }.bind(this));
            
            // File change
            $input.on('change', function() {
                if (this.files.length > 0) {
                    this.showFilePreview($uploadArea, this.files[0]);
                }
            }.bind(this));
            
            // Remove file
            $uploadArea.find('.remove-file').on('click', function() {
                $input.val('');
                $uploadArea.find('.file-preview').addClass('d-none');
                $uploadArea.find('.text-center').removeClass('d-none');
            });
        }.bind(this));
    }

    showFilePreview($uploadArea, file) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const $preview = $uploadArea.find('.file-preview');
            const $img = $preview.find('img');
            
            $img.attr('src', e.target.result);
            $preview.removeClass('d-none');
            $uploadArea.find('.text-center').addClass('d-none');
        };
        
        reader.readAsDataURL(file);
    }

    // Password visibility toggle
    togglePasswordVisibility(input) {
        const $input = $(input);
        const type = $input.attr('type');
        const $button = $input.next('button');
        const $icon = $button.find('i');
        
        if (type === 'password') {
            $input.attr('type', 'text');
            $icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            $input.attr('type', 'password');
            $icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    }

    // Reset form
    resetAddMemberForm() {
        const $form = $('#addMemberForm');
        $form[0].reset();
        $form.removeClass('was-validated');
        $form.find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
        $form.find('.valid-feedback, .invalid-feedback').remove();
    }

    // Identity Field Management
    addIdentityField() {
        const identityFields = document.getElementById('identityFields');
        const fieldCount = identityFields.querySelectorAll('.identity-field').length;
        
        const newField = document.createElement('div');
        newField.className = 'identity-field mb-3';
        newField.innerHTML = `
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Jenis Identitas *</label>
                    <select class="form-select identity-type" name="identityType[]" required>
                        <option value="">Pilih Jenis</option>
                        <option value="KTP">KTP</option>
                        <option value="SIM">SIM</option>
                        <option value="PASSPORT">Passport</option>
                        <option value="NPWP">NPWP</option>
                        <option value="BPJS">BPJS</option>
                        <option value="KK">Kartu Keluarga</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nomor Identitas *</label>
                    <input type="text" class="form-control identity-number" name="identityNumber[]" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Upload Scan</label>
                    <input type="file" class="form-control identity-file" name="identityFile[]" accept="image/*">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="this.removeIdentityField(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        
        identityFields.appendChild(newField);
        this.updateRemoveButtons();
    }

    removeIdentityField(button) {
        const field = button.closest('.identity-field');
        field.remove();
        this.updateRemoveButtons();
    }

    updateRemoveButtons() {
        const fields = document.querySelectorAll('.identity-field');
        fields.forEach((field, index) => {
            const removeBtn = field.querySelector('.btn-outline-danger');
            if (removeBtn) {
                removeBtn.style.display = fields.length > 1 ? 'block' : 'none';
            }
        });
    }

    // Address Database Functions
    async loadProvinces() {
        try {
            // Fetch provinces from alamat_db API
            const response = await fetch('/api/multiple-identities.php?action=get_provinces&token=' + this.getAuthToken());
            const data = await response.json();
            
            if (data.success) {
                const provinceSelect = document.getElementById('provinceSelect');
                provinceSelect.innerHTML = '<option value="">Pilih Provinsi</option>';
                
                data.data.forEach(province => {
                    const option = document.createElement('option');
                    option.value = province.id; // Store ID
                    option.textContent = province.name; // Display name
                    option.setAttribute('data-name', province.name); // Store name for reference
                    provinceSelect.appendChild(option);
                });

                provinceSelect.addEventListener('change', () => this.loadRegencies(provinceSelect.value));
            } else {
                console.error('Failed to load provinces:', data.error);
                this.showNotification('Gagal memuat data provinsi', 'error');
            }
        } catch (error) {
            console.error('Error loading provinces:', error);
            this.showNotification('Terjadi kesalahan saat memuat provinsi', 'error');
        }
    }

    async loadRegencies(provinceId) {
        if (!provinceId) return;

        try {
            // Fetch regencies from alamat_db API
            const response = await fetch(`/api/multiple-identities.php?action=get_regencies&province_id=${provinceId}&token=` + this.getAuthToken());
            const data = await response.json();
            
            if (data.success) {
                const regencySelect = document.getElementById('regencySelect');
                regencySelect.innerHTML = '<option value="">Pilih Kabupaten/Kota</option>';
                regencySelect.disabled = false;
                
                data.data.forEach(regency => {
                    const option = document.createElement('option');
                    option.value = regency.id; // Store ID
                    option.textContent = regency.name; // Display name
                    option.setAttribute('data-name', regency.name); // Store name for reference
                    regencySelect.appendChild(option);
                });

                regencySelect.addEventListener('change', () => this.loadDistricts(regencySelect.value));
            } else {
                console.error('Failed to load regencies:', data.error);
                this.showNotification('Gagal memuat data kabupaten/kota', 'error');
            }
        } catch (error) {
            console.error('Error loading regencies:', error);
            this.showNotification('Terjadi kesalahan saat memuat kabupaten/kota', 'error');
        }
    }

    async loadDistricts(regencyId) {
        if (!regencyId) return;

        try {
            // Fetch districts from alamat_db API
            const response = await fetch(`/api/multiple-identities.php?action=get_districts&regency_id=${regencyId}&token=` + this.getAuthToken());
            const data = await response.json();
            
            if (data.success) {
                const districtSelect = document.getElementById('districtSelect');
                districtSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
                districtSelect.disabled = false;
                
                data.data.forEach(district => {
                    const option = document.createElement('option');
                    option.value = district.id; // Store ID
                    option.textContent = district.name; // Display name
                    option.setAttribute('data-name', district.name); // Store name for reference
                    districtSelect.appendChild(option);
                });

                districtSelect.addEventListener('change', () => this.loadVillages(districtSelect.value));
            } else {
                console.error('Failed to load districts:', data.error);
                this.showNotification('Gagal memuat data kecamatan', 'error');
            }
        } catch (error) {
            console.error('Error loading districts:', error);
            this.showNotification('Terjadi kesalahan saat memuat kecamatan', 'error');
        }
    }

    async loadVillages(districtId) {
        if (!districtId) return;

        try {
            // Fetch villages from alamat_db API
            const response = await fetch(`/api/multiple-identities.php?action=get_villages&district_id=${districtId}&token=` + this.getAuthToken());
            const data = await response.json();
            
            if (data.success) {
                const villageSelect = document.getElementById('villageSelect');
                villageSelect.innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
                villageSelect.disabled = false;
                
                data.data.forEach(village => {
                    const option = document.createElement('option');
                    option.value = village.id; // Store ID
                    option.textContent = village.name; // Display name
                    option.setAttribute('data-name', village.name); // Store name for reference
                    villageSelect.appendChild(option);
                });
            } else {
                console.error('Failed to load villages:', data.error);
                this.showNotification('Gagal memuat data desa/kelurahan', 'error');
            }
        } catch (error) {
            console.error('Error loading villages:', error);
            this.showNotification('Terjadi kesalahan saat memuat desa/kelurahan', 'error');
        }
    }

    // Location Services
    getCurrentLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    document.querySelector('input[name="latitude"]').value = lat.toFixed(6);
                    document.querySelector('input[name="longitude"]').value = lng.toFixed(6);
                    
                    this.showNotification('Lokasi berhasil didapatkan!', 'success');
                },
                (error) => {
                    this.showNotification('Gagal mendapatkan lokasi: ' + error.message, 'error');
                }
            );
        } else {
            this.showNotification('Browser tidak mendukung geolocation', 'error');
        }
    }

    // Password visibility toggle
    togglePasswordVisibility(button) {
        const input = button.previousElementSibling;
        const icon = button.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    // Borrower Identity Field Management
    addBorrowerIdentityField() {
        const identityFields = document.getElementById('borrowerIdentityFields');
        const fieldCount = identityFields.querySelectorAll('.identity-field').length;
        
        const newField = document.createElement('div');
        newField.className = 'identity-field mb-3';
        newField.innerHTML = `
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Jenis Identitas *</label>
                    <select class="form-select identity-type" name="borrowerIdentityType[]" required>
                        <option value="">Pilih Jenis</option>
                        <option value="KTP">KTP</option>
                        <option value="SIM">SIM</option>
                        <option value="PASSPORT">Passport</option>
                        <option value="NPWP">NPWP</option>
                        <option value="BPJS">BPJS</option>
                        <option value="KK">Kartu Keluarga</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nomor Identitas *</label>
                    <input type="text" class="form-control identity-number" name="borrowerIdentityNumber[]" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Upload Scan</label>
                    <input type="file" class="form-control identity-file" name="borrowerIdentityFile[]" accept="image/*">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="window.modalManager.removeBorrowerIdentityField(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        
        identityFields.appendChild(newField);
        this.updateBorrowerRemoveButtons();
    }

    removeBorrowerIdentityField(button) {
        const field = button.closest('.identity-field');
        field.remove();
        this.updateBorrowerRemoveButtons();
    }

    updateBorrowerRemoveButtons() {
        const fields = document.querySelectorAll('#borrowerIdentityFields .identity-field');
        fields.forEach((field, index) => {
            const removeBtn = field.querySelector('.btn-outline-danger');
            if (removeBtn) {
                removeBtn.style.display = fields.length > 1 ? 'block' : 'none';
            }
        });
    }

    // Helper function to get auth token
    getAuthToken() {
        // Try to get from localStorage first
        let token = localStorage.getItem('auth_token');
        
        // If not in localStorage, try from session storage
        if (!token) {
            token = sessionStorage.getItem('auth_token');
        }
        
        // If still not found, try to get from meta tag (for demo purposes)
        if (!token) {
            const metaTag = document.querySelector('meta[name="auth-token"]');
            token = metaTag ? metaTag.getAttribute('content') : 'demo-token';
        }
        
        return token || 'demo-token';
    }

    // Borrower Address Database Functions
    async loadBorrowerProvinces() {
        try {
            // Fetch provinces from alamat_db API
            const response = await fetch('/api/multiple-identities.php?action=get_provinces&token=' + this.getAuthToken());
            const data = await response.json();
            
            if (data.success) {
                const provinceSelect = document.getElementById('borrowerProvinceSelect');
                provinceSelect.innerHTML = '<option value="">Pilih Provinsi</option>';
                
                data.data.forEach(province => {
                    const option = document.createElement('option');
                    option.value = province.id; // Store ID
                    option.textContent = province.name; // Display name
                    option.setAttribute('data-name', province.name); // Store name for reference
                    provinceSelect.appendChild(option);
                });

                provinceSelect.addEventListener('change', () => this.loadBorrowerRegencies(provinceSelect.value));
            } else {
                console.error('Failed to load provinces:', data.error);
                this.showNotification('Gagal memuat data provinsi', 'error');
            }
        } catch (error) {
            console.error('Error loading provinces:', error);
            this.showNotification('Terjadi kesalahan saat memuat provinsi', 'error');
        }
    }

    async loadBorrowerRegencies(provinceId) {
        if (!provinceId) return;

        try {
            // Fetch regencies from alamat_db API
            const response = await fetch(`/api/multiple-identities.php?action=get_regencies&province_id=${provinceId}&token=` + this.getAuthToken());
            const data = await response.json();
            
            if (data.success) {
                const regencySelect = document.getElementById('borrowerRegencySelect');
                regencySelect.innerHTML = '<option value="">Pilih Kabupaten/Kota</option>';
                regencySelect.disabled = false;
                
                data.data.forEach(regency => {
                    const option = document.createElement('option');
                    option.value = regency.id; // Store ID
                    option.textContent = regency.name; // Display name
                    option.setAttribute('data-name', regency.name); // Store name for reference
                    regencySelect.appendChild(option);
                });

                regencySelect.addEventListener('change', () => this.loadBorrowerDistricts(regencySelect.value));
            } else {
                console.error('Failed to load regencies:', data.error);
                this.showNotification('Gagal memuat data kabupaten/kota', 'error');
            }
        } catch (error) {
            console.error('Error loading regencies:', error);
            this.showNotification('Terjadi kesalahan saat memuat kabupaten/kota', 'error');
        }
    }

    async loadBorrowerDistricts(regencyId) {
        if (!regencyId) return;

        try {
            // Fetch districts from alamat_db API
            const response = await fetch(`/api/multiple-identities.php?action=get_districts&regency_id=${regencyId}&token=` + this.getAuthToken());
            const data = await response.json();
            
            if (data.success) {
                const districtSelect = document.getElementById('borrowerDistrictSelect');
                districtSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
                districtSelect.disabled = false;
                
                data.data.forEach(district => {
                    const option = document.createElement('option');
                    option.value = district.id; // Store ID
                    option.textContent = district.name; // Display name
                    option.setAttribute('data-name', district.name); // Store name for reference
                    districtSelect.appendChild(option);
                });

                districtSelect.addEventListener('change', () => this.loadBorrowerVillages(districtSelect.value));
            } else {
                console.error('Failed to load districts:', data.error);
                this.showNotification('Gagal memuat data kecamatan', 'error');
            }
        } catch (error) {
            console.error('Error loading districts:', error);
            this.showNotification('Terjadi kesalahan saat memuat kecamatan', 'error');
        }
    }

    async loadBorrowerVillages(districtId) {
        if (!districtId) return;

        try {
            // Fetch villages from alamat_db API
            const response = await fetch(`/api/multiple-identities.php?action=get_villages&district_id=${districtId}&token=` + this.getAuthToken());
            const data = await response.json();
            
            if (data.success) {
                const villageSelect = document.getElementById('borrowerVillageSelect');
                villageSelect.innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
                villageSelect.disabled = false;
                
                data.data.forEach(village => {
                    const option = document.createElement('option');
                    option.value = village.id; // Store ID
                    option.textContent = village.name; // Display name
                    option.setAttribute('data-name', village.name); // Store name for reference
                    villageSelect.appendChild(option);
                });
            } else {
                console.error('Failed to load villages:', data.error);
                this.showNotification('Gagal memuat data desa/kelurahan', 'error');
            }
        } catch (error) {
            console.error('Error loading villages:', error);
            this.showNotification('Terjadi kesalahan saat memuat desa/kelurahan', 'error');
        }
    }

    // Loan Application Form Management
    resetLoanApplicationForm() {
        const $form = $('#loanApplicationForm');
        $form[0].reset();
        $form.removeClass('was-validated');
        $form.find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
        $form.find('.valid-feedback, .invalid-feedback').remove();
        
        // Reset simulation
        $('#simAmount, #simInterest, #simTotal, #simInstallment').text('Rp 0');
        
        // Hide collateral details
        $('#collateralDetails').hide();
    }

    // Enhanced loan validation
    validateLoanApplicationForm() {
        const $form = $('#loanApplicationForm');
        let isValid = true;
        
        // Check borrower identity
        const identityTypes = $form.find('select[name="borrowerIdentityType[]"]');
        const identityNumbers = $form.find('input[name="borrowerIdentityNumber[]"]');
        
        for (let i = 0; i < identityTypes.length; i++) {
            if (identityTypes[i].value && !identityNumbers[i].value) {
                identityNumbers[i].setCustomValidity('Nomor identitas harus diisi');
                isValid = false;
            } else {
                identityNumbers[i].setCustomValidity('');
            }
        }
        
        // Check loan amount vs income
        const amount = parseFloat($form.find('input[name="amount"]').val()) || 0;
        const income = parseFloat($form.find('input[name="borrowerIncome"]').val()) || 0;
        
        if (income > 0 && amount > income * 12) {
            $form.find('input[name="amount"]')[0].setCustomValidity('Jumlah pinjaman tidak boleh lebih dari 12x penghasilan');
            isValid = false;
        } else {
            $form.find('input[name="amount"]')[0].setCustomValidity('');
        }
        
        return isValid;
    }
}

// Initialize Modal Manager when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (typeof $ !== 'undefined') {
        window.modalManager = new ModalManager();
    } else {
        console.error('jQuery is required for Modal Manager');
    }
});
