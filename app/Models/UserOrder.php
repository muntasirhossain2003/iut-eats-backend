<?php
namespace App\Models;

use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Model;

class UserOrder extends Model
{
    use DefaultDatetimeFormat;

    // Define the table name
    protected $table = 'user_orders';

}
