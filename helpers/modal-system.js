/**
 * Modal System untuk CRUD Operations
 * Menggunakan jQuery dan AJAX
 */

// Modal Templates
const modalTemplates = {
    // Member Modal
    member: {
        add: `
            <div class="modal fade" id="memberModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Tambah Anggota Baru</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="memberForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Nomor Anggota</label>
                                            <input type="text" class="form-control" name="member_number" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">NIK</label>
                                            <input type="text" class="form-control" name="nik" maxlength="16" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Nama Lengkap</label>
                                            <input type="text" class="form-control" name="name" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Tempat Lahir</label>
                                            <input type="text" class="form-control" name="birth_place">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Tanggal Lahir</label>
                                            <input type="date" class="form-control" name="birth_date">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Jenis Kelamin</label>
                                            <select class="form-control" name="gender" required>
                                                <option value="">Pilih</option>
                                                <option value="male">Laki-laki</option>
                                                <option value="female">Perempuan</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Alamat</label>
                                            <textarea class="form-control" name="address" rows="3" required></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Telepon</label>
                                            <input type="text" class="form-control" name="phone">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" name="email">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Pekerjaan</label>
                                            <input type="text" class="form-control" name="occupation">
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-primary" onclick="saveMember()">Simpan</button>
                        </div>
                    </div>
                </div>
            </div>
        `,
        edit: `
            <div class="modal fade" id="memberEditModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Anggota</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="memberEditForm">
                                <input type="hidden" name="id">
                                <!-- Same fields as add modal -->
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-primary" onclick="updateMember()">Update</button>
                        </div>
                    </div>
                </div>
            </div>
        `,
        view: `
            <div class="modal fade" id="memberViewModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Detail Anggota</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div id="memberDetails">
                                <!-- Member details will be loaded here -->
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        `
    },

    // Loan Modal
    loan: {
        add: `
            <div class="modal fade" id="loanModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Pengajuan Pinjaman Baru</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="loanForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Anggota</label>
                                            <select class="form-control" name="member_id" required>
                                                <option value="">Pilih Anggota</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Produk Pinjaman</label>
                                            <select class="form-control" name="product_id" required>
                                                <option value="">Pilih Produk</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Jumlah Pinjaman</label>
                                            <input type="number" class="form-control" name="amount" min="500000" max="50000000" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Jangka Waktu (bulan)</label>
                                            <input type="number" class="form-control" name="term_months" min="1" max="36" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Tujuan Pinjaman</label>
                                            <textarea class="form-control" name="purpose" rows="3" required></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Jaminan</label>
                                            <textarea class="form-control" name="collateral" rows="2"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-primary" onclick="saveLoan()">Ajukan</button>
                        </div>
                    </div>
                </div>
            </div>
        `,
        approve: `
            <div class="modal fade" id="loanApproveModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Setujui Pinjaman</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>Apakah Anda yakin ingin menyetujui pinjaman ini?</p>
                            <div id="loanApprovalDetails">
                                <!-- Loan details will be shown here -->
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-success" onclick="approveLoan()">Setujui</button>
                        </div>
                    </div>
                </div>
            </div>
        `
    },

    // Savings Modal
    savings: {
        deposit: `
            <div class="modal fade" id="depositModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Setoran Simpanan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="depositForm">
                                <div class="mb-3">
                                    <label class="form-label">Nomor Rekening</label>
                                    <input type="text" class="form-control" name="account_number" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Jumlah Setoran</label>
                                    <input type="number" class="form-control" name="amount" min="10000" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Keterangan</label>
                                    <textarea class="form-control" name="description" rows="2"></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-primary" onclick="processDeposit()">Proses</button>
                        </div>
                    </div>
                </div>
            </div>
        `
    }
};

// Modal Controller Class
class ModalController {
    constructor() {
        this.init();
    }

    init() {
        // Load jQuery if not loaded
        if (typeof jQuery === 'undefined') {
            this.loadjQuery();
        } else {
            this.setupModals();
        }
    }

