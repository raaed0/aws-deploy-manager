const styles = {
    running: 'bg-emerald-500/15 text-emerald-200 ring-emerald-500/30',
    deploying: 'bg-amber-500/15 text-amber-200 ring-amber-500/30',
    stopped: 'bg-slate-500/20 text-slate-200 ring-slate-500/30',
    failed: 'bg-rose-500/15 text-rose-200 ring-rose-500/30',
};

export default function StatusBadge({ status, label }) {
    if (!status) return null;

    const normalized = status.toLowerCase();
    const style = styles[normalized] ?? 'bg-white/5 text-white';

    return (
        <span className={`inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset ${style}`}>
            {(label ?? status).charAt(0).toUpperCase() + (label ?? status).slice(1)}
        </span>
    );
}
