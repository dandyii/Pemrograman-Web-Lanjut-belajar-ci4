<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<?php
if (session()->getFlashData('success')) {
?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= session()->getFlashData('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php
}
?> 
<?php echo form_open('keranjang/edit') ?>
<!-- Table with stripped rows -->
<table class="table datatable">
    <thead>
        <tr>
            <th scope="col">Nama</th>
            <th scope="col">Foto</th>
            <th scope="col">Harga</th>
            <th scope="col">Jumlah</th>
            <th scope="col">Subtotal</th>
            <th scope="col">Aksi</th> 
        </tr>
    </thead>
    <tbody>
        <?php
        $i = 1;
        if (!empty($items)) :
            foreach ($items as $index => $item) :
        ?>
                <tr>
                    <td><?php echo $item['name'] ?></td>
                    <td><img src="<?php echo base_url() . "img/" . $item['options']['foto'] ?>" width="100px"></td>
                    <td><?php echo number_to_currency($item['price'], 'IDR') ?></td> 
                    <td><input type="number" min="1" name="qty<?php echo $i++ ?>" class="form-control qty-input" data-price="<?= $item['price'] ?>" value="<?php echo $item['qty'] ?>"></td>
                    <td class="item-subtotal"><?php echo number_to_currency($item['subtotal'], 'IDR') ?></td>
                    <td>
                        <a href="<?php echo base_url('keranjang/delete/' . $item['rowid'] . '') ?>" class="btn btn-danger"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
        <?php
            endforeach;
        endif;
        ?>
    </tbody>
</table>
<!-- End Table with stripped rows -->

<div class="alert alert-info">
    <?php echo "Total = " . number_to_currency($total, 'IDR') ?>
</div>

<button type="submit" class="btn btn-primary" name="action" value="update">Perbarui Keranjang</button>
 <a class="btn btn-warning" href="<?php echo base_url() ?>keranjang/clear">Kosongkan Keranjang</a>
 <?php if (!empty($items)) : ?>
    <button type="submit" class="btn btn-success" name="action" value="checkout">Selesai Belanja</button>
<?php endif; ?>
<?php echo form_close() ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const qtyInputs = document.querySelectorAll('.qty-input');
        const totalEl = document.querySelector('.alert-info');

        function formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(number);
        }

        qtyInputs.forEach(input => {
            input.addEventListener('input', function () {
                const price = parseInt(this.dataset.price, 10) || 0;
                const qty = parseInt(this.value, 10) || 0;
                const subtotalEl = this.closest('tr').querySelector('.item-subtotal');
                const subtotal = price * qty;
                subtotalEl.textContent = formatRupiah(subtotal);

                let grandTotal = 0;
                document.querySelectorAll('.item-subtotal').forEach(item => {
                    const value = item.textContent.replace(/[^0-9]/g, '');
                    grandTotal += parseInt(value || 0, 10);
                });

                if (totalEl) {
                    totalEl.textContent = 'Total = ' + formatRupiah(grandTotal);
                }
            });
        });
    });
</script> 
<?= $this->endSection() ?>