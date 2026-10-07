<?php
declare(strict_types=1);

namespace App\Core;

class InvoiceBuilder
{
    public const DEFAULT_GST_RATE = 18.00;

    public static function createForPayment(int $paymentId, float $subtotal, float $gstRate = self::DEFAULT_GST_RATE): array
    {
        $gstAmount = round($subtotal * ($gstRate / 100), 2);
        $total = round($subtotal + $gstAmount, 2);

        $invoiceNo = 'INV-' . date('Ym') . '-' . strtoupper(bin2hex(random_bytes(3)));

        Database::query(
            "INSERT INTO invoices (payment_id, invoice_no, subtotal, gst_rate, gst_amount, total, issued_at)
             VALUES (:payment_id, :invoice_no, :subtotal, :gst_rate, :gst_amount, :total, NOW())",
            [
                'payment_id' => $paymentId,
                'invoice_no' => $invoiceNo,
                'subtotal'   => $subtotal,
                'gst_rate'   => $gstRate,
                'gst_amount' => $gstAmount,
                'total'      => $total,
            ]
        );

        $id = (int)Database::lastInsertId();

        return [
            'id'         => $id,
            'payment_id' => $paymentId,
            'invoice_no' => $invoiceNo,
            'subtotal'   => $subtotal,
            'gst_rate'   => $gstRate,
            'gst_amount' => $gstAmount,
            'total'      => $total,
            'issued_at'  => date('Y-m-d H:i:s'),
        ];
    }

    public static function formatRupees(float $amount): string
    {
        return '₹' . number_format($amount, 2, '.', ',');
    }
}
