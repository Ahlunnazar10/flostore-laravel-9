<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;

class CheckoutController extends Controller
{
    public function process(Request $request)
    {
        // Save user data
        $user = Auth::user();
        $user->update($request->except("total_price"));

        // Proses checkout
        $code = "STORE-" . mt_rand(00000, 99999);
        $carts = Cart::with(["product", "user"])
            ->where("users_id", Auth::user()->id)
            ->get();

        // Transaction Create
        $transaction = Transaction::create([
            "users_id" => Auth::user()->id,
            "insurance_price" => 0,
            "shipping_price" => 0,
            "total_price" => (int) $request->total_price,
            "transaction_status" => "PENDING",
            "code" => $code,
        ]);

        // Save data ke trasnaction details
        foreach ($carts as $cart) {
            $trx = "TRX-" . mt_rand(00000, 99999);

            TransactionDetail::create([
                "transactions_id" => $transaction->id,
                "products_id" => $cart->product->id,
                "price" => $cart->product->price,
                "shipping_status" => "PENDING",
                "resi" => "",
                "code" => $trx,
            ]);
        }

        // Menghapus cart data
        Cart::where("users_id", Auth::user()->id)->delete();

        // Konfigurasi Midtrans
        Config::$serverKey = config("services.midtrans.serverKey");
        Config::$isProduction = config("services.midtrans.isProduction");
        Config::$isSanitized = config("services.midtrans.isSanitized");
        Config::$is3ds = config("services.midtrans.is3ds");

        // Membuat Array agar dapat dikirim ke midtrans
        $midtrans = [
            "transaction_details" => [
                "order_id" => $code,
                "gross_amount" => (int) $request->total_price,
            ],
            "custumer_details" => [
                "first_name" => Auth::user()->name,
                "email" => Auth::user()->email,
            ],
            "enabled_payments" => [
                "bca_va",
                "bni_va",
                "bri_va",
                "gopay",
                "indomaret",
                "shopeepay",
            ],
            "vtweb" => [],
        ];

        try {
            $paymentUrl = Snap::createTransaction($midtrans)->redirect_url;
            return redirect($paymentUrl);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function callback(Request $request)
    {
        // set konfigurasi midtrans
        Config::$serverKey = config("services.midtrans.serverKey");
        Config::$isProduction = config("services.midtrans.isProduction");
        Config::$isSanitized = config("services.midtrans.isSanitized");
        Config::$is3ds = config("services.midtrans.is3ds");

        // Instance midtrans notification
        $notification = new Notification();

        // Assign ke variable untuk memudahkan coding
        $status = $notification->transaction_status;
        $type = $notification->payment_type;
        $fraud = $notification->fraud_status;
        $order_id = $notification->order_id;

        // cari transaksi berdasarkan ID
        $transaction = Transaction::findOrFail($order_id);

        // handle status notifikasi
        if ($status == "capture") {
            if ($type == "credit_card") {
                if ($fraud == "challenge") {
                    $transaction->status = "PENDING";
                } else {
                    $transaction->status = "SUCCESS";
                }
            }
        } elseif ($status == "settlement") {
            $transaction->status = "SUCCESS";
        } elseif ($status == "pending") {
            $transaction->status = "PENDING";
        } elseif ($status == "deny") {
            $transaction->status = "CANCELLED";
        } elseif ($status == "expire") {
            $transaction->status = "CANCELLED";
        } elseif ($status == "cancle") {
            $transaction->status = "CANCELLED";
        }

        // simpan transaksi
        $transaction->save();
    }
}
