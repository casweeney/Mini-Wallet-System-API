<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wallet;
use App\Models\Transaction;

class WalletController extends Controller
{
    public function deposit(Request $request)
    {
        $this->validate($request, [
            'amount' => 'required|numeric'
        ]);

        try {
            DB::beginTransaction();

            $checkWallet = Wallet::where('user_id', auth()->user()->id)->first();

            if(!checkWallet) {
                $wallet = new Wallet;
                $wallet->user_id = auth()->user()->id;
                $wallet->main = $request->amount;
                $wallet->save();
            } else {
                $checkWallet->balance = $checkWallet->balance + $request->amount;
                $checkWallet->save();
            }

            $transaction = new Transaction;
            $transaction->user_id = auth()->user()->id;
            $transaction->transaction_type = "credit";
            $transaction->amount = $request->amount;
            $transaction->save();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
        }
    }

    public function withdraw(Request $request)
    {
        $this->validate($request, [
            'amount' => 'required|numeric'
        ]);

        try {
            DB::beginTransaction();

            $checkWallet = Wallet::where('user_id', auth()->user()->id)->first();

            if(!checkWallet) {
                return "insufficient funds";
            } elseif($request->amount > $checkWallet->balance) {
                return "insufficient funds";
            } else {
                $checkWallet->balance = $checkWallet->balance - $request->amount;
                $checkWallet->save();
            }

            $transaction = new Transaction;
            $transaction->user_id = auth()->user()->id;
            $transaction->transaction_type = "debit";
            $transaction->amount = $request->amount;
            $transaction->save();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
        }
    }
}
