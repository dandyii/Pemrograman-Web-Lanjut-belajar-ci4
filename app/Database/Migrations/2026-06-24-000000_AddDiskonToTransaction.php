<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDiskonToTransaction extends Migration
{
    public function up()
    {
        $fields = [
            'diskon' => [
                'type'       => 'DOUBLE',
                'null'       => false,
                'default'    => 0,
                'comment'    => 'Nilai diskon dalam rupiah',
            ],
        ];

        $this->forge->addColumn('transaction', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('transaction', 'diskon');
    }
}
