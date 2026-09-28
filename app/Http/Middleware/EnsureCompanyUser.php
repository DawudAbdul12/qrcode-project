<?php

namespace App\Http\Middleware;

use App\Models\Batch;
use App\Models\Company;
use App\Models\CompanyProfile;
use App\Models\LabelCode;
use App\Models\LabelOrder;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\QrCode;
use App\Models\ScanLog;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyUser
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isActive() || ! $user->isCompanyUser()) {
            abort(403);
        }

        foreach ($request->route()?->parameters() ?? [] as $parameter) {
            $companyId = $this->companyIdFor($parameter);

            if ($companyId !== null && (int) $companyId !== (int) $user->company_id) {
                abort(403);
            }
        }

        return $next($request);
    }

    private function companyIdFor(mixed $parameter): ?int
    {
        return match (true) {
            $parameter instanceof Company => $parameter->id,
            $parameter instanceof CompanyProfile => $parameter->company_id,
            $parameter instanceof Product => $parameter->company_id,
            $parameter instanceof ProductCategory => $parameter->company_id,
            $parameter instanceof Batch => $parameter->product?->company_id,
            $parameter instanceof LabelOrder => $parameter->company_id,
            $parameter instanceof LabelCode => $parameter->company_id,
            $parameter instanceof QrCode => $parameter->batch?->product?->company_id,
            $parameter instanceof ScanLog => $parameter->labelCode?->company_id
                ?? $parameter->qrCode?->batch?->product?->company_id,
            $parameter instanceof User => $parameter->company_id,
            default => null,
        };
    }
}
