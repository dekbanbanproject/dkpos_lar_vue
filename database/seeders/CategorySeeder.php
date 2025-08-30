<?php
namespace Database\Seeders;


use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;


class CategorySeeder extends Seeder
{
public function run(): void
{
$names = ['ขนมปัง','เค้ก','คุกกี้','เครื่องดื่ม'];
foreach ($names as $n) {
Category::updateOrCreate(['slug' => Str::slug($n)], ['name' => $n]);
}
}
}