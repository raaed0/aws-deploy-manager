import clsx from 'clsx';

export default function FormField({ label, hint = null, error = null, required = false, children, className = '' }) {
    return (
        <label className={clsx('block space-y-2 text-sm', className)}>
            {label ? (
                <span className="font-medium text-slate-200">
                    {label}
                    {required ? <span className="ml-1 text-rose-300">*</span> : null}
                </span>
            ) : null}
            {children}
            {hint ? <p className="text-xs text-slate-400">{hint}</p> : null}
            {error ? <p className="text-xs font-medium text-rose-300">{error}</p> : null}
        </label>
    );
}