    loadjQuery() {
        const script = document.createElement('script');
        script.src = 'https://code.jquery.com/jquery-3.6.0.min.js';
        script.onload = () => this.setupModals();
        document.head.appendChild(script);
    }

    setupModals() {
        // Add all modals to body
        Object.keys(modalTemplates).forEach(type => {
            Object.keys(modalTemplates[type]).forEach(action => {
                if (!document.getElementById(`${type}${action.charAt(0).toUpperCase() + action.slice(1)}Modal`)) {
                    $('body').append(modalTemplates[type][action]);
                }
            });
        });

        // Setup event handlers
        this.setupEventHandlers();
    }

    setupEventHandlers() {
        // Member modals
        $(document).on('click', '[data-action="add-member"]', () => this.showMemberAddModal());
        $(document).on('click', '[data-action="edit-member"]', (e) => this.showMemberEditModal(e));
        $(document).on('click', '[data-action="view-member"]', (e) => this.showMemberViewModal(e));

        // Loan modals
        $(document).on('click', '[data-action="add-loan"]', () => this.showLoanAddModal());
        $(document).on('click', '[data-action="approve-loan"]', (e) => this.showLoanApproveModal(e));

        // Savings modals
        $(document).on('click', '[data-action="deposit"]', (e) => this.showDepositModal(e));
    }

    // Member Modal Methods
    showMemberAddModal() {
        $('#memberModal').modal('show');
        this.loadMembersForSelect();
    }

    showMemberEditModal(e) {
        const memberId = $(e.target).data('id');
        this.loadMemberData(memberId);
        $('#memberEditModal').modal('show');
    }

    showMemberViewModal(e) {
        const memberId = $(e.target).data('id');
        this.loadMemberDetails(memberId);
        $('#memberViewModal').modal('show');
    }

    // Loan Modal Methods
    showLoanAddModal() {
        $('#loanModal').modal('show');
        this.loadMembersForSelect();
        this.loadLoanProducts();
    }

    showLoanApproveModal(e) {
        const loanId = $(e.target).data('id');
        this.loadLoanDetails(loanId);
        $('#loanApproveModal').modal('show');
    }

    // Savings Modal Methods
    showDepositModal(e) {
        const accountNumber = $(e.target).data('account');
        $('input[name="account_number"]').val(accountNumber);
        $('#depositModal').modal('show');
    }

    // Data Loading Methods
    loadMembersForSelect() {
        $.ajax({
            url: '/api/crud.php?path=members',
            method: 'GET',
            success: (data) => {
                const options = data.map(member =>
                    `<option value="${member.id}">${member.name} (${member.member_number})</option>`
                ).join('');
                $('select[name="member_id"]').html('<option value="">Pilih Anggota</option>' + options);
            }
        });
    }

    loadLoanProducts() {
        $.ajax({
            url: '/api/crud.php?path=loan-products',
            method: 'GET',
            success: (data) => {
                const options = data.map(product =>
                    `<option value="${product.id}" data-min="${product.minimum_amount}" data-max="${product.maximum_amount}" data-rate="${product.interest_rate_monthly}">
                        ${product.name} - ${product.interest_rate_monthly}%/bulan
                    </option>`
                ).join('');
                $('select[name="product_id"]').html('<option value="">Pilih Produk</option>' + options);
            }
        });
    }

    loadMemberData(memberId) {
        $.ajax({
            url: `/api/crud.php?path=members/${memberId}`,
            method: 'GET',
            success: (data) => {
                Object.keys(data).forEach(key => {
                    $(`#memberEditForm [name="${key}"]`).val(data[key]);
                });
            }
        });
    }

