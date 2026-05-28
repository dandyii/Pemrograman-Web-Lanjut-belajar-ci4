<!-- Add Modal Begin -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Tambah Data</h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                </button>
            </div>

            <form action="" method="post" enctype="multipart/form-data">

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text"
                               class="form-control"
                               name="nama"
                               placeholder="Nama Barang">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Harga</label>
                        <input type="text"
                               class="form-control"
                               name="harga"
                               placeholder="Harga Barang">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jumlah</label>
                        <input type="text"
                               class="form-control"
                               name="jumlah"
                               placeholder="Jumlah Barang">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Foto</label>
                        <input type="file"
                               class="form-control"
                               name="foto">
                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        Close
                    </button>

                    <button type="submit"
                            class="btn btn-primary">
                        Simpan
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>
<!-- Add Modal End -->