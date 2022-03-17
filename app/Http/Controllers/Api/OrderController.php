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
                'menu_id' => $menu['menuId'],
                'amount' => $menu['amount'],
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

        $order->save();
        return response()->json([
            'success' => true,
            'order' => $order,
        ]);
    }

    public function checkinTable(Request $request){
        $table = Table::findOrFail($request->input('tableId'));
        $table->status = 'NOT_FREE';
        $table->save();

        return response()->json([
            'success' => true,
            'table' => $table,
        ]);
    }

    public function createBill(Request $request) {
        $bill = new Bill();
        $bill->save();
        $price = 0.0;
        $orderId = $request->input('orderId');

        foreach ($orderId as $order){
            $order = Order::findOrFail($order);
            $order->status = 'PAYMENT';
            $order->bill_id = $bill->id;
            $order->save();
            $price += $order->price;
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

        return response()->json([
            'success' => true,
            'bill' => $bill,
        ]);
    }

    // query order 
    public function getOrders(Request $request) {
        $user = auth()->user();
        $orders = Order::whereIn('table_id', function ($q) use ($user) {
            return $q->select(DB::raw('id'))
            ->from('tables')
            ->where('merchant_id', $user->merchant->id);
        })->get();
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

    public function getOrdersWithStatus(Request $request) {
        $user = auth()->user();
        $orders = Order::whereIn('table_id', function ($q) use ($user) {
            return $q->select(DB::raw('id'))
            ->from('tables')
            ->where('merchant_id', $user->merchant->id);
        })->where('status', $request->input('status'))->get();

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
}