    loadMemberDetails(memberId) {
        $.ajax({
            url: `/api/crud.php?path=members/${memberId}`,
            method: 'GET',
            success: (data) => {
                const details = `
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nomor Anggota:</strong> ${data.member_number}</p>
                            <p><strong>NIK:</strong> ${formatNIK(data.nik)}</p>
                            <p><strong>Nama:</strong> ${data.name}</p>
                            <p><strong>Telepon:</strong> ${formatTelepon(data.phone)}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Email:</strong> ${data.email}</p>
                            <p><strong>Alamat:</strong> ${data.address}</p>
                            <p><strong>Status:</strong> ${data.is_active ? 'Aktif' : 'Tidak Aktif'}</p>
                            <p><strong>Skor Kredit:</strong> ${data.credit_score}</p>
                        </div>
                    </div>
                `;
                $('#memberDetails').html(details);
            }
        });
    }
}

// CRUD Functions
function deleteMember(memberId) {
    if (confirm('Apakah Anda yakin ingin menghapus anggota ini?')) {
        $.ajax({
            url: `/api/crud.php?path=members/${memberId}`,
            method: 'DELETE',
            success: (response) => {
                if (response.success) {
                    showAlert('success', 'Anggota berhasil dihapus');
                    location.reload();
                } else {
                    showAlert('error', response.message);
                }
            },
            error: () => {
                showAlert('error', 'Terjadi kesalahan saat menghapus anggota');
            }
        });
    }
}

function saveMember() {
    const formData = $('#memberForm').serialize();

    $.ajax({
        url: '/api/crud.php?path=members',
        method: 'POST',
        data: formData,
        success: (response) => {
            if (response.success) {
                $('#memberModal').modal('hide');
                showAlert('success', 'Anggota berhasil ditambahkan');
                location.reload(); // Or refresh table
            } else {
                showAlert('error', response.message);
            }
        },
        error: () => {
            showAlert('error', 'Terjadi kesalahan saat menyimpan data');
        }
    });
}

function updateMember() {
    const formData = $('#memberEditForm').serialize();
    const memberId = $('input[name="id"]').val();

    $.ajax({
        url: `/api/crud.php?path=members/${memberId}`,
        method: 'PUT',
        data: formData,
        success: (response) => {
            if (response.success) {
                $('#memberEditModal').modal('hide');
                showAlert('success', 'Data anggota berhasil diupdate');
                location.reload();
            } else {
                showAlert('error', response.message);
            }
        },
        error: () => {
            showAlert('error', 'Terjadi kesalahan saat mengupdate data');
        }
    });
}

function saveLoan() {
    const formData = $('#loanForm').serialize();

    $.ajax({
        url: '/api/loans',
        method: 'POST',
        data: formData,
        success: (response) => {
            if (response.success) {
                $('#loanModal').modal('hide');
                showAlert('success', 'Pengajuan pinjaman berhasil diajukan');
                location.reload();
            } else {
                showAlert('error', response.message);
            }
        },
        error: () => {
            showAlert('error', 'Terjadi kesalahan saat mengajukan pinjaman');
        }
    });
}

function approveLoan() {
    const loanId = $('#loanApproveModal').data('loan-id');

    $.ajax({
        url: `/api/loans/${loanId}/approve`,
        method: 'PUT',
        success: (response) => {
            if (response.success) {
                $('#loanApproveModal').modal('hide');
                showAlert('success', 'Pinjaman berhasil disetujui');
                location.reload();
            } else {
                showAlert('error', response.message);
            }
        },
        error: () => {
            showAlert('error', 'Terjadi kesalahan saat menyetujui pinjaman');
        }
    });
}

function processDeposit() {
    const formData = $('#depositForm').serialize();

    $.ajax({
        url: '/api/savings/deposit',
        method: 'POST',
        data: formData,
        success: (response) => {
            if (response.success) {
                $('#depositModal').modal('hide');
                showAlert('success', 'Setoran berhasil diproses');
                location.reload();
            } else {
                showAlert('error', response.message);
            }
        },
        error: () => {
            showAlert('error', 'Terjadi kesalahan saat memproses setoran');
        }
    });
}

// Utility Functions
function showAlert(type, message) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('.container').prepend(alertHtml);

    // Auto remove after 5 seconds
    setTimeout(() => {
        $('.alert').fadeOut();
    }, 5000);
}

// Initialize Modal Controller
$(document).ready(() => {
    new ModalController();
});
