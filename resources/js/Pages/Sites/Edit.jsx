import { router } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import SiteForm from '../../Components/SiteForm';
import StatusBadge from '../../Components/StatusBadge';

export default function Edit({ site }) {
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
            <SiteForm site={site} />
        </AppLayout>
    );
}
