import { router, Link } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import SummaryCard from '../../Components/SummaryCard';
import Input from '../../Components/Input';
import Select from '../../Components/Select';
import StatusBadge from '../../Components/StatusBadge';

export default function Index({ sites, filters, statusOptions, summary }) {
    const appliedFilters = filters ?? {};
    const currentStatus = appliedFilters.status ?? '';
    const search = appliedFilters.search ?? '';

    const submitFilters = (key, value) => {
        router.get(
            '/sites',
            {
                ...appliedFilters,
                [key]: value,
            },
            {
                preserveScroll: true,
                preserveState: true,
                replace: true,
            },
        );
    };

    const startSite = (site) => {
        router.post(`/sites/${site.id}/start`, {}, { preserveScroll: true });
    };

    const stopSite = (site) => {
        router.post(`/sites/${site.id}/stop`, {}, { preserveScroll: true });
    };

    const destroySite = (site) => {
        if (!window.confirm(`Remove ${site.domain}? This will destroy its Docker containers.`)) {
            return;
        }

        router.delete(`/sites/${site.id}`, { preserveScroll: true });
    };

    const pagination = sites?.links ?? [];

    return (
        <AppLayout title="Sites overview">
            <section className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <SummaryCard label="Total sites" value={summary.total} />
                <SummaryCard label="Running" value={summary.running} />
                <SummaryCard label="Stopped" value={summary.stopped} />
                <SummaryCard label="Failed" value={summary.failed} />
            </section>

            <section className="card">
                <div className="flex flex-col gap-4 lg:flex-row lg:items-end">
                    <div className="lg:w-1/2">
                        <p className="text-xs uppercase tracking-[0.3em] text-slate-400">Search</p>
                        <Input
                            placeholder="Search by domain or host"
                            defaultValue={search}
                            onBlur={(event) => submitFilters('search', event.target.value)}
                        />
                    </div>
                    <div className="lg:w-1/4">
                        <p className="text-xs uppercase tracking-[0.3em] text-slate-400">Status</p>
                        <Select
                            value={currentStatus}
                            onChange={(event) => submitFilters('status', event.target.value)}
                        >
                            <option value="">All states</option>
                            {statusOptions.map((option) => (
                                <option key={option.value} value={option.value}>
                                    {option.label}
                                </option>
                            ))}
                        </Select>
                    </div>
                    <div className="lg:ml-auto">
                        <Link
                            href="/sites/create"
                            className="inline-flex items-center rounded-xl border border-sky-500/40 bg-sky-500/10 px-4 py-2 text-sm font-semibold text-sky-100 hover:bg-sky-500/20"
                        >
                            + Add site
                        </Link>
                    </div>
                </div>

                <div className="mt-8 overflow-hidden rounded-2xl border border-white/5">
                    <table className="min-w-full divide-y divide-white/5">
                        <thead>
                            <tr className="text-left text-xs uppercase tracking-[0.3em] text-slate-400">
                                <th className="px-6 py-4">Domain</th>
                                <th className="px-6 py-4">Server</th>
                                <th className="px-6 py-4">Status</th>
                                <th className="px-6 py-4">Last deploy</th>
                                <th className="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-white/5">
                            {sites.data.length ? (
                                sites.data.map((site) => (
                                    <tr key={site.id}>
                                        <td className="px-6 py-4">
                                            <p className="font-semibold text-white">{site.name}</p>
                                            <p className="text-sm text-slate-400">{site.domain}</p>
                                        </td>
                                        <td className="px-6 py-4 text-sm text-slate-300">
                                            <p>{site.server}</p>
                                            <p className="text-xs text-slate-500">{site.container_name}</p>
                                        </td>
                                        <td className="px-6 py-4">
                                            <StatusBadge status={site.status} label={site.status_label} />
                                        </td>
                                        <td className="px-6 py-4 text-sm text-slate-300">
                                            {site.deployed_at ? (
                                                site.deployed_at
                                            ) : (
                                                <span className="text-slate-500">Pending</span>
                                            )}
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="flex items-center justify-end gap-2 text-sm font-medium">
                                                <Link
                                                    href={`/sites/${site.id}/edit`}
                                                    className="rounded-xl border border-white/10 px-3 py-1 text-slate-200 hover:text-white"
                                                >
                                                    Edit
                                                </Link>
                                                {site.status === 'running' ? (
                                                    <button
                                                        type="button"
                                                        onClick={() => stopSite(site)}
                                                        className="rounded-xl border border-amber-400/40 bg-amber-500/10 px-3 py-1 text-amber-100 hover:bg-amber-500/20"
                                                    >
                                                        Stop
                                                    </button>
                                                ) : (
                                                    <button
                                                        type="button"
                                                        onClick={() => startSite(site)}
                                                        className="rounded-xl border border-emerald-400/40 bg-emerald-500/10 px-3 py-1 text-emerald-100 hover:bg-emerald-500/20"
                                                    >
                                                        Start
                                                    </button>
                                                )}
                                                <button
                                                    type="button"
                                                    onClick={() => destroySite(site)}
                                                    className="rounded-xl border border-rose-400/40 bg-rose-500/10 px-3 py-1 text-rose-100 hover:bg-rose-500/20"
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td className="px-6 py-10 text-center text-sm text-slate-400" colSpan={5}>
                                        No sites found. Launch one now!
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                {pagination.length > 1 ? (
                    <div className="mt-6 flex flex-wrap gap-2">
                        {pagination.map((link, index) => (
                            <button
                                key={index}
                                className={`rounded-xl px-3 py-1 text-sm ${
                                    link.active
                                        ? 'bg-sky-500/20 text-white'
                                        : 'bg-slate-800/80 text-slate-300 hover:bg-slate-700/80'
                                }`}
                                disabled={!link.url}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                                onClick={() => {
                                    if (link.url) {
                                        router.visit(link.url, { preserveState: true, preserveScroll: true });
                                    }
                                }}
                            />
                        ))}
                    </div>
                ) : null}
            </section>
        </AppLayout>
    );
}
