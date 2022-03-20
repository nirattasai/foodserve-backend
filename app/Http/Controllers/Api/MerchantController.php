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


class MerchantController extends Controller
{   
    public function createMerchant (Request $request) {
        $user = auth()->user();
        $merchant = new Merchant();
        $merchant->name = $request->input('name');
        $merchant->address = $request->input('address');
        $merchant->table = $request->input('table');
        $merchant->qr_code = $request->input('qrCode');
        $merchant->owner_id = $user->id; // MUST BE IMPLEMENT LATER

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

    public function updateStatusMerchant (Request $request) {
        $merchant = Merchant::findOrFail($request->input('merchantId'));
        $merchant->status = $request->input('status');
        $merchant->save();

        return response()->json([
            'success' => true,
            'merchant' => $merchant,
        ]);
    }

    public function createCatagory (Request $request) {
        $user = auth()->user();
        $catagory = new Catagory();
        $catagory->name = $request->input('name');
        $catagory->merchant_id = $user->merchant->id;
        $catagory->save();

        $merchant = Merchant::findOrFail($user->merchant->id);
        

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
    
    public function createMenu (Request $request) {
        $menu = new Menu();
        $menu->name = $request->input('name');
        $menu->price = $request->input('price');
        $menu->detail = $request->input('detail');
        if ($request->input('image') != null){
            $menu->image = $request->input('image');
        }
        $menu->catagory_id = $request->input('catagoryId');
        $menu->save();

        return response()->json([
            'success' => true,
            'menu' => $menu,
        ]);
    }

    public function updateStatusMenu (Request $request) {
        $menu = Menu::findOrFail($request->input('menuId'));
        $menu->status = $request->input('status');
        $menu->save();
        return response()->json([
            'success' => true,
            'menu' => $menu,
        ]);
    }

    public function deleteMenu (Request $request) {
        $menu = Menu::findOrFail($request->input('menuId'));
        $menu->delete();
        return response()->json([
            'success' => true,
            'menu' => $menu,
        ]);
    }

    public function editMenu (Request $request) {
        $menu = Menu::findOrFail($request->input('menuId'));
        if ($request->input('name') != null){
            $menu->name = $request->input('name');
        }
        if ($request->input('price') != null){
            $menu->price = $request->input('price');
        }
        if ($request->input('detail') != null){
            $menu->detail = $request->input('detail');
        }
        if ($request->input('image') != null){
            $menu->image = $request->input('image');
        }
        if ($request->input('status') != null){
            $menu->status = $request->input('status');
        }
        $menu->save();
        return response()->json([
            'success' => true,
            'menu' => $menu,
        ]);
    }

    // query request

    public function getMerchantWithId (Request $request) {
        $merchant = Merchant::findOrFail($request->input('merchantId'));
        return response()->json([
            'success' => true,
            'merchant' => $merchant,
        ]);
    }

    public function getMerchantWithUser (Request $request) {
        $user = auth()->user();
        $merchant = Merchant::find($user->merchant->id);
        return response()->json([
            'success' => true,
            'merchant' => $merchant,
        ]);
    }

    public function getCatagories (Request $request) {
        $user = auth()->user();
        $catagories = $user->merchant->catagories;
        return response()->json([
            'success' => true,
            'catagories' => $catagories,
        ]);
    }

    public function getMenuWithCatagoryId (Request $request) {
        $catagory = Catagory::where('id', $request->input('catagoryId'))->first();
        $menus = Menu::where('catagory_id', $catagory->id)->get();
        return response()->json([
            'success' => true,
            'menus' => $menus,
        ]);
    }

    public function getMenus (Request $request) {
        $user = auth()->user();
        $menus = Menu::whereIn('catagory_id', function ($q) use ($user) {
            return $q->select(DB::raw('id'))
                ->from('catagories')
                ->where('merchant_id', $user->merchant->id);
        })->get();

        return response()->json([
            'success' => true,
            'menus' => $menus,
        ]);
    }

    public function getTables (Request $request) {
        $user = auth()->user();
        $tables = Table::where('merchant_id', $user->merchant->id)->get();
        return response()->json([
            'success' => true,
            'tables' => $tables,
        ]);
    }
}