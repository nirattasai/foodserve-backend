<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;


use App\Models\User;
use App\Models\Merchant;
use App\Models\Catagory;
use App\Models\Table;
use App\Models\Menu;


class MerchantController extends Controller
{   
    public function createMerchant (Request $request) {

        $merchant = new Merchant();
        $merchant->name = $request->input('name');
        $merchant->address = $request->input('address');
        $merchant->table = $request->input('table');
        $merchant->qr_code = $request->input('qrCode');
        $merchant->owner_id = 1; // MUST BE IMPLEMENT LATER

        $merchant->save();

        for ($i=0; $i<$merchant->table; $i++){
            $table = new Table();
            $table->merchant_id = $merchant->id;
            $table->number = $i+1;
            $table->save();
        }
        
        return response()->json([
            'success' => true,
            'merchant' => $merchant,
        ]);
    }

    public function openMerchant (Request $request) {
        $merchant = Merchant::findOrFail($request->input('merchantId'));
        $merchant->status = 'OPEN';
        $merchant->save();

        return response()->json([
            'success' => true,
            'merchant' => $merchant,
        ]);
    }

    public function closeMerchant (Request $request) {
        $merchant = Merchant::findOrFail($request->input('merchantId'));
        $merchant->status = 'CLOSE';
        $merchant->save();
        
        return response()->json([
            'success' => true,
            'merchant' => $merchant,
        ]);
    }

    public function createCatagory (Request $request) {
        $catagory = new Catagory();
        $catagory->merchant_id = $request->input('merchantId');
        $catagory->name = $request->input('name');
        $catagory->save();  

        $merchant = Merchant::findOrFail($request->input('merchantId'));

        return response()->json([
            'success' => true,
            'catagory' => $merchant->catagories,
        ]);
    }

    public function editCatagory (Request $request) {
        $catagory = Catagory::findOrFail($request->input('catagoryId'));
        $catagory->name = $request->input('name');
        $catagory->save();  

        $merchant = Merchant::findOrFail($catagory->merchant_id);

        return response()->json([
            'success' => true,
            'catagory' => $merchant->catagories,
        ]);
    }

    public function deleteCatagory (Request $request) {
        $catagory = Catagory::findOrFail($request->input('catagoryId'));
        $catagory->delete();

        $merchant = Merchant::findOrFail($catagory->merchant_id);

        return response()->json([
            'success' => true,
            'catagory' => $merchant->catagories,
        ]);
    }
}