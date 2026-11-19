const API_URL = '/buku-data';
const BOOK_URL = '/buku';

document.addEventListener('DOMContentLoaded', () => {
    fetchBuku();

    const bukuForm = document.getElementById('buku-form');
    if (bukuForm) {
        bukuForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const id = document.getElementById('buku_id').value;
            const payload = {
                kategori_id: document.getElementById('kategori_id').value,
                judul: document.getElementById('judul').value,
                penulis: document.getElementById('penulis').value,
                tahun_terbit: document.getElementById('tahun_terbit').value
            };
            const isEdit = id !== '';
            const url = isEdit ? `${BOOK_URL}/${encodeURIComponent(id)}` : BOOK_URL;
            const csrfToken = bukuForm.querySelector('input[name="_token"]').value;

            try {
                const response = await fetch(url, {
                    method: isEdit ? 'PUT' : 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(payload)
                });
                const data = await response.json();

                if (!response.ok) {
                    const validationErrors = data.errors
                        ? Object.values(data.errors).flat().join(' ')
                        : '';
                    throw new Error(validationErrors || data.message || 'Gagal menyimpan data.');
                }

                showAlert(data.message || (isEdit
                    ? 'Data buku berhasil diperbarui!'
                    : 'Buku baru berhasil ditambahkan!'), 'success');
                resetForm();
                fetchBuku();
            } catch (error) {
                showAlert(error.message, 'danger');
            }
        });
    }
});

async function fetchBuku() {
    try {
        const response = await fetch(API_URL, {
            headers: { 'Accept': 'application/json' }
        });

        if (!response.ok) {
            throw new Error('Gagal mengambil data dari API.');
        }

        const responseData = await response.json();
        const books = responseData.data || responseData;
        const tbody = document.getElementById('buku-table-body');
        tbody.replaceChildren();

        if (books.length === 0) {
            const row = document.createElement('tr');
            const cell = document.createElement('td');
            cell.colSpan = 5;
            cell.textContent = 'Tidak ada data buku.';
            row.append(cell);
            tbody.append(row);
            return;
        }

        const permissions = window.bukuPermissions || {};

        books.forEach((book) => {
            const row = document.createElement('tr');
            const categoryCell = document.createElement('td');
            const titleCell = document.createElement('td');
            const authorCell = document.createElement('td');
            const yearCell = document.createElement('td');
            const actionCell = document.createElement('td');

            categoryCell.textContent = book.kategori || '-';
            // titleCell.innerHTML = book.judul;
            titleCell.textContent = book.judul;
            authorCell.textContent = book.penulis;
            yearCell.textContent = book.tahun_terbit;

            if (permissions.edit) {
                const template = document.getElementById('buku-edit-button-template');
                if (template) {
                    const editButton = template.content.firstElementChild.cloneNode(true);
                    editButton.addEventListener('click', () => editBuku(book));
                    actionCell.append(editButton);
                }
            }

            if (permissions.delete) {
                const template = document.getElementById('buku-delete-form-template');
                if (template) {
                    const form = template.content.firstElementChild.cloneNode(true);
                    form.action = `${BOOK_URL}/${encodeURIComponent(book.id)}`;
                    form.addEventListener('submit', (event) => deleteBuku(event, form));
                    actionCell.append(form);
                }
            }

            if (!actionCell.hasChildNodes()) {
                actionCell.textContent = '-';
            }

            row.append(categoryCell, titleCell, authorCell, yearCell, actionCell);
            tbody.append(row);
        });
    } catch (error) {
        showAlert(error.message, 'danger');
    }
}

function editBuku(book) {
    const form = document.getElementById('buku-form');
    if (!form) return;

    document.getElementById('buku_id').value = book.id;
    document.getElementById('kategori_id').value = book.kategori_id;
    document.getElementById('judul').value = book.judul;
    document.getElementById('penulis').value = book.penulis;
    document.getElementById('tahun_terbit').value = book.tahun_terbit;

    document.getElementById('form-title').textContent = 'Ubah Data Buku';
    document.getElementById('btn-cancel').style.display = 'inline';
}

function resetForm() {
    const form = document.getElementById('buku-form');
    if (!form) return;

    form.reset();
    document.getElementById('buku_id').value = '';
    document.getElementById('form-title').textContent = 'Tambah Buku Baru';
    document.getElementById('btn-cancel').style.display = 'none';
}

async function deleteBuku(event, form) {
    event.preventDefault();
    if (!confirm('Apakah Anda yakin ingin menghapus buku ini?')) return;

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value
            },
            body: new FormData(form)
        });

        const data = await response.json();
        if (!response.ok) {
            throw new Error(data.message || 'Gagal menghapus data buku.');
        }

        showAlert(data.message || 'Buku berhasil dihapus.', 'success');
        fetchBuku();
    } catch (error) {
        showAlert(error.message, 'danger');
    }
}

function showAlert(message, type) {
    const container = document.getElementById('alert-container');
    if (!container) return;

    const alert = document.createElement('div');
    alert.style.cssText = type === 'success'
        ? 'padding:10px;background-color:#d4edda;color:#155724;margin-bottom:15px;border-radius:4px;'
        : 'padding:10px;background-color:#f8d7da;color:#721c24;margin-bottom:15px;border-radius:4px;';
    alert.textContent = message;
    container.replaceChildren(alert);

    setTimeout(() => container.replaceChildren(), 4000);
}
