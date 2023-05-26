<?php

use Phinx\Seed\AbstractSeed;

class PhoneCodesDbInfo extends AbstractSeed {
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run() {
        
        $data = [
            [
                'code' => '+359',
                'country_id' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'code' => '+49',
                'country_id' => 2,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'code' => '+82',
                'country_id' => 3,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
        ];

        $phone_codes = $this->table('phone_codes');

        $phone_codes->truncate();

        $phone_codes->insert($data)->saveData();
    }
}
