import { router } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import SiteForm from '../../Components/SiteForm';
import StatusBadge from '../../Components/StatusBadge';

export default function Edit({ site }) {
    const history = site?.meta?.history ?? [];

    const statusValue = (status) => {
        switch (status) {
            case 'running':
                return 3;
            case 'deploying':
                return 2;
            case 'stopped':
                return 1;
            default:
                return 0;
        }
    };

    const sparklinePoints = () => {
        if (!history.length) {
            return '';
        }
        const width = 220;
        const height = 60;
        const step = history.length > 1 ? width / (history.length - 1) : width;
        return history
            .map((entry, index) => {
                const x = index * step;
                const y = height - statusValue(entry.status) * 15;
                return `${x},${y}`;
            })
            .join(' ');
    };

    const start = () => router.post(`/sites/${site.id}/start`);
    const stop = () => router.post(`/sites/${site.id}/stop`);
    const destroy = () => {
        if (window.confirm(`Destroy ${site.domain} and remove its containers?`)) {
            router.delete(`/sites/${site.id}`);
        }
    };

    return (
        <AppLayout
            title={`Manage ${site.domain}`}
            actions={
                <div className="flex flex-wrap gap-3">
                    {site.status === 'running' ? (
                        <button
                            type="button"
                            onClick={stop}
                            className="rounded-xl border border-amber-500/40 px-4 py-2 text-sm text-amber-100"
                        >
                            Stop
                        </button>
                    ) : (
                        <button
                            type="button"
                            onClick={start}
                            className="rounded-xl border border-emerald-500/40 px-4 py-2 text-sm text-emerald-100"
                        >
                            Start
                        </button>
                    )}
                    <button
                        type="button"
                        onClick={destroy}
                        className="rounded-xl border border-rose-500/40 px-4 py-2 text-sm text-rose-100"
                    >
                        Delete
                    </button>
                </div>
            }
        >
            <div className="card space-y-3">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p className="text-xs uppercase tracking-[0.3em] text-slate-400">Status</p>
                        <StatusBadge status={site.status} label={site.status_label} />
                    </div>
                    <div>
                        <p className="text-xs uppercase tracking-[0.3em] text-slate-400">Deployed at</p>
                        <p className="text-sm text-slate-200">{site.deployed_at ?? 'Pending'}</p>
                    </div>
                    <div>
                        <p className="text-xs uppercase tracking-[0.3em] text-slate-400">Last health check</p>
                        <p className="text-sm text-slate-200">{site.last_health_check_at ?? 'Unknown'}</p>
                    </div>
                </div>
            </div>
            <div className="card space-y-3">
                <div className="flex items-center justify-between">
                    <div>
                        <p className="text-xs uppercase tracking-[0.3em] text-slate-400">Health</p>
                        <p className="text-sm text-slate-200">Recent status history</p>
                    </div>
                    {history.length ? (
                        <StatusBadge status={history.at(-1)?.status ?? site.status} label="Latest" />
                    ) : null}
                </div>
                {history.length ? (
                    <div className="overflow-hidden rounded-xl bg-slate-900/60 p-4">
                        <svg viewBox="0 0 220 60" className="h-16 w-full text-sky-400">
                            <polyline
                                fill="none"
                                stroke="currentColor"
                                strokeWidth="3"
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                points={sparklinePoints()}
                            />
                        </svg>
                        <div className="mt-3 flex flex-wrap gap-4 text-xs text-slate-400">
                            <span>running=3, deploying=2, stopped=1, failed=0</span>
                            <span>points: {history.length}</span>
                        </div>
                    </div>
                ) : (
                    <p className="text-sm text-slate-400">No health samples received yet.</p>
                )}
            </div>
            <SiteForm site={site} />
        </AppLayout>
    );
}
