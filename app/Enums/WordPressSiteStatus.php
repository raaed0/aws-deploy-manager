<?php

namespace App\Enums;

enum WordPressSiteStatus: string
{
    case Deploying = 'deploying';
    case Running = 'running';
    case Stopped = 'stopped';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Deploying => 'Deploying',
            self::Running => 'Running',
            self::Stopped => 'Stopped',
            self::Failed => 'Failed',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Deploying => 'bg-amber-100 text-amber-800 ring-amber-500/20',
            self::Running => 'bg-emerald-100 text-emerald-800 ring-emerald-500/20',
            self::Stopped => 'bg-slate-100 text-slate-800 ring-slate-500/20',
            self::Failed => 'bg-rose-100 text-rose-800 ring-rose-500/20',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->map(fn (self $status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ])->all();
    }
}
