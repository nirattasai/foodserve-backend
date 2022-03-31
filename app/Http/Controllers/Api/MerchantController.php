<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Imagick;

use App\Models\User;
use App\Models\Merchant;
use App\Models\Catagory;
use App\Models\Table;
use App\Models\Menu;
use App\Models\Bill;


class MerchantController extends Controller
{   
    public function createMerchant (Request $request) {
        $url = env('APP_URL', 'http://localhost');
        $user = auth()->user();
        $merchant = Merchant::find($user->merchant->id);
        $merchant->name = $request->input('name');
        $merchant->address = $request->input('address');
        $merchant->table = $request->input('table');
        $merchant->qr_code = $request->input('qrCode');
        // $merchant->owner_id = $user->id; // MUST BE IMPLEMENT LATER

        $merchant->save();

        $tables = Table::where('merchant_id', $merchant->id)->get();
        if (count($tables) == 0) {
            for ($i=0; $i<$merchant->table; $i++){
                $table = new Table();
                $table->merchant_id = $merchant->id;
                $table->number = $i+1;
                $table->save();
                $table->qr_code = base64_encode(QrCode::format('png')->size(600)->generate($url.':8000/table/'.$table->id));
                $table->save();
            }
        }
        else if (count($tables) == $request->input('table')) {
            foreach ($tables as $table) {
                $table->qr_code = base64_encode(QrCode::format('png')->size(600)->generate($url.':8000/table/'.$table->id));
                $table->save();
            }
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

    public function dashboard(Request $request) {
        $user = auth()->user();
        $dateFilter = $request->input('dateFilter');

        $bills = Bill::whereIn('table_id', function ($q) use ($user, $dateFilter){
            return $q->select(DB::raw("id"))
            ->from('tables')
            ->where('merchant_id', $user->merchant->id)
            ->get();
        })
        ->where('status', 'PAID')
        ->whereDate('created_at', $dateFilter)
        ->get();
        $orders = [];
        $price = 0.0;
        foreach ($bills as $bill) {
            $price += $bill->price;
            $orders = array_merge($orders, $bill->orders()->where('status', '!=', 'CANCELED')->get()->toArray());
        }
        $menuArr = [];
        $menus = Menu::whereIn('catagory_id', function ($q) use ($user) {
            return $q->select(DB::raw('id'))
            ->from('catagories')
            ->where('merchant_id', $user->merchant->id)
            ->get();
        })->get();

        foreach ($menus as $menu) {
            foreach ($orders as $order){
                array_push($menuArr, [
                    'menuId' => $menu->id,
                    'amount' => DB::table('order_menu')
                    ->where('menu_id','=', $menu->id)
                    ->where('order_id', '=', $order['id'])
                    ->sum('amount'),
                ]);
            }
            
        }
        $max = (object)[
            'id' => -1,
            'amount' => 0,
        ];

        foreach($menuArr as $menu) {
            if($max->amount < $menu['amount']) {
                $max->id = $menu['menuId'];
                $max->amount = $menu['amount'];
            }
        }

        $mostMenu = Menu::find($max->id);
        
        return response()->json([
            'success' => true,
            'orderCount' => count($orders),
            'price' => $price,
            'mostMenu' => $mostMenu,
            'max' => $max,
        ]);
        
    }

    public function monthlyReport(Request $request) {
        $user = auth()->user();
        $from = date($request->input('from'));
        $to = date($request->input('to'));

        $bills = Bill::whereIn('table_id', function ($q) use ($user, $from, $to){
            return $q->select(DB::raw("id"))
            ->from('tables')
            ->where('merchant_id', $user->merchant->id)
            ->get();
        })
        ->where('status', 'PAID')
        ->whereBetween('created_at', [$from, $to])
        ->get();
        $orders = [];
        $price = 0.0;
        foreach ($bills as $bill) {
            $price += $bill->price;
            $orders = array_merge($orders, $bill->orders()->where('status', '!=', 'CANCELED')->get()->toArray());
        }

        $menuArr = [];
        $menus = Menu::whereIn('catagory_id', function ($q) use ($user) {
            return $q->select(DB::raw('id'))
            ->from('catagories')
            ->where('merchant_id', $user->merchant->id)
            ->get();
        })->get();

        foreach ($menus as $menu) {
            foreach ($orders as $order){
                array_push($menuArr, [
                    'menuId' => $menu->id,
                    'amount' => DB::table('order_menu')
                    ->where('menu_id','=', $menu->id)
                    ->where('order_id', '=', $order['id'])
                    ->sum('amount'),
                ]);
            }
            
        }
        $max = (object)[
            'id' => -1,
            'amount' => 0,
        ];

        foreach($menuArr as $menu) {
            if($max->amount < $menu['amount']) {
                $max->id = $menu['menuId'];
                $max->amount = $menu['amount'];
            }
        }

        $mostMenu = Menu::find($max->id);
        
        return response()->json([
            'success' => true,
            'orderCount' => count($orders),
            'price' => $price,
            'mostMenu' => $mostMenu,
            'max' => $max,
        ]);
    }

    public function downloadDailyReport(Request $request) {

        $date = $request->query('date');
        $merchantId = $request->query('merchantId');

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=download.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $bills = Bill::whereIn('table_id', function ($q) use ($merchantId, $date){
            return $q->select(DB::raw("id"))
            ->from('tables')
            ->where('merchant_id', $merchantId)
            ->get();
        })
        ->where('status', 'PAID')
        ->whereDate('created_at', $date)
        ->get();
        $orders = [];
        $price = 0.0;
        foreach ($bills as $bill) {
            $price += $bill->price;
            $orders = array_merge($orders, $bill->orders()->where('status', '!=', 'CANCELED')->get()->toArray());
        }
        $menuArr = [];
        $menus = Menu::whereIn('catagory_id', function ($q) use ($merchantId) {
            return $q->select(DB::raw('id'))
            ->from('catagories')
            ->where('merchant_id', $merchantId)
            ->get();
        })->get();

        foreach ($menus as $menu) {
                array_push($menuArr, [
                    'id' => $menu->id,
                    'menu' => $menu->name,
                    'price' => $menu->price,
                    'amount' => DB::table('order_menu')
                    ->where('menu_id','=', $menu->id)
                    ->where('status', '!=', 'CANCELED')
                    ->whereDate('created_at', $date)
                    ->sum('amount'),
                ]);
        }


        $columns = array('เมนู', 'ราคาต่อจาน','จำนวนที่ขายได้', 'ราคาทั้งหมด');

        $callback = function() use($columns, $menuArr) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($menuArr as $menu) {
                $row['name']  = $menu['menu'];
                $row['price'] = $menu['price'];
                $row['amount'] = $menu['amount'];
                $row['total']    = $menu['amount']*$menu['price'];

                fputcsv($file, array($row['name'], $row['price'], $row['amount'], $row['total']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function downloadMonthlyReport(Request $request) {

        $from = $request->query('from');
        $to = $request->query('to');
        $merchantId = $request->query('merchantId');

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=download.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $bills = Bill::whereIn('table_id', function ($q) use ($merchantId, $from, $to){
            return $q->select(DB::raw("id"))
            ->from('tables')
            ->where('merchant_id', $merchantId)
            ->get();
        })
        ->where('status', 'PAID')
        ->whereBetween('created_at', [$from, $to])
        ->get();
        $orders = [];
        $price = 0.0;
        foreach ($bills as $bill) {
            $price += $bill->price;
            $orders = array_merge($orders, $bill->orders()->where('status', '!=', 'CANCELED')->get()->toArray());
        }
        $menuArr = [];
        $menus = Menu::whereIn('catagory_id', function ($q) use ($merchantId) {
            return $q->select(DB::raw('id'))
            ->from('catagories')
            ->where('merchant_id', $merchantId)
            ->get();
        })->get();

        foreach ($menus as $menu) {
                array_push($menuArr, [
                    'id' => $menu->id,
                    'menu' => $menu->name,
                    'price' => $menu->price,
                    'amount' => DB::table('order_menu')
                    ->where('menu_id','=', $menu->id)
                    ->where('status', '!=', 'CANCELED')
                    ->whereBetween('created_at', [$from, $to])
                    ->sum('amount'),
                ]);
        }


        $columns = array('เมนู', 'ราคาต่อจาน','จำนวนที่ขายได้', 'ราคาทั้งหมด');

        $callback = function() use($columns, $menuArr) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($menuArr as $menu) {
                $row['name']  = $menu['menu'];
                $row['price'] = $menu['price'];
                $row['amount'] = $menu['amount'];
                $row['total']    = $menu['amount']*$menu['price'];

                fputcsv($file, array($row['name'], $row['price'], $row['amount'], $row['total']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

}