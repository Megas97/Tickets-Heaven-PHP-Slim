<?php

use Phinx\Seed\AbstractSeed;

class ContinentsDbInfo extends AbstractSeed {
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
                'name' => 'Europe',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Asia',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
        ];

        $continents = $this->table('continents');

        $continents->truncate();

        $continents->insert($data)->saveData();
    }
}
