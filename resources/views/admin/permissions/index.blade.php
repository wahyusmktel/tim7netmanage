@extends('layouts.admin')

@section('title', 'Manajemen Permission')

@section('content')
    <div class="page-header">
        <h4 class="page-title">Manajemen Permission</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}"><i class="flaticon-home"></i></a>
            </li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item">Permission</li>
        </ul>
    </div>

    <div class="page-category">
        <div class="card">
            <div class="card-header">
                <div class="card-head-row d-flex justify-content-between align-items-center">
                    <h4 class="card-title">Data Permission</h4>
                    <button class="btn btn-primary btn-sm" onclick="showCreateModal()">
                        <i class="fas fa-plus"></i> Tambah Permission
                    </button>
                </div>
                <form method="GET" class="mt-2">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control form-control-sm"
                            placeholder="Cari nama permission..." value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-sm btn-info"><i class="fas fa-search"></i> Cari</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="text-center">
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Guard</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($permissions as $permission)
                                <tr>
                                    <td class="text-center">
                                        {{ $loop->iteration + ($permissions->currentPage() - 1) * $permissions->perPage() }}
                                    </td>
                                    <td>{{ e($permission->name) }}</td>
                                    <td class="text-center">{{ e($permission->guard_name) }}</td>
                                    <td class="text-center">
                                        <button onclick="editPermission('{{ $permission->id }}')"
                                            class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</button>
                                        <button onclick="deletePermission('{{ $permission->id }}')"
                                            class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Hapus</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">Data tidak ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer">
                    {{ $permissions->links('vendor.pagination.bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Create/Edit --}}
    <div class="modal fade" id="permissionModal" tabindex="-1" role="dialog" aria-labelledby="permissionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="permissionForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="permissionModalLabel">Tambah Permission</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" id="method" name="_method">
                        <div class="form-group">
                            <label>Nama Permission</label>
                            <input type="text" name="name" id="name" class="form-control"
                                placeholder="Nama Permission" required>
                        </div>
                        <div class="form-group">
                            <label>Guard Name</label>
                            <select name="guard_name" id="guard_name" class="form-control" required>
                                <option value="web">Web</option>
                                <option value="api">API</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Assign to Role</label>
                            <select name="roles[]" id="roles" class="form-control" multiple>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ ucwords(str_replace('_', ' ', $role->name)) }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Bisa pilih lebih dari satu role.</small>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let urlCreate = "{{ route('admin.permissions.store') }}";
        let urlUpdate = "{{ url('admin/permissions') }}";

        function showCreateModal() {
            $('#permissionModalLabel').text('Tambah Permission');
            $('#permissionForm')[0].reset();
            $('#method').val('');
            $('#permissionModal').modal('show');
        }

        function editPermission(id) {
            $.get(urlUpdate + '/' + id + '/edit', function(response) {
                $('#permissionModalLabel').text('Edit Permission');
                $('#name').val(response.permission.name);
                $('#guard_name').val(response.permission.guard_name);
                $('#roles').val(response.assigned_roles).trigger('change');
                $('#method').val('PUT');
                $('#permissionForm').attr('action', urlUpdate + '/' + id);
                $('#permissionModal').modal('show');
            });
        }

        function deletePermission(id) {
            Swal.fire({
                title: 'Yakin Hapus?',
                text: "Data permission akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: urlUpdate + '/' + id,
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Berhasil', response.message, 'success').then(() => location
                                    .reload());
                            } else {
                                Swal.fire('Gagal', response.message, 'error');
                            }
                        }
                    });
                }
            });
        }

        $('#permissionForm').submit(function(e) {
            e.preventDefault();
            let formData = $(this).serialize();
            let method = $('#method').val() ? 'POST' : 'POST';
            let actionUrl = $('#method').val() ? $('#permissionForm').attr('action') : urlCreate;

            $.ajax({
                url: actionUrl,
                method: method,
                data: formData,
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Berhasil', response.message, 'success').then(() => location
                            .reload());
                    } else {
                        Swal.fire('Gagal', response.message, 'error');
                    }
                }
            });
        });

        $(document).ready(function() {
            $('#roles').select2({
                width: '100%',
                theme: 'bootstrap'
            });
        });
    </script>
@endpush
