<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class TermsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json_file = Storage::disk('local')->get('terms/terms.json');
        $arr_file = json_decode($json_file, true);
        
        foreach($arr_file as $type => $terms) {
            $term_type_id = $type == 'disabled_functions' ? 1 : 2;
            foreach($terms as $term) {
                DB::table('terms')->insert(
                    [
                        'term' => $term,
                        'term_type_id' => $term_type_id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]
                );
            }
        }
    }
}
