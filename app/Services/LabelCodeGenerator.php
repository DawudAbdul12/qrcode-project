<?php

namespace App\Services;

use App\Models\LabelCode;
use App\Models\LabelOrder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class LabelCodeGenerator
{
    private const ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public function reconcile(LabelOrder $labelOrder): void
    {
        $labelOrder->labelCodes()->update([
            'company_id' => $labelOrder->company_id,
            'batch_id' => $labelOrder->batch_id,
        ]);

        $wanted = max(0, (int) $labelOrder->print_quantity);
        $existing = $labelOrder->labelCodes()->count();

        if ($existing < $wanted) {
            $this->generate($labelOrder, $wanted - $existing);

            return;
        }

        if ($existing > $wanted) {
            $this->removeUnusedExtras($labelOrder, $existing - $wanted);
        }
    }

    private function generate(LabelOrder $labelOrder, int $quantity): void
    {
        $prefix = $this->prefixFor($labelOrder);
        $remaining = $quantity;

        while ($remaining > 0) {
            $chunkSize = min($remaining, 500);
            $rows = collect();

            while ($rows->count() < $chunkSize) {
                $code = $this->uniqueCode($prefix, $rows->pluck('code'));

                $rows->push([
                    'label_order_id' => $labelOrder->id,
                    'company_id' => $labelOrder->company_id,
                    'batch_id' => $labelOrder->batch_id,
                    'code' => $code,
                    'status' => 'unused',
                    'verification_count' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            LabelCode::query()->insert($rows->all());
            $remaining -= $chunkSize;
        }
    }

    private function removeUnusedExtras(LabelOrder $labelOrder, int $quantity): void
    {
        $ids = $labelOrder->labelCodes()
            ->where('status', 'unused')
            ->latest('id')
            ->limit($quantity)
            ->pluck('id');

        if ($ids->isNotEmpty()) {
            LabelCode::query()->whereIn('id', $ids)->delete();
        }
    }

    private function uniqueCode(string $prefix, Collection $pendingCodes): string
    {
        do {
            $code = $prefix.$this->randomBlock(4).$this->randomBlock(4);
        } while ($pendingCodes->contains($code) || LabelCode::query()->where('code', $code)->exists());

        return $code;
    }

    private function prefixFor(LabelOrder $labelOrder): string
    {
        $source = $labelOrder->company?->slug ?: $labelOrder->company?->name ?: 'A8';
        $prefix = Str::of($source)
            ->upper()
            ->replaceMatches('/[^A-Z0-9]/', '')
            ->substr(0, 3)
            ->padRight(3, 'X')
            ->toString();

        return $prefix ?: 'A8X';
    }

    private function randomBlock(int $length): string
    {
        $value = '';
        $max = strlen(self::ALPHABET) - 1;

        for ($i = 0; $i < $length; $i++) {
            $value .= self::ALPHABET[random_int(0, $max)];
        }

        return $value;
    }
}
