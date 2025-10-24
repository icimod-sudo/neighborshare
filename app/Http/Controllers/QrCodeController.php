<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class QrCodeController extends Controller
{
    public function generateProductQr(Product $product)
    {
        // Generate product share URL
        $productUrl = route('products.show', $product);

        // Generate QR code
        $qrCode = \QrCode::size(300)
            ->backgroundColor(255, 255, 255)
            ->color(16, 185, 129) // Green color matching your theme
            ->margin(1)
            ->generate($productUrl);

        return response($qrCode)->header('Content-Type', 'image/svg+xml');
    }

    public function downloadProductQr(Product $product, $type = 'svg')
    {
        $productUrl = route('products.show', $product);
        $filename = "product-{$product->id}-qr." . $type;

        switch ($type) {
            case 'png':
                $qrCode = \QrCode::format('png')
                    ->size(300)
                    ->backgroundColor(255, 255, 255)
                    ->color(16, 185, 129)
                    ->margin(1)
                    ->generate($productUrl);

                return response($qrCode)
                    ->header('Content-Type', 'image/png')
                    ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");

            case 'svg':
            default:
                $qrCode = \QrCode::size(300)
                    ->backgroundColor(255, 255, 255)
                    ->color(16, 185, 129)
                    ->margin(1)
                    ->generate($productUrl);

                return response($qrCode)
                    ->header('Content-Type', 'image/svg+xml')
                    ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
        }
    }

    public function shareProduct(Product $product)
    {
        $product->load('user');
        $qrCodeUrl = route('qr.product.generate', $product);
        $productUrl = route('products.show', $product);

        // Shareable message
        $shareMessage = "Check out this product on Gwache App:\n\n";
        $shareMessage .= "🌱 {$product->title}\n";
        $shareMessage .= "💰 " . ($product->is_free ? 'FREE' : "₹{$product->price}") . "\n";
        $shareMessage .= "📦 {$product->quantity} {$product->unit}\n";
        $shareMessage .= "👤 Shared by: {$product->user->name}\n\n";
        $shareMessage .= "View product: {$productUrl}";

        return view('products.share', compact('product', 'qrCodeUrl', 'productUrl', 'shareMessage'));
    }
}
