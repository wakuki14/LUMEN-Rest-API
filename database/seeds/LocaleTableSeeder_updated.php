<?php

use Illuminate\Database\Seeder;
use App\Locale;

class LocaleTableSeeder_updated extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $locale1 = new Locale();
        $locale1->language_iso = 'en';
        $locale1->sort = 1;
        $locale1->is_default = 1;
        $locale1->save();
        
        $locale2 = new Locale();
        $locale2->language_iso = 'en';
        $locale2->sort = 2;
        $locale2->is_default = 0;
        $locale2->save();
    }
}
