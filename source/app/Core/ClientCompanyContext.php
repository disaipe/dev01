<?php

namespace App\Core;

use App\Facades\Auth;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ClientCompanyContext
{
    protected ?User $user = null;

    protected ?string $companyContextId = null;

    protected ?string $companyContextCode = null;

    protected const COMPANY_CONTEXT_COOKIE_NAME = 'company-context';

    public function __construct()
    {
        /** @var User $user */
        $this->user = Auth::user();

        if ($this->user && $this->user->isClient()) {
            // Cookie::get('company-context');
            $this->companyContextId = Arr::get($_COOKIE, self::COMPANY_CONTEXT_COOKIE_NAME);

            if ($this->companyContextId) {
                /** @var Company $company */
                $company = $this->user->companies()
                    ->withoutGlobalScopes()
                    ->whereKey($this->companyContextId)
                    ->first();

                if ($company) {
                    $this->companyContextCode = $company->code;

                    return;
                }
            } else {
                /** @var Company $company */
                $company = $this->user->companies()
                    ->withoutGlobalScopes()
                    ->first();

                if ($company) {
                    $this->companyContextId = $company->getKey();
                    $this->companyContextCode = $company->code;

                    setcookie(self::COMPANY_CONTEXT_COOKIE_NAME, $this->companyContextId, ['path' => '/']);

                    return;
                }
            }
        }

        // set wrong company identity
        $this->companyContextId = -1;
        $this->companyContextCode = Str::random(8);
    }

    public function isHasCompanyContext(): bool
    {
        if ($this->user) {
            return $this->user->isClient();
        }

        return false;
    }

    public function getCompanyContextCode(): ?string
    {
        return $this->companyContextCode;
    }

    public function getClientCompaniesCodes(): Collection
    {
        return $this->user->companies()->withoutGlobalScopes()->pluck('code');
    }
}
