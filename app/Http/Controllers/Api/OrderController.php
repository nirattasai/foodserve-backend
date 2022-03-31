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


class OrderController extends Controller
{   
    public function createOrder(Request $request){
        $order = new Order();
        $order->table_id = $request->input('tableId');
        $order->price = $request->input('price');
        $order->type = $request->input('type');
        $order->save();
        $data = [];
        $menus = $request->input('menus');

        foreach($menus as $menu) {
            array_push($data, [
                'order_id' => $order->id,
                'menu_id' => $menu['menu']['id'],
                'amount' => $menu['amount'],
                'status' => 'READY',
                'created_at' => $order->created_at,
            ]);
        }

        DB::table('order_menu')->insert($data);

        return response()->json([
            'success' => true,
            'order' => $order,
            'menus' => $order->menus,
        ]);
    }

    public function updateStatusOrder(Request $request){
        $order = Order::findOrFail($request->input('orderId'));
        $order->status = $request->input('status');

        if ($order->status == 'CANCELED') {
            DB::table('order_menu')
            ->where('order_id', $order->id)
            ->update(array(
                'status' => $order->status,
            ));
        }

        $order->save();
        return response()->json([
            'success' => true,
            'order' => $order,
        ]);
    }

    public function createBill(Request $request) {
        $bill = new Bill();
        $price = 0.0;
        $orderId = $request->input('orderId');
        $tableId = $request->input('tableId');
        $bill->table_id = $tableId;
        $bill->save();

        foreach ($orderId as $order){
            $order = Order::findOrFail($order);
            if($order->status == 'CANCELED'){
                continue;
            }
            $order->status = 'PAYMENT';
            $order->bill_id = $bill->id;
            $order->save();
            $price += $order->price;
            $table_id = $order->table_id;
        } 

        $bill->price = $price;
        $bill->save();

        return response()->json([
            'success' => true,
            'bill' => $bill,
        ]);
    }

    public function updateBill(Request $request) {
        $bill = Bill::findOrFail($request->input('billId'));
        $bill->status = $request->input('status');
        $bill->save();

        if($request->input('status') == 'PAID') {
            $table = Table::find($bill->table->id);
            $table->status = 'READY';
            $table->save();
        }

        return response()->json([
            'success' => true,
            'bill' => $bill,
        ]);
    }

    // query order 
    public function getOrders(Request $request) {
        $user = auth()->user();
        $dateFilter = $request->input('dateFilter');
        $statusFilter = $request->input('statusFilter');
        if($statusFilter == "ALL") {
            $orders = Order::whereIn('table_id', function ($q) use ($user) {
                return $q->select(DB::raw('id'))
                ->from('tables')
                ->where('merchant_id', $user->merchant->id);
            })
            ->whereDate('created_at', $dateFilter)
            ->get();
        }
        else {
            $orders = Order::whereIn('table_id', function ($q) use ($user) {
                return $q->select(DB::raw('id'))
                ->from('tables')
                ->where('merchant_id', $user->merchant->id);
            })
            ->whereDate('created_at', $dateFilter)
            ->where('status', $statusFilter)
            ->get();
        }
        
        $orders_output = [];

        foreach ($orders as $order){
            array_push($orders_output, [
                "id" => $order->id,
                "status" => $order->status,
                "price" => $order->price,
                "type" => $order->type,
                "tableNumber" => $order->table->number,
            ]);
        }
        

        return response()->json([
            'success' => true,
            'orders' => $orders_output,
        ]);
    }

    public function getMenuInOrder(Request $request) {
        $menus = DB::table('order_menu')
        ->where('order_id', $request->input('orderId'))
        ->get();

        $menus_output = [];

        foreach($menus as $menu){
            $m = Menu::find($menu->menu_id);
            array_push($menus_output, [
                'name' => $m->name,
                'price' => $m->price,
                'amount' => $menu->amount,
            ]);
        }

        return response()->json([
            'success' => true,
            'menus' => $menus_output,
        ]);
    }

    public function getBills(Request $request) {
        $user = auth()->user();
        $dateFilter = $request->input('dateFilter');
        $statusFilter = $request->input('statusFilter');

        if ($statusFilter == 'ALL') {
            $bills = Bill::whereIn('table_id', function ($q) use ($user){
                return $q->select(DB::raw("id"))
                ->from('tables')
                ->where('merchant_id', $user->merchant->id)
                ->get();
            })->whereDate('created_at', $dateFilter)->get();
        }
        else {
            $bills = Bill::whereIn('table_id', function ($q) use ($user){
                return $q->select(DB::raw("id"))
                ->from('tables')
                ->where('merchant_id', $user->merchant->id)
                ->get();
            })
            ->whereDate('created_at', $dateFilter)
            ->where('status', $statusFilter)
            ->get();
        }
        return response()->json([
            'success' => true,
            'bills' => $bills,
        ]);    
    }

    public function myOrder(Request $request) {
        $orders = Order::where('table_id', $request->input('tableId'))
        ->where('status', '!=', 'PAYMENT')
        ->where('status', '!=', 'CANCELED')
        ->where('status', '!=', 'PAID')->get();

        return response()->json([
            'success' => true,
            'orders' => $orders,
        ]);    
    }

    public function cancelOrder(Request $request) {
        $order = Order::find($request->input('orderId'));
        $order->status = 'CANCELED';
        $order->save();

        DB::table('order_menu')
        ->where('order_id', $order->id)
        ->update(array(
            'status' => $order->status,
        ));
        return response()->json([
            'success' => true,
            'order' => $order,
        ]);
    }

    public function uploadSlip(Request $request) {
        $bill = Bill::findOrFail($request->input('billId'));
        $bill->slip = $request->input('slip');
        $bill->status = 'WAITING';
        $bill->save();

        if($request->input('status') == 'PAID') {
            $table = Table::find($bill->table->id);
            $table->status = 'READY';
            $table->save();
        }

        return response()->json([
            'success' => true,
            'bill' => $bill,
        ]);
    }

}