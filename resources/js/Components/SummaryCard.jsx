export default function SummaryCard({ label, value, trend = null }) {
    return (
        <div className="card">
            <p className="text-xs uppercase tracking-[0.3em] text-slate-400">{label}</p>
            <p className="mt-2 text-3xl font-semibold text-white">{value}</p>
            {trend ? <p className="mt-2 text-xs text-slate-400">{trend}</p> : null}
        </div>
    );
}
