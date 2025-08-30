<?php
namespace Database\Seeders;


use Illuminate\Database\Seeder;
use App\Models\{Product, Category};


class ProductSeeder extends Seeder
{
public function run(): void
{
$map = Category::pluck('id','name');
$items = [
['ขนมปัง', 'ขนมปังโฮลวีต', 35, 50],
['ขนมปัง', 'ครัวซองต์เนยสด', 45, 60],
['เค้ก', 'เค้กช็อกโกแลต', 75, 20],
['เค้ก', 'ชีสเค้ก', 85, 20],
['คุกกี้', 'คุกกี้ช็อกชิป', 25, 100],
['คุกกี้', 'คุกกี้เนย', 20, 120],
['เครื่องดื่ม', 'อเมริกาโน่ (เย็น)', 45, 200],
['เครื่องดื่ม', 'ลาเต้ (เย็น)', 55, 200],
['เครื่องดื่ม', 'โกโก้ (เย็น)', 55, 150],
['เครื่องดื่ม', 'นมสดเย็น', 40, 150],
['ขนมปัง', 'การ์ลิคขนมปัง', 30, 60],
['เค้ก', 'เรดเวลเวท', 85, 15],
];


foreach ($items as [$cat, $name, $price, $stock]) {
Product::updateOrCreate(
['name' => $name],
[
'category_id' => $map[$cat] ?? null,
'price' => $price,
'cost_price' => null,
'stock' => $stock,
'is_active' => true,
]
);
}
}
}