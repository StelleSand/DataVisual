<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // $this->call(UserTableSeeder::class);

       /* $this->call('UserTableSeeder');

        $this->command->info('User table seeded!');*/


        $this->call('MerchandiseClassSeeder');

        $this->command->info('merchandise_class table seeded!');


        Model::reguard();
    }

}

class UserTableSeeder extends Seeder {

    public function run()
    {
        DB::table('users')->delete();

        DB::insert('insert into users (name,email,password) values (?, ?, ?)', ['TEST','buaasoft@163.com','testtest']);
    }

}

class ChannelTableSeeder extends Seeder {

    public function run()
    {
        DB::table('channel')->delete();

        DB::insert('insert into channel (name,channel_number,receiver_id,nominal_power,power_factor,create_user) values (?, ?, ?, ?, ?, ?)', ['所有',1,'100313',0,1,1]);
        DB::insert('insert into channel (name,channel_number,receiver_id,nominal_power,power_factor,create_user) values (?, ?, ?, ?, ?, ?)', ['面炉',2,'100313',8,1,1]);
        DB::insert('insert into channel (name,channel_number,receiver_id,nominal_power,power_factor,create_user) values (?, ?, ?, ?, ?, ?)', ['三个炉子',3,'100313',0,1,1]);
        DB::insert('insert into channel (name,channel_number,receiver_id,nominal_power,power_factor,create_user) values (?, ?, ?, ?, ?, ?)', ['开水炉',4,'100313',5.94,1,1]);
        DB::insert('insert into channel (name,channel_number,receiver_id,nominal_power,power_factor,create_user) values (?, ?, ?, ?, ?, ?)', ['排风扇/烟机',5,'100313',1.5,1,1]);
        DB::insert('insert into channel (name,channel_number,receiver_id,nominal_power,power_factor,create_user) values (?, ?, ?, ?, ?, ?)', ['三个冰箱',6,'100313',0,1,1]);
        DB::insert('insert into channel (name,channel_number,receiver_id,nominal_power,power_factor,create_user) values (?, ?, ?, ?, ?, ?)', ['展示柜冰箱',7,'100313',2,1,1]);
        DB::insert('insert into channel (name,channel_number,receiver_id,nominal_power,power_factor,create_user) values (?, ?, ?, ?, ?, ?)', ['蒸包炉',8,'100313',3,1,1]);
        DB::insert('insert into channel (name,channel_number,receiver_id,nominal_power,power_factor,create_user) values (?, ?, ?, ?, ?, ?)', ['保温台',9,'100313',2,1,1]);
        DB::insert('insert into channel (name,channel_number,receiver_id,nominal_power,power_factor,create_user) values (?, ?, ?, ?, ?, ?)', ['可乐插座',10,'100313',0,1,1]);
    }

}
class MerchandiseClassSeeder extends Seeder{
    public function run()
    {
        DB::table('merchandise_class')->delete();

        DB::insert('insert into merchandise_class (name,create_user) values (?,?)', ['麻辣烫',1]);
        DB::insert('insert into merchandise_class (name,create_user) values (?,?)', ['面类',1]);
        DB::insert('insert into merchandise_class (name,create_user) values (?,?)', ['小吃',1]);
        DB::insert('insert into merchandise_class (name,create_user) values (?,?)', ['饮料',1]);
        DB::insert('insert into merchandise_class (name,create_user) values (?,?)', ['蒸点',1]);
    }
}
class MerchandiseSeeder extends Seeder{
    public function run()
    {
        DB::table('merchandise')->delete();

        DB::insert('insert into merchandise (name) values (?)', ['一元类']);
    }
}