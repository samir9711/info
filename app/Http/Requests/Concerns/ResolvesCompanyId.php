<?php

namespace App\Http\Requests\Concerns;

trait ResolvesCompanyId
{
    protected function isCompanyUser(): bool
    {
        $user = auth()->user();

        return $user && ($user->type === 'company');
    }

    protected function resolveCompanyId(): ?int
    {
        $user = auth()->user();

        return $user?->company_id ?? $user?->company?->id;
    }

    protected function prepareForValidation(): void
    {
        if ($this->isCompanyUser()) {
            $this->merge([
                'company_id' => $this->resolveCompanyId(),
            ]);
        }
    }
}
