<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;



use App\Models\User;
use App\Models\Merchant;
use App\Models\Catagory;
use App\Models\Table;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Bill;


class CustomerController extends Controller
{   
    public function customerPage(Request $request){
        $table = Table::find($request->input('tableId'));

        $table->status = 'NOT_FREE';
        $table->save();

        $merchant = Merchant::find($table->merchant_id);
        $catagories = $merchant->catagories;
        $catagory = [];
        foreach ($catagories as $catalog) {
            array_push($catagory, [
                'catagoryName' => $catalog->name,
                'catagoryId' => $catalog->id,
                'menus' => $catalog->menus,
            ]);
        }

        return response()->json([
            'success' => true,
            'merchant' => $merchant,
            'table' => $table,
            'catagory' => $catagory,
        ]);
    }
}