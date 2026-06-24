<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Services\RajaOngkirService;

use App\Models\TransactionModel;
use App\Models\TransactionDetailModel;

class TransaksiController extends BaseController
{
    protected $cart;
    protected $transactionModel;
    protected $transactionDetailModel;

    public function __construct()
        {
            helper(['number', 'form']);
            helper('diskon_helper');
            $this->cart = service('cart');

            $this->transactionModel = new TransactionModel();
            $this->transactionDetailModel = new TransactionDetailModel(); 
        }
    
    public function index()
        {  
            $data = [
                'items' => $this->cart->contents(),
                'total' => $this->cart->total() 
            ];

            return view('v_keranjang', $data);
        }

    public function cart_add()
        {
            $productId = $this->request->getPost('id');
            $price = $this->request->getPost('harga');
            $name = $this->request->getPost('nama');
            $foto = $this->request->getPost('foto');

            $existingRowId = null;
            $existingQty = 0;

            foreach ($this->cart->contents() as $item) {
                if ($item['id'] === $productId && ($item['options']['foto'] ?? '') === $foto) {
                    $existingRowId = $item['rowid'];
                    $existingQty = $item['qty'];
                    break;
                }
            }

            if ($existingRowId) {
                $this->cart->update([
                    'rowid' => $existingRowId,
                    'qty'   => $existingQty + 1,
                ]);
            } else {
                $this->cart->insert([
                    'id'      => $productId,
                    'qty'     => 1,
                    'price'   => $price,
                    'name'    => $name,
                    'options' => [
                        'foto' => $foto
                    ]
                ]);
            }
            
            session()->setFlashdata(
                'success',
                'Produk berhasil ditambahkan ke keranjang. 
                <a href="' . base_url('keranjang') . '">Lihat</a>'
            );
            
            return redirect()->to(base_url('/'));
        } 

    public function cart_edit()
        {
            $i = 1;
            foreach ($this->cart->contents() as $item) {
                $qty = $this->request->getPost('qty' . $i++);

                $this->cart->update([
                    'rowid' => $item['rowid'],
                    'qty'   => $qty
                ]);
            }

            $action = $this->request->getPost('action');
            session()->setFlashdata(
                'success',
                'Keranjang berhasil diperbarui'
            );

            if ($action === 'checkout') {
                return redirect()->to(base_url('checkout'));
            }

            return redirect()->to(base_url('keranjang'));
        }

    public function cart_delete($rowid)
        {
            $this->cart->remove($rowid);

            session()->setFlashdata(
                'success',
                'Produk berhasil dihapus dari keranjang'
            );

            return redirect()->to(base_url('keranjang'));
        }

    public function cart_clear()
        {
            $this->cart->destroy();

            session()->setFlashdata(
                'success',
                'Keranjang berhasil dikosongkan'
            );

            return redirect()->to(base_url('keranjang'));
        }

    public function checkout()
        {  
            $service = new RajaOngkirService();
            $response = $service->getDestination('semarang');
            $response2 = $service->getCost('64999','65042','1000','jne');
            
            $data = [
                'items' => $this->cart->contents(),
                'total' => $this->cart->total(),
                'response' => $response,
                'response2' => $response2
            ];

            return view('v_checkout', $data);
        }

    public function destinations()
        {
            $search = $this->request->getGet('q'); 

            if (empty($search)) {
                return $this->response->setJSON([
                    'results' => []
                ]);
            }

            $service = new RajaOngkirService();
            $response = $service->getDestination($search);

            $results = [];
            $data = $response['data'] ?? [];

            foreach ($data as $item) {
                $results[] = [
                    'id'   => $item['id'],
                    'text' => $item['label']
                ];
            }

            return $this->response->setJSON([
                'results' => $results
            ]);
        }

    public function costs()
        {
            $origin = '64999';
            $destination = $this->request->getGet('destination');
            $weight = '1000';
            $courier = 'jne'; 

            $service = new RajaOngkirService();
            $response = $service->getCost($origin, $destination, $weight, $courier);

            $results = [];
            $data = $response['data'] ?? [];

            foreach ($data as $item) {
                $results[] = [
                    'service'     => $item['service'],
                    'description' => $item['description'],
                    'cost'        => $item['cost'],
                    'etd'         => $item['etd']
                ];
            }

            return $this->response->setJSON($results);
        }

    public function buy()
        { 
            $cartItems = $this->cart->contents();

            if (empty($cartItems)) {
                return redirect()->back();
            }

            $db = \Config\Database::connect();
            $db->transStart(); 

            $subtotal = 0;
            foreach ($cartItems as $item) {
                $subtotal += $item['qty'] * $item['price'];
            }

            $ongkir = (int) $this->request->getPost('ongkir');

            $diskonData = calculate_diskon_total($subtotal);
            $transaction = [
                'username'    => $this->request->getPost('username'),
                'alamat'      => $this->request->getPost('alamat'),
                'ongkir'      => $ongkir,
                'diskon'      => $diskonData['amount'],
                'total_harga' => $subtotal + $ongkir - $diskonData['amount'],
                'status'      => 0, 
            ];

            // insert transaction
            if (!$this->transactionModel->insert($transaction)) {
                $db->transRollback();
                return redirect()->back()->with('error', 'Gagal membuat transaksi');
            }

            $transactionId = $this->transactionModel->getInsertID();

            // insert transaction detail
            $detailSuccess = true;
            foreach ($cartItems as $item) {
                $inserted = $this->transactionDetailModel->insert([
                    'transaction_id' => $transactionId,
                    'product_id'     => $item['id'],
                    'jumlah'         => $item['qty'],
                    'diskon'         => 0,
                    'subtotal_harga' => $item['qty'] * $item['price']
                ]);

                if (!$inserted) {
                    $detailSuccess = false;
                    break;
                }
            }

            if (!$detailSuccess) {
                $db->transRollback();
                return redirect()->back()->with('error', 'Gagal menyimpan detail transaksi');
            }

            $db->transComplete();

            if (!$db->transStatus()) {
                return redirect()->back()->with('error', 'Gagal membuat transaksi');
            }

            // hapus session keranjang belanja
            $this->cart->destroy();
            return redirect()->to(base_url())->with('success', 'Pesanan berhasil dibuat');
        }

        public function history()
        {
            $username = session()->get('username'); 
        
            $transactions = $this->transactionModel->where('username', $username)->findAll();
            $transactionIds = array_column($transactions, 'id');

            $products = $this->transactionDetailModel->getProductsByTransactionIds($transactionIds);

            $data = [
                'username'      => $username,
                'transactions'  => $transactions,
                'products'      => $products
            ]; 

            return view('v_history', $data);
        }
}