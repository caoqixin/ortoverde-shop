<?php

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            [
                'name' => '瓜类',
                'children' => [
                    ['name' => '自产水果'],
                    ['name' => '自产瓜类'],
                    ['name' => '进口水果'],
                    ['name' => '进口瓜类'],


                ]
            ],
            [
                'name' => '蔬菜',
                'children' => [
                    ['name' => '自产时蔬'],
                    ['name' => '进口时蔬'],
                ],
            ],
        ];

        foreach ($categories as $data) {
            $this->createCategory($data);
        }
    }


    public function createCategory($data, $parent = null)
    {
        // 创建一个新的类目对象
        $category = new Category(['name' => $data['name']]);
        // 如果有 children 字段则代表这是个父类目
        $category->is_directory = isset($data['children']);
        // 如果有传入 $parent 参数, 代表有父类目
        if (!is_null($parent)) {
            $category->parent()->associate($parent);
        }

        // 保存到数据库

        $category->save();
        // 如果有 children 字段 并且 children 字段是个数组
        if (isset($data['children']) && is_array($data['children'])) {
            // 遍历 children 字段
            foreach ($data['children'] as $child) {
                // 递归调用 createCategory 方法, 第二个参数 为 刚刚创建的类目
                $this->createCategory($child, $category);
            }
        }
    }
}
