<?php

namespace App\Modules\ActiveDirectory\Expressions;

use App\Core\Report\Expression\CountExpression;
use App\Modules\ActiveDirectory\Models\ADComputerEntry;
use App\Modules\ActiveDirectory\Models\ADUserEntry;
use App\Utils\DomainUtils;
use Filament\Forms\Components\Textarea;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;

class ADEntryCountExpression extends CountExpression
{
    public function exec(Builder $query): float
    {
        return $query->count($this->column);
    }

    public function beforeExec(Builder $query): void
    {
        // apply OU filters from options
        $baseDns = Arr::get($this->options, 'base_dn');

        if ($baseDns) {
            $ous = DomainUtils::parseOUs($baseDns);

            if (count($ous)) {
                $query->where(function (Builder $q) use ($ous) {
                    foreach ($ous as $ou) {
                        $q->orWhere('ou_path', '=', $ou);
                    }
                });
            }
        }
    }

    public static function label(): string
    {
        return '[AD] '.__('admin.$expression.count');
    }

    public static function form(): array
    {
        return [
            Textarea::make('base_dn')
                ->label(__('ad::messages.base dn or ou'))
                ->helperText(new HtmlString(__('ad::messages.base dn or ou helper'))),
        ];
    }

    public static function disabled(array $state): bool
    {
        $reference = Arr::get($state, 'reference');

        $references = [
            class_basename(ADUserEntry::class),
            class_basename(ADComputerEntry::class),
        ];

        return ! in_array($reference, $references);
    }
}
