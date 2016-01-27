<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Channel;
use App\MerchandiseClass;
use App\User;
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

        $this->call('UserTableSeeder');

        $this->command->info('User table seeded!');


        /*$this->call('MerchandiseClassTableSeeder');

        $this->command->info('merchandise_class table seeded!');*/

        $this->call('ChannelTableSeeder');

        $this->command->info('channel table seeded!');


        Model::reguard();
    }

}

class UserTableSeeder extends Seeder {

    public function run()
    {
        DB::table('users')->delete();

        User::create(['name'=>'TEST','email'=>'buaasoft@163.com','description'=>'testtest']);
    }

}

class ChannelTableSeeder extends Seeder {

    public function run()
    {
        DB::table('channel')->delete();

        Channel::create(['name'=>'所有','channel_number'=>1,'receiver_id'=>'100313','create_user'=>1]);
        Channel::create(['name'=>'面炉','channel_number'=>2,'receiver_id'=>'100313','create_user'=>1]);
        Channel::create(['name'=>'三个炉子','channel_number'=>3,'receiver_id'=>'100313','create_user'=>1]);
        Channel::create(['name'=>'开水炉','channel_number'=>4,'receiver_id'=>'100313','create_user'=>1]);
        Channel::create(['name'=>'排气扇/烟机','channel_number'=>5,'receiver_id'=>'100313','create_user'=>1]);
        Channel::create(['name'=>'三个冰箱','channel_number'=>6,'receiver_id'=>'100313','create_user'=>1]);
        Channel::create(['name'=>'展示柜冰箱','channel_number'=>7,'receiver_id'=>'100313','create_user'=>1]);
        Channel::create(['name'=>'蒸包炉','channel_number'=>8,'receiver_id'=>'100313','create_user'=>1]);
        Channel::create(['name'=>'保温台','channel_number'=>9,'receiver_id'=>'100313','create_user'=>1]);
        Channel::create(['name'=>'可乐插座','channel_number'=>10,'receiver_id'=>'100313','create_user'=>1]);
    }

}
class MerchandiseClassTableSeeder extends Seeder{
    public function run()
    {
        DB::table('merchandise_class')->delete();

        MerchandiseClass::create( ['name'=>'麻辣烫','create_user'=>1]);
        MerchandiseClass::create( ['name'=>'面类','create_user'=>1]);
        MerchandiseClass::create( ['name'=>'小吃','create_user'=>1]);
        MerchandiseClass::create( ['name'=>'饮料','create_user'=>1]);
        MerchandiseClass::create( ['name'=>'蒸点','create_user'=>1]);
        MerchandiseClass::create( ['name'=>'称重','create_user'=>1]);
    }
}