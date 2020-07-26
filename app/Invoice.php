<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Konekt\PdfInvoice\InvoicePrinter;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_id','type','project_id','status','issue_date','due_date','discount','tax_id','client_id','workspace_id'
    ];

    public function project(){
        return $this->hasOne('App\Project','id','project_id');
    }

    public function client(){
        return $this->hasOne('App\Client','id','client_id');
    }

    public function items(){
        return $this->hasMany('App\InvoiceItem','invoice_id','id');
    }

    public function tax(){
        return $this->hasOne('App\Tax','id','tax_id');
    }

    public function getTotal(){
        $sub_total = $this->getSubTotal();
        $discount = $this->discount;
        $tax = $this->getTaxTotal();
        $total = $sub_total + $tax - $discount;
        return $total;
    }

    public function getSubTotal(){
        $subtotal = 0;
        $items = $this->items;
        if($items){
            foreach ($items as $item){
                $subtotal += $item->price * $item->qty;
            }
        }
        return $subtotal;
    }

    public function getTaxTotal(){
        $tax = 0;
        if($objTax = $this->tax) {
            $sub_total = $this->getSubTotal() - $this->discount;
            $tax = ($sub_total * $objTax->rate) / 100;
        }
        return $tax;
    }

    public function pdf($currantWorkspace)
    {
        $customer = $this->client;

        $customerdetails = [
            ucfirst($customer->name),
            $customer->email,
            '',
            '',
            '',
            '',
        ];

        $user = $currantWorkspace->creater;

        $userdetails = [
            ucfirst($user->name),
            $user->email,
            '',
            '',
            '',
            '',
        ];

        $invoice_id    = Utility::invoiceNumberFormat($this->invoice_id);
        $invoice_color = '#4FD1FE';

        $quotation_note = '';
        $items = $this->items;
        $invoice = new InvoicePrinter("A4", $currantWorkspace->currency, $currantWorkspace->lang);

        $invoice->setLogo(env('APP_URL').Storage::url('logo/logo-full.png'));
        $invoice->setColor($invoice_color);
        $invoice->setType($invoice_id);
//        $invoice->setReference($quotation->reference_no);
        $invoice->setDate(Utility::dateFormat($this->issue_date));
//        $invoice->setTime($user->timeFormat($quotation->created_at));
        $invoice->setDue(Utility::dateFormat($this->issue_date));
        $invoice->setFrom($userdetails);
        $invoice->setTo($customerdetails);
        $total = 0;
        foreach($items as $key => $item)
        {
            $sub_total = $item->qty * $item->price;
            if($invoice->type == 'product') {
                $name = $item->product->name;
            }
            else{
                $name = $item->task->title .' - '. $item->task->project->name;
            }
            $invoice->addItem($name, "", $item->qty,false, $item->price, false,$sub_total);
        }
        if($this->discount) {
            $invoice->addTotal("Discount", $this->discount, false);
        }
        if($this->tax){
            $invoice->addTotal( $this->tax->name.' (' .$this->tax->rate.'%)', $this->getTaxTotal(), false);
        }

        $invoice->addTotal("Total", $this->getTotal(), true);
        if($this->status == 'sent')
        {
            $invoice->addBadge(__('Sent'),'#ffc107');
        }
        elseif($this->status == 'paid'){
            $invoice->addBadge(__('Paid'),'#47c363');
        }
        else
        {
            $invoice->addBadge(__('Canceled'),'#fc544b');
        }
        $invoice->addTitle("Important Notice");
        $quotation_note = (isset($quotation_note) && !empty($quotation_note)) ? $quotation_note : "No item will be replaced or refunded if you don't have the invoice with you.";
        $invoice->addParagraph($quotation_note);
        $invoice->setFooternote(URL::to('/'));
        $name = 'invoice/invoice_' . md5(time()) . '.pdf';
        $invoice->render('I', $name);
    }

    public function getDueAmount(){
        $total=0;
        $payments = $this->payments;
        if($payments){
            foreach ($payments as $payment){
                $total += $payment->amount;
            }
        }

        return $this->getTotal() - $total;
    }

    public function payments(){
        return $this->hasMany('App\InvoicePayment','invoice_id','id');
    }
}